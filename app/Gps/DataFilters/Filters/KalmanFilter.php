<?php

namespace App\Gps\DataFilters\Filters;

use App\Gps\DataFilters\Contracts\Filter;

class KalmanFilter implements Filter
{
    public $x0;
    public $p0;

    public $f;
    public $q;
    public $h;
    public $r;

    public $state;
    public $covariance;

    public function __construct($q, $r, $f = 1, $h = 1)
    {
        $this->q = $q;
        $this->r = $r;
        $this->f = $f;
        $this->h = $h;
    }

    public function setState($state, $covariance)
    {
        $this->state = $state;
        $this->covariance = $covariance;
    }

    public function correct($data)
    {
        $this->x0 = $this->f * $this->state;
        $this->p0 = $this->f * $this->covariance * $this->f + $this->q;

        $k = ($this->h * $this->p0) / ($this->h * $this->p0 * $this->h + $this->r);
        $this->state = $this->x0 + $k * ($data - $this->h * $this->x0);
        $this->covariance = (1 - $k * $this->h) * $this->p0;

        return $this->state;
    }
}
