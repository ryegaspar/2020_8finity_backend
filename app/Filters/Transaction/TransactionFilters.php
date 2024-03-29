<?php

namespace App\Filters\Transaction;

use App\Filters\Common\SearchFilter;
use App\Filters\Common\SortFilter;
use App\Filters\FiltersAbstract;

class TransactionFilters extends FiltersAbstract
{
    protected $filters = [
        'type'   => TypeFilter::class,
        'search' => SearchFilter::class,
        'sort'   => SortFilter::class
    ];
}
