<?php

namespace App\Filters\Transfer;

use App\Filters\Common\SearchFilter;
use App\Filters\Common\SortFilter;
use App\Filters\FiltersAbstract;

class TransferFilters extends FiltersAbstract
{
    protected $filters = [
        'from_account' => FromFilter::class,
        'to_account'   => ToFilter::class,
        'search'       => SearchFilter::class,
        'sort'         => SortFilter::class
    ];
}
