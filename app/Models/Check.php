<?php

namespace App\Models;

use App\Filters\Transaction\TransactionFilters;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Check extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'post_date' => 'date:Y-m-d',
        'amount'    => 'integer'
    ];

    public function getFormattedPostDateAttribute()
    {
        return Carbon::parse($this->post_date)->format('Y-m-d');
    }

    public function scopeTableFilter(Builder $builder, $request)
    {
        return (new TransactionFilters($request)) //TODO: use CheckFilters instead of piggy backing
            ->filter($builder);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

}
