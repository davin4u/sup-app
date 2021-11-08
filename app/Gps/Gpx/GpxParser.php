<?php

namespace App\Gps\Gpx;

use App\Gps\DataFilters\Filters\KalmanFilter;
use App\Utilities\DataHelper;
use phpGPX\Models\Point;
use phpGPX\Models\Track;
use phpGPX\phpGPX;

class GpxParser
{
    /**
     * @var Track
     */
    private $track;

    private $slice = null;

    /**
     * @param string $path
     */
    public function __construct(string $path)
    {
        $file = (new phpGPX())->load($path);

        $this->track = $file->tracks[0];
    }

    /**
     * @return array
     */
    public function transform(): array
    {
        $this->calculateStats();

        $this->applyFilter();
        $this->applyFilter();
        $this->applyFilter();

        if (!is_null($this->slice)) {
            $this->doSlice();
        }

        return [
            'stats' => $this->transformStats(),
            'points' => $this->track->segments[0]->getPoints(),
        ];
    }

    /**
     * @param $start
     * @param $end
     * @return $this
     */
    public function slice($start, $end)
    {
        if (is_null($start) || is_null($end)) {
            return $this;
        }

        $this->slice = [$start, $end];

        return $this;
    }

    private function doSlice()
    {
        $sliced = [];

        $points = $this->track->segments[0]->getPoints();

        foreach ($points as $point) {
            /** @var Point $point */
            if ($point->distance >= $this->slice[0] && $point->distance <= $this->slice[1]) {
                $sliced[] = $point;
            }
        }

        $this->track->segments[0]->points = $sliced;
    }


    private function applyFilter()
    {
        foreach (['latitude', 'longitude', 'speed'] as $property) {
            if ($property === 'speed') {
                $this->calculateStats();
            }

            $points = $this->track->segments[0]->getPoints();

            $filter = new KalmanFilter(2, 15, 1, 1);
            $filter->setState($points[0]->{$property}, 0.1);

            foreach ($points as &$point) {
                $point->{$property} = $filter->correct($point->{$property});
            }

            $this->track->segments[0]->points = $points;
        }

        $this->calculateStats();
    }

    private function calculateStats()
    {
        $points = $this->track->segments[0]->getPoints();

        $points[0]->speed = property_exists($points[0], 'speed') && $points[0]->speed
            ? $points[0]->speed
            : 0;
        $points[0]->part_distance = property_exists($points[0], 'part_distance') && $points[0]->part_distance
            ? $points[0]->part_distance
            : 0;
        $points[0]->total_distance = property_exists($points[0], 'total_distance') && $points[0]->total_distance
            ? $points[0]->total_distance
            : 0;

        foreach ($points as $key => &$point) {
            if ($key > 0) {
                $prevPointTime = $points[$key - 1]->time->getTimestamp();
                $currPointTime = $point->time->getTimestamp();
                $elapsedTime = $currPointTime - $prevPointTime;

                $prevPointDistance = $points[$key - 1]->distance;
                $currPointDistance = $point->distance;

                $point->speed = $this->calculateSpeed($prevPointDistance, $currPointDistance, $elapsedTime, true, 1);
                $point->part_distance = $currPointDistance - $prevPointDistance;
                $point->total_distance = $point->distance;
            }
        }

        $this->track->segments[0]->points = $points;

        $this->track->recalculateStats();
    }

    /**
     * @param $pointA
     * @param $pointB
     * @param $time
     * @param bool $msToKmh
     * @param int $precision
     * @return float
     */
    private function calculateSpeed($pointA, $pointB, $time, $msToKmh = true, $precision = 1)
    {
        $speed = ($pointB - $pointA) / $time;

        if ($msToKmh) {
            $speed *= 3.6;
        }

        return round($speed, $precision);
    }

    /**
     * @return array
     */
    private function transformStats(): array
    {
        $stats = $this->track->stats;
        $points = $this->track->segments[0]->getPoints();

        $distance = round($stats->distance, 2);
        $averageSpeed = round($stats->averageSpeed, 2);
        $averagePace = round($stats->averagePace, 2);
        $startedAt = $stats->startedAt->format('d.m.Y H:i:s');
        $startedAtTimestamp = $stats->startedAt->getTimestamp();
        $finishedAt = $stats->finishedAt->format('d.m.Y H:i:s');
        $finishedAtTimestamp = $stats->finishedAt->getTimestamp();
        $duration = $stats->duration;

        $speedStats = $this->calculateSpeedStats($points);

        return array_merge([
            'intervals_stats' => $this->calculateIntervalTrainingStats($points, $speedStats['clean_average_speed']),
            'distance_m' => $distance,
            'distance_km' => round($distance/1000, 2),
            'average_speed_ms' => $averageSpeed,
            'average_speed_kmh' => round($averageSpeed * 3.6, 2),
            'started_at' => $startedAt,
            'started_at_timestamp' => $startedAtTimestamp,
            'finished_at' => $finishedAt,
            'finished_at_timestamp' => $finishedAtTimestamp,
            'duration_s' => $duration,
            'duration_m' => round($duration/60, 2),
        ], $speedStats);
    }

    /**
     * @param array $points
     * @return array
     */
    private function calculateSpeedStats(array $points): array
    {
        $minSpeed = null;
        $maxSpeed = null;

        foreach ($points as $point) {
            if ($point->speed > 0) {
                if (is_null($minSpeed) || ($point->speed < $minSpeed)) {
                    $minSpeed = $point->speed;
                }

                if (is_null($maxSpeed) || $point->speed > $maxSpeed) {
                    $maxSpeed = $point->speed;
                }
            }
        }

        return [
            'min_speed' => $minSpeed,
            'max_speed' => $maxSpeed,
            'clean_average_speed' => $this->calculateCleanAverageSpeed($points),
        ];
    }

    private function calculateCleanAverageSpeed(array $points)
    {
        $data = [];

        foreach ($points as $point) {
            // prepare data
            // [5.2 km/h => 300m, 5.6km/h => 250m...]
            if ($point->speed > 0) {
                if (!isset($data['s_' . $point->speed])) {
                    $data['s_' . $point->speed] = 0;
                }

                $data['s_' . $point->speed] += $point->part_distance;
            }
        }

        asort($data);
        reset($data);

        $step = current($data);
        $int = 0;
        $total = 0;

        foreach ($data as $speed => $distance) {
            $speed = (float)str_replace('s_', '', $speed);
            $int += $distance / $step;
            $total += ($distance / $step) * $speed;
        }

        return round($total / $int, 2);
    }

    private function calculateIntervalTrainingStats(array $points, $trackCleanAverageSpeed)
    {
        $data = [];
        $prevPoint = null;
        $currentMax = current($points)->speed;
        $averagePassed = false;
        $isInterval = false;
        $i = 0;

        foreach ($points as $point) {
            if (!isset($data[$i])) {
                $data[$i] = [
                    'labels' => [],
                    'points' => []
                ];
            }

            if (is_null($prevPoint)) {
                $prevPoint = $point;

                continue;
            }

            $growing = $point->speed >= $prevPoint->speed;

            if ($growing || $averagePassed) {
                // speed is growing... we assume that this is the start of interval

                $data[$i]['points'][] = $point;

                if ($point->speed > $currentMax) {
                    $currentMax = $point->speed;
                }

                $averagePassed = $point->speed >= $trackCleanAverageSpeed;

                if ($growing && $averagePassed) {
                    $isInterval = true;
                }
            } else {
                if (!$isInterval && !empty($data[$i])) {
                    $data[$i]['points'] = [];
                    $currentMax = 0;
                }

                if ($isInterval) {
                    $i++;
                    $averagePassed = false;
                    $currentMax = 0;
                    $isInterval = false;
                }
            }

            $prevPoint = $point;
        }

        if (!empty($data)) {
            foreach ($data as $key => $intervalPoints) {
                if (empty($intervalPoints['points'])) {
                    continue;
                }

                $c = count($intervalPoints['points']) - 1;
                $startDistance = $intervalPoints['points'][0]->distance;
                $finishDistance = $intervalPoints['points'][$c]->distance;

                $startTime = $intervalPoints['points'][0]->time->getTimestamp();
                $finishTime = $intervalPoints['points'][$c]->time->getTimestamp();

                $data[$key]['stats'] = array_merge($this->calculateSpeedStats($intervalPoints['points']), [
                    'distance' => DataHelper::metersToHumanDistance(round($finishDistance - $startDistance), true),
                    'distance_m' => round($finishDistance - $startDistance),
                    'elapsed_time' => DataHelper::secondsToHumanDuration($finishTime - $startTime, true),
                    'elapsed_time_s' => $finishTime - $startTime,
                ]);
            }

            $data = array_filter($data, function ($intervalData) use ($trackCleanAverageSpeed) {
                // @TODO: filtration by number of points may give issues here
                // in case of short intervals there may be little amount of points
                // and such intervals will be filtered out

                return count($intervalData['points']) > config('gpx_parser.minimum_interval_points')
                    && $intervalData['stats']['clean_average_speed'] >= $trackCleanAverageSpeed;
            });

            $data = array_values($data);

            $bestMaxSpeed = $data[0]['stats']['max_speed'];
            $bestMaxSpeedIntervalNumber = 0;
            $bestAvgSpeed = $data[0]['stats']['clean_average_speed'];
            $bestAvgSpeedIntervalNumber = 0;
            $secondBestAvgSpeed = $data[0]['stats']['clean_average_speed'];
            $bestDistance = $data[0]['stats']['distance_m'];
            $bestDistanceIntervalNumber = 0;

            foreach ($data as $key => $interval) {
                $data[$key]['number'] = $key + 1;

                if ($interval['stats']['max_speed'] > $bestMaxSpeed) {
                    $bestMaxSpeed = $interval['stats']['max_speed'];
                    $bestMaxSpeedIntervalNumber = $key;
                }

                if ($interval['stats']['clean_average_speed'] > $bestAvgSpeed) {
                    $secondBestAvgSpeed = $bestAvgSpeed;
                    $bestAvgSpeed = $interval['stats']['clean_average_speed'];
                    $bestAvgSpeedIntervalNumber = $key;
                }

                if ($interval['stats']['distance_m'] > $bestDistance) {
                    $bestDistance = $interval['stats']['distance_m'];
                    $bestDistanceIntervalNumber = $key;
                }
            }

            $data[$bestDistanceIntervalNumber]['labels'][] = 'best_distance';
            $data[$bestMaxSpeedIntervalNumber]['labels'][] = 'best_max_speed';
            $data[$bestAvgSpeedIntervalNumber]['labels'][] = 'best_avg_speed';

            // determine intervals with worse avg speed
            $thresholdAvgSpeed = round(($bestAvgSpeed + $secondBestAvgSpeed) / 2 * config('gpx_parser.threshold_avg_speed_reduction'), 2);
            foreach ($data as $key => $interval) {
                if ($interval['stats']['clean_average_speed'] < $thresholdAvgSpeed) {
                    $data[$key]['labels'][] = 'worse_avg_speed';
                }
            }
        }

        return $data;
    }
}
