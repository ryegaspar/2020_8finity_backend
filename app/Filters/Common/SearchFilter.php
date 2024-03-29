<?php

namespace App\Filters\Common;

use App\Filters\FilterAbstract;
use Illuminate\Database\Eloquent\Builder;

class SearchFilter extends FilterAbstract
{
    public function filter(Builder $builder, $value)
    {
        return $builder->where('description', 'LIKE', "%{$value}%");
    }
}
