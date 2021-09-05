<?php

namespace App\Filters\Transfer;

use App\Filters\FilterAbstract;
use Illuminate\Database\Eloquent\Builder;

class ToFilter extends FilterAbstract
{
    public function filter(Builder $builder, $value)
    {
        return $builder->where('to_account', $value);
    }
}
