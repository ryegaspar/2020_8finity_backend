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

    public function getAmountFormattedAttribute()
    {
        return (new Money($this->amount))->formatted();
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
        [$startTime, $endTime] = self::getTime($startTime, $endTime);

        return self::whereBetween('date', [$startTime, $endTime])
            ->orderBy('date', 'desc')
            ->get()
            ->groupBy(function ($val) {
                return Carbon::parse($val->date)->format('d');
            });
    }

    protected static function getTime($startTime = null, $endTime = null)
    {
        $startTime = ($startTime ? Carbon::parse($startTime) : Carbon::parse('-6 months'))->format('Y-m-d');
        $endTime = ($endTime ? Carbon::parse($endTime) : Carbon::today())->format('Y-m-d');

        return [$startTime, $endTime];
    }
}
