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
//    const ByYear = 'Y';

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

    public static function transactionsBetween($startDate = null, $endDate = null)
    {
        [$startDate, $endDate] = self::getValidDate($startDate, $endDate);

        return self::whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'desc')
            ->get();
    }

    public static function transactionsBy($startDate = null, $endDate = null, $groupBy = self::ByDate)
    {
        [$startDate, $endDate] = self::getValidDate($startDate, $endDate);

        return self::whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'desc')
            ->get()
            ->groupBy(function ($val) use ($groupBy) {
                return Carbon::parse($val->date)->format($groupBy);
            });
    }

    protected static function getValidDate($startDate = null, $endDate = null)
    {
        $startDate = ($startDate ? Carbon::parse($startDate) : Carbon::parse('-3 months'))->format('Y-m-d');
        $endDate = ($endDate ? Carbon::parse($endDate) : Carbon::today())->format('Y-m-d');

        return [$startDate, $endDate];
    }
}
