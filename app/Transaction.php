<?php

namespace App;

use App\ExpenseTracker\Money;
use Carbon\Carbon;
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
}
