<?php

namespace App\Utilities;

use Illuminate\Support\Arr;

class ChartHelper
{
    /**
     * @param array $points
     * @param $xKey
     * @param $yKey
     * @return array[]
     */
    public static function prepareLineChartData(array $points, $xKey, $yKey): array
    {
        $x_points = [];
        $y_points = [];

        foreach ($points as $point) {
            $x_points[] = static::getProperty($point, $xKey);
            $y_points[] = static::getProperty($point, $yKey);
        }

        return [
            'x_points' => $x_points,
            'y_points' => $y_points,
        ];
    }

    private static function getProperty($point, $property)
    {
        if (is_object($point)) {
            return property_exists($point, $property) ? $point->{$property} : null;
        }

        if (is_array($point)) {
            return Arr::get($point, $property, null);
        }

        return null;
    }
}
