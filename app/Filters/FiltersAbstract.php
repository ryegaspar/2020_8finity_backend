<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

abstract class FiltersAbstract
{
    protected $request;

    protected $filters = [];

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function filter(Builder $builder)
    {
        foreach ($this->getFilters() as $filter => $value) {
            $this->resolveFilter($filter)->filter($builder, $value);
        }

        return $builder;
    }

    public function add(array $filters)
    {
        $this->filters = array_merge($this->filters, $filters);

        return $this;
    }

    protected function getFilters()
    {
        return $this->filterFilters($this->filters);
    }

    protected function resolveFilter($filter)
    {
        return new $this->filters[$filter];
    }

    /**
     * get the keys on the filters and their values from the request
     * that are defined specifically for the instantiated object
     * @param $filters
     * @return array
     */
    protected function filterFilters($filters)
    {
        // array filter to remove empty values
        return array_filter($this->request->only(array_keys($this->filters)));
    }
}
