<?php

namespace App\Filters\Common;

use App\Filters\FilterAbstract;
use Illuminate\Database\Eloquent\Builder;

class SortFilter extends FilterAbstract
{
    public function filter(Builder $builder, $value)
    {
        $sort = explode(',', $value);

        foreach ($sort as $item) {
            list ($sortCol, $sortDir) = explode('|', $item);
            $builder->orderBy($sortCol, $sortDir);
        }

        return $builder;
    }
}
