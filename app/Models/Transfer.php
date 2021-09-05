<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transfer extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'date'   => 'date:Y-m-d',
        'amount' => 'integer'
    ];

    public function scopeSumFrom($query, $account)
    {
        return $query->where('from_account', $account)->sum('amount');
    }

    public function scopeSumTo($query, $account)
    {
        return $query->where('to_account', $account)->sum('amount');
    }
}
