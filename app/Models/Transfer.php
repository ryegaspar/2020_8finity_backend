<?php

namespace App\Models;

use App\Filters\Transfer\TransferFilters;
use App\Logger\Loggable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transfer extends Model
{
    use HasFactory, Loggable;

    public $loggable_actions = ['created', 'updated', 'deleted'];
    public $loggable_fields = [
        'from_account',
        'to_account',
        'amount',
        'description',
        'notes',
        'date'
    ];

    protected $guarded = [];

    protected $casts = [
        'date'   => 'date:Y-m-d',
        'amount' => 'integer'
    ];

    public function getFormattedDateAttribute()
    {
        return Carbon::parse($this->date)->format('Y-m-d');
    }

    public function scopeTableFilter(Builder $builder, $request)
    {
        return (new TransferFilters($request))
            ->filter($builder);
    }

    public function fromAccount()
    {
        return $this->belongsTo(Account::class, 'from_account');
    }

    public function toAccount()
    {
        return $this->belongsTo(Account::class, 'to_account');
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

//    public function scopeSumFrom($query, $account)
//    {
//        return $query->where('from_account', $account)->sum('amount');
//    }
//
//    public function scopeSumTo($query, $account)
//    {
//        return $query->where('to_account', $account)->sum('amount');
//    }


}
