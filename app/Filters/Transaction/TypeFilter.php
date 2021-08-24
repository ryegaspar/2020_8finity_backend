<?php

namespace App\Filters\Transaction;

use App\Filters\FilterAbstract;
use Illuminate\Database\Eloquent\Builder;

class TypeFilter extends FilterAbstract
{
    public function filter(Builder $builder, $value)
    {
        if ($value === 'all')
            return $builder;

        $value = ($value === 'income' ? 'in' : 'out');
        return $builder->whereHas('category', function ($q) use ($value) {
            $q->where('type', $value);
        });
    }
}
