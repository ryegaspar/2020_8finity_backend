<?php

namespace App\Filters\Log;

use App\Filters\FilterAbstract;
use Illuminate\Database\Eloquent\Builder;

class TypeFilter extends FilterAbstract
{
    public function filter(Builder $builder, $value)
    {
        return $builder->where('loggable_type', $value);
    }
}
