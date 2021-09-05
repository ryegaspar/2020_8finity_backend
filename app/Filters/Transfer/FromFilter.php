<?php

namespace App\Filters\Transfer;

use App\Filters\FilterAbstract;
use Illuminate\Database\Eloquent\Builder;

class FromFilter extends FilterAbstract
{
    public function filter(Builder $builder, $value)
    {
        return $builder->where('from_account', $value);
    }
}
