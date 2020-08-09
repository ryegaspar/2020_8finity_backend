<?php

namespace App;

use App\ExpenseTracker\Money;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    const ByDate = 'Y-m-d';
    const ByWeek = 'Y-W';
    const ByMonth = 'Y-m';
    const ByYear = 'Y';

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

    public static function transactionsBy($startTime = null, $endTime = null, $groupBy = self::ByDate)
    {
        [$startTime, $endTime] = self::getTime($startTime, $endTime);

        return self::whereBetween('date', [$startTime, $endTime])
            ->orderBy('date', 'desc')
            ->get()
            ->groupBy(function ($val) use ($groupBy) {
                return Carbon::parse($val->date)->format($groupBy);
            });
    }

    protected static function getTime($startTime = null, $endTime = null)
    {
        $startTime = ($startTime ? Carbon::parse($startTime) : Carbon::parse('-6 months'))->format('Y-m-d');
        $endTime = ($endTime ? Carbon::parse($endTime) : Carbon::today())->format('Y-m-d');

        return [$startTime, $endTime];
    }
}
