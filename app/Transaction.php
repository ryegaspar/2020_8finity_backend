<?php

namespace App;

use App\ExpenseTracker\Money;
use Carbon\Carbon;
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
        return Carbon::parse($this->date)->format('Y-m-d');
    }

    public function getAmountFormattedAttribute()
    {
        return (new Money($this->amount))->formatted();
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public static function transactionsBetween($startDate = null, $endDate = null)
    {
        [$startDate, $endDate] = self::getValidDate($startDate, $endDate);

        return self::with(['category'])
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'desc')
            ->get();
    }

    protected static function getValidDate($startDate = null, $endDate = null)
    {
        $startDate = ($startDate ? Carbon::parse($startDate) : Carbon::now()->startOfMonth()->subMonths(2))->format('Y-m-d');
        $endDate = ($endDate ? Carbon::parse($endDate) : Carbon::today())->format('Y-m-d');

        return [$startDate, $endDate];
    }
}
