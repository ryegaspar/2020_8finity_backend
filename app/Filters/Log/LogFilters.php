<?php

namespace App\Filters\Log;

use App\Filters\Common\SortFilter;
use App\Filters\FiltersAbstract;

class LogFilters extends FiltersAbstract
{
    protected $filters = [
        'sort'   => SortFilter::class,
        'type'   => TypeFilter::class,
        'action' => ActionFilter::class,
    ];
}
