<?php

namespace App\Utilities;

class DataHelper
{
    /**
     * @param int $seconds
     * @param false $html
     * @return string
     */
    public static function secondsToHumanDuration(int $seconds, $html = false): string
    {
        $formatted = '';
        $h_sec = 0;
        $h_min = 0;
        $h_hours = 0;

        if ($seconds < 60) {
            return $seconds . ($html ? '<span class="text-sm">s</span>' : 's');
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
            $formatted .= $h_hours . ($html ? '<span class="text-sm">h</span> ' : 'h ');
        }

        if ($h_min > 0) {
            $formatted .= $h_min . ($html ? '<span class="text-sm">m</span> ' : 'm ');
        }

        if ($h_sec > 0) {
            $formatted .= $h_sec . ($html ? '<span class="text-sm">s</span>' : 's');
        }

        return $formatted;
    }

    /**
     * @param int $meters
     * @param false $html
     * @return string
     */
    public static function metersToHumanDistance(int $meters, $html = false): string
    {
        if ($meters > 1000) {
            $km = floor($meters / 1000);
            $m = $meters - ($km * 1000);

            return $html
                ? ($km . '<span class="text-sm">' . __('km') . '</span> ' . $m . '<span class="text-sm">' . __('m') . '</span>')
                : ($km . __('km') . ' ' . $m . __('m'));
        }

        return $html
            ? ($meters . '<span class="text-sm">' . __('m') . '</span>')
            : ($meters . __('m'));
    }
}
