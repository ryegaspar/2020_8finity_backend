<?php

namespace App\Filters\Log;

use App\Filters\FilterAbstract;
use Illuminate\Database\Eloquent\Builder;

class ActionFilter extends FilterAbstract
{
    public function filter(Builder $builder, $value)
    {
        return $builder->where('action', $value);
    }
}
