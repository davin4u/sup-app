<?php

namespace App\Gps\DataFilters;

use App\Gps\DataFilters\Contracts\Filter;
use App\Gps\DataFilters\Exceptions\FilterNotFoundException;

class FiltersPipeline
{
    private $pipeline = [];

    private $next;

    public function add(string $property, string $filterClass)
    {
        if (!class_exists($filterClass)) {
            throw new FilterNotFoundException("Filter {$filterClass} not found.");
        }

        $filter = new $filterClass();

        $this->pipeline[] = function ($items) use ($property, $filter) {
            /** @var Filter $filter */
            $filtered = [];

            foreach ($items as $item) {
                $filtered[] = $filter->filter($item, $property);
            }

            return $filtered;
        };

    }

    public function filter(array $items)
    {

    }
}
