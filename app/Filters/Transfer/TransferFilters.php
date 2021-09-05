<?php

namespace App\Filters\Transfer;

use App\Filters\Common\SearchFilter;
use App\Filters\Common\SortFilter;
use App\Filters\FiltersAbstract;

class TransferFilters extends FiltersAbstract
{
    protected $filters = [
        'from'   => FromFilter::class,
        'to'     => ToFilter::class,
        'search' => SearchFilter::class,
        'sort'   => SortFilter::class
    ];
}
