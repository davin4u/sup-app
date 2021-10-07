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
            $x_points[] = Arr::get($point, $xKey, null);
            $y_points[] = Arr::get($point, $yKey, null);
        }

        return [
            'x_points' => $x_points,
            'y_points' => $y_points,
        ];
    }
}
