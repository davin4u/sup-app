<?php

namespace App\Gps\Gpx;

use App\Gps\DataFilters\Filters\KalmanFilter;
use phpGPX\Models\Point;
use phpGPX\Models\Track;
use phpGPX\phpGPX;

class GpxParser
{
    /**
     * @var Track
     */
    private $track;

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
        $sliced = [];

        $points = $this->track->segments[0]->getPoints();

        foreach ($points as $point) {
            /** @var Point $point */
            if ($point->distance >= $start && $point->distance <= $end) {
                $sliced[] = $point;
            }
        }

        $this->track->segments[0]->points = $sliced;

        return $this;
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

        $points[0]->speed = 0;
        $points[0]->part_distance = 0;
        $points[0]->total_distance = 0;

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

        return array_merge([
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
        ], $this->calculateSpeedStats($points));
    }

    /**
     * @param array $points
     * @return array
     */
    private function calculateSpeedStats(array $points): array
    {
        $minSpeed = null;
        $maxSpeed = null;
        $averageSpeed = 0;

        foreach ($points as $point) {
            $averageSpeed += $point->speed;

            if (is_null($minSpeed) || $point->speed < $minSpeed) {
                $minSpeed = $point->speed;
            }

            if (is_null($maxSpeed) || $point->speed > $maxSpeed) {
                $maxSpeed = $point->speed;
            }
        }

        return [
            'min_speed' => $minSpeed,
            'max_speed' => $maxSpeed,
            'clean_average_speed' => round($averageSpeed / count($points), 1)
        ];
    }
}
