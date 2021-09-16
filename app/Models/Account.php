<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'is_active'     => 'boolean',
        'balance'       => 'integer',
        'check_balance' => 'integer'
    ];

    public function scopeTableFilter($query)
    {
        return $query->when(request('sort') ?? null, function ($query) {
            $sort = explode(',', request('sort'));

            foreach ($sort as $item) {
                list ($sortCol, $sortDir) = explode('|', $item);
                $query->orderBy($sortCol, $sortDir);
            }
        });
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function recalculateBalance()
    {
        $fromTransfers = $this->fromTransfers()->sum('amount');
        $toTransfers = $this->toTransfers()->sum('amount');
        $transactions = $this->transactions()->sum('amount');

        $this->update(['balance' => $transactions + $toTransfers - $fromTransfers]);
    }

    public function recalculateCheckBalance()
    {
        $checks = $this->checks()->sum('amount');

        $this->update(['check_balance' => $checks]);
    }

    public function fromTransfers()
    {
        return $this->hasMany(Transfer::class, 'from_account');
    }

    public function toTransfers()
    {
        return $this->hasMany(Transfer::class, 'to_account');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function checks()
    {
        return $this->hasMany(Check::class);
    }

    //    public function scopeTableView($query, array $filters = [])
//    {
//        return $query
//            ->when(request('sort') ?? null, function ($query) {
//                $sort = explode(',', request('sort'));
//
//                foreach ($sort as $item) {
//                    list ($sortCol, $sortDir) = explode('|', $item);
//                    $query->orderBy($sortCol, $sortDir);
//                }
//            })
//            ->when(request('search') ?? null, function ($query) {
//                $search = request('search');
//                $query->where('description', 'LIKE', "%{$search}%");
//            })
//            ->when(request('type') && request('type') !== 'all', function ($query) {
//                $type = request('type') === 'income' ? 'in' : 'out';
//                $query->whereHas('category', function ($q) use ($type) {
//                    $q->where('type', $type);
//                });
//            });
//    }
}
