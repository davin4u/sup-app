<?php

namespace App\Utilities;

use phpGPX\Models\Point;
use phpGPX\Models\Stats;
use phpGPX\Models\Track;
use phpGPX\phpGPX;

class GpxAnalyzer
{
    /**
     * @var phpGPX
     */
    private $gpxParser;

    public function __construct()
    {
        $this->gpxParser = new phpGPX();
    }

    /**
     * @param string $path
     *
     * @return array[]
     */
    public function analyze(string $path, $start = null, $end = null): array
    {
        $file = $this->gpxParser->load($path);

        /** @var Track $track */
        $track = $file->tracks[0];

        if (!is_null($start) && !is_null($end)) {
            $this->slicePoints($track, $start, $end);
        }

        /** @var Stats $stats */
        $stats = $track->stats;

        $points = $track->segments[0]->getPoints();
        $points = $this->formatPoints($points);
        $points = $this->combinePoints($points, 10, 1);

        return [
            'stats' => $this->formatStats($stats, $points),
            'points' => $points,
        ];
    }

    /**
     * @param Track $track
     * @param $start
     * @param $end
     */
    private function slicePoints(Track $track, $start, $end)
    {
        $sliced = [];

        $points = $track->segments[0]->getPoints();

        foreach ($points as $point) {
            /** @var Point $point */
            if ($point->distance >= $start && $point->distance <= $end) {
                $sliced[] = $point;
            }
        }

        $track->segments[0]->points = $sliced;

        $track->recalculateStats();
    }

    /**
     * @param Stats $stats
     * @param array $points
     *
     * @return array
     */
    private function formatStats(Stats $stats, array $points): array
    {
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
     *
     * @return array
     */
    private function formatPoints(array $points): array
    {
        $result = [];

        $startTime = null;

        if (!empty($points) && isset($points[0])) {
            $startTime = $points[0]->time->getTimestamp();
        }

        foreach ($points as $point) {
            /** @var Point $point */
            $result[] = [
                'lat' => $point->latitude,
                'lng' => $point->longitude,
                'distance' => $point->distance,
                'difference' => $point->difference,
                'time' => $point->time->getTimestamp(),
                'formatted_time' => $point->time->format('d.m.Y H:i:s'),
                'elapsed_time' => $point->time->getTimestamp() - $startTime,
            ];
        }

        return $result;
    }

    private function combinePoints(array $points, int $step = 50, int $precision = 1): array
    {
        $data = [];

        $prevCheckDistance = $points[0]['distance'];
        $timeStart = null;
        $combineKey = 0;

        foreach ($points as $point) {
            if (!isset($data[$combineKey])) {
                $data[$combineKey] = [
                    'points' => []
                ];
            }

            $data[$combineKey]['points'][] = $point;

            $timeEnd = $point['time'];
            if (is_null($timeStart)) {
                $timeStart = $point['time'];
            }

            if (($point['distance'] - $prevCheckDistance) >= $step) {
                $elapsedTime = $timeEnd - $timeStart;

                $data[$combineKey] = array_merge($data[$combineKey], [
                    'elapsed_time' => $elapsedTime,
                    'time_start' => $timeStart,
                    'time_end' => $timeEnd,
                    'part_distance' => round($point['distance']) - $prevCheckDistance,
                    'total_distance' => round($point['distance']),
                    'speed' => $this->calculateSpeed($prevCheckDistance, $point['distance'], $elapsedTime, true, $precision),
                ]);

                $prevCheckDistance = $point['distance'];
                $timeStart = null;
                $timeEnd = null;
                $combineKey++;
            }
        }

        $lastPoint = end($points);

        if (!is_null($timeStart) && (($lastPoint['distance'] - $prevCheckDistance) < $step)) {
            if (count($data[$combineKey]['points']) === 1) {
                $prevPointsSet = $data[$combineKey - 1];
                $timeStart = $prevPointsSet['time_start'];
                $timeEnd = $lastPoint['time'];
                $elapsedTime = $timeEnd - $timeStart;

                $data[$combineKey - 1] = array_merge($data[$combineKey - 1], [
                    'elapsed_time' => $elapsedTime,
                    'time_start' => $timeStart,
                    'time_end' => $timeEnd,
                    'part_distance' => round($prevPointsSet['part_distance']) + $lastPoint['difference'],
                    'total_distance' => round($lastPoint['distance']),
                    'speed' => $this->calculateSpeed($data[$combineKey - 2]['total_distance'], $lastPoint['distance'], $elapsedTime, true, $precision),
                ]);

                unset($data[$combineKey]);
            }
            else {
                $elapsedTime = $lastPoint['time'] - $timeStart;

                $data[$combineKey] = array_merge($data[$combineKey], [
                    'elapsed_time' => $elapsedTime,
                    'time_start' => $timeStart,
                    'time_end' => $lastPoint['time'],
                    'part_distance' => round($lastPoint['distance']) - $prevCheckDistance,
                    'total_distance' => round($lastPoint['distance']),
                    'speed' => $this->calculateSpeed($prevCheckDistance, $lastPoint['distance'], $elapsedTime, true, $precision),
                ]);
            }
        }

        return $data;
    }

    private function calculateSpeedStats(array $points): array
    {
        $minSpeed = null;
        $maxSpeed = null;
        $averageSpeed = 0;

        foreach ($points as $point) {
            $averageSpeed += $point['speed'];

            if (is_null($minSpeed) || $point['speed'] < $minSpeed) {
                $minSpeed = $point['speed'];
            }

            if (is_null($maxSpeed) || $point['speed'] > $maxSpeed) {
                $maxSpeed = $point['speed'];
            }
        }

        return [
            'min_speed' => $minSpeed,
            'max_speed' => $maxSpeed,
            'clean_average_speed' => round($averageSpeed / count($points), 1)
        ];
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
}
