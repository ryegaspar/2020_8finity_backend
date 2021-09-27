<?php

namespace App\Models;

use App\Filters\Transaction\TransactionFilters;
use App\Logger\Loggable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory, Loggable;

    public $loggable_actions = ['created', 'updated', 'deleted'];
    public $loggable_fields = [
        'category_id',
        'account_id',
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

    private function getValidDate($startDate = null, $endDate = null)
    {
        $startDate = ($startDate ? Carbon::parse($startDate) : Carbon::now()->startOfMonth())->format('Y-m-d');
        $endDate = ($endDate ? Carbon::parse($endDate) : Carbon::today()->endOfMonth()->addDay())->format('Y-m-d');

        return [$startDate, $endDate];
    }

    public function getFormattedDateAttribute()
    {
        return Carbon::parse($this->date)->format('Y-m-d');
    }

    public function scopeTableFilter(Builder $builder, $request)
    {
        return (new TransactionFilters($request))
            ->filter($builder);
    }

    public function scopeSumByCategoryTypeBetween($query, $type = 'in', $startDate = null, $endDate = null)
    {
        [$startDate, $endDate] = $this->getValidDate($startDate, $endDate);

        return $query
            ->whereHas('category', function ($query) use ($type) {
                $query->where('categories.type', $type);
            })
            ->with([
                'category' => function ($query) use ($type) {
                    $query->where('categories.type', $type);
                }
            ])
            ->whereBetween('date', [$startDate, $endDate])
            ->sum('amount');
    }

    public function scopeTransactionsBetween($query, $startDate = null, $endDate = null)
    {
        [$startDate, $endDate] = $this->getValidDate($startDate, $endDate);

        return $query
            ->with(['category', 'admin'])
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'desc')
            ->get();
    }

    public function scopeSumByAccount($query, $accountId)
    {
        return $query->with(['account'])
            ->whereHas('account', function ($q) use ($accountId) {
                $q->where('accounts.id', $accountId);
            })
            ->sum('amount');
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
