<?php

namespace App\Utilities;

class DataHelper
{
    /**
     * @param int $seconds
     * @return string
     */
    public static function secondsToHumanDuration(int $seconds): string
    {
        $formatted = '';
        $h_sec = 0;
        $h_min = 0;
        $h_hours = 0;

        if ($seconds < 60) {
            return $seconds . 's';
        }

        if ($seconds > 60) {
            $h_min = floor($seconds / 60);
            $h_sec = $seconds - ($h_min * 60);
        }

        if ($h_min > 60) {
            $h_hours = floor($h_min / 60);
            $h_min = $h_min - ($h_hours * 60);
        }

        if ($h_hours > 0) {
            $formatted .= $h_hours . 'h ';
        }

        if ($h_min > 0) {
            $formatted .= $h_min . 'm ';
        }

        if ($h_sec > 0) {
            $formatted .= $h_sec . 's';
        }

        return $formatted;
    }

    /**
     * @param int $meters
     * @return string
     */
    public static function metersToHumanDistance(int $meters): string
    {
        if ($meters > 1000) {
            $km = floor($meters / 1000);
            $m = $meters - ($km * 1000);

            return $km . __('km') . ' ' . $m . __('m');
        }

        return $meters . __('m');
    }
}
