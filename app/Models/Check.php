<?php

namespace App\Models;

use App\Filters\Transaction\TransactionFilters;
use App\Logger\Loggable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Check extends Model
{
    use HasFactory, Loggable;

    public $loggable_actions = ['created', 'updated', 'deleted'];
    public $loggable_fields = [
        'category_id',
        'account_id',
        'transaction_id',
        'amount',
        'description',
        'status',
        'notes',
        'due_date'
    ];

    const PENDING = 'pending';
    const CLEARED = 'cleared';
    const CANCELLED = 'cancelled';

    protected $guarded = [];

    protected $casts = [
        'due_date' => 'date:Y-m-d',
        'amount'   => 'integer'
    ];

    public function getFormattedDueDateAttribute()
    {
        return Carbon::parse($this->due_date)->format('Y-m-d');
    }

    public function scopeTableFilter(Builder $builder, $request)
    {
        return (new TransactionFilters($request)) //TODO: use CheckFilters instead of piggy backing
        ->filter($builder);
    }

    public function createTransaction()
    {
        return Transaction::create([
            'description' => $this->description,
            'category_id' => $this->category_id,
            'account_id'  => $this->account_id,
            'admin_id'    => auth('admin')->id(),
            'amount'      => $this->amount,
            'date'        => Carbon::now(),
            'notes'       => $this->notes
        ]);
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
