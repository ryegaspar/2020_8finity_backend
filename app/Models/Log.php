<?php

namespace App\Models;

use App\Filters\Log\LogFilters;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function scopeTableFilter(Builder $builder, $request)
    {
        return (new LogFilters($request))
            ->filter($builder);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}
