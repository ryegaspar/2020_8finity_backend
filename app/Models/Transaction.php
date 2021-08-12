<?php

namespace App\Models;

use App\ExpenseTracker\Money;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'date' => 'date:Y-m-d',
    ];

    private function getValidDate($startDate = null, $endDate = null)
    {
        $startDate = ($startDate ? Carbon::parse($startDate) : Carbon::now()->startOfMonth())->format('Y-m-d');
        $endDate = ($endDate ? Carbon::parse($endDate) : Carbon::today()->endOfMonth())->format('Y-m-d');

        return [$startDate, $endDate];
    }

    public function getFormattedDateAttribute()
    {
        return Carbon::parse($this->date)->format('Y-m-d');
    }

    public function getAmountFormattedAttribute()
    {
        return (new Money($this->amount))->formatted();
    }

    public function scopeTableView($query)
    {
        return $query
            ->when(request('sort') ?? null, function ($query) {
                $sort = explode(',', request('sort'));

                foreach ($sort as $item) {
                    list ($sortCol, $sortDir) = explode('|', $item);
                    $query->orderBy($sortCol, $sortDir);
                }
            })
            ->when(request('search') ?? null, function ($query) {
                $search = request('search');
                $query->where('description', 'LIKE', "%{$search}%");
            })
            ->when(request('filter') && request('filter') !== 'all', function ($query) {
                $filter = request('filter') === 'income' ? 'in' : 'out';
                $query->whereHas('category', function ($q) use ($filter) {
                    $q->where('type', $filter);
                });
            });
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

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}
