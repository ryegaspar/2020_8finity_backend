<?php

namespace App;

use App\ExpenseTracker\Money;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $guarded = [];

    public function getFormattedDateAttribute()
    {
        return Carbon::parse($this->date)->format('M j, Y');
    }

    public function getAmountAttribute($value)
    {
        return (new Money($value))->formatted();
    }

    public function scopeIncome(Builder $builder)
    {
        $builder->where('transaction_type', 'in');
    }

    public function scopeExpenses(Builder $builder)
    {
        $builder->where('transaction_type', 'out');
    }

    public static function transactionsByDate($startTime = null, $endTime = null)
    {
        return self::orderBy('date', 'desc')
            ->get()
            ->groupBy(function ($val) {
                return Carbon::parse($val->date)->format('d');
            });
    }
}
