<?php

namespace App\Filters\Transaction;

use App\Filters\FiltersAbstract;

class TransactionFilters extends FiltersAbstract
{
    protected $filters = [
        'type'   => TypeFilter::class,
        'search' => SearchFilter::class,
        'sort'   => SortFilter::class
    ];
}
