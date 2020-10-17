<?php

namespace App\Models;

use App\ExpenseTracker\Money;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use JamesDordoy\LaravelVueDatatable\Traits\LaravelVueDatatableTrait;

class Transaction extends Model
{
    use HasFactory, LaravelVueDatatableTrait;

    protected $guarded = [];

    protected $dataTableColumns = [
        'id'          => [
            'searchable' => false,
        ],
        'description' => [
            'searchable' => true,
        ],
        'amount'      => [
            'orderable'  => true,
            'searchable' => false,
        ],
        'date'        => [
            'orderable'  => true,
            'searchable' => false,
        ],
    ];

    protected $dataTableRelationships = [
        "belongsTo" => [
            "category" => [
                "model"       => \App\Models\Category::class,
                "foreign_key" => 'category_id',
                "columns"     => [
                    "type" => [
                        "searchable" => false,
                        "orderable"  => true
                    ],
                    "name" => [
                        "searchable" => false,
                        "orderable"  => true
                    ]
                ]
            ]
        ]
    ];

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

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public static function sumByCategoryTypeBetween($type = 'in', $startDate = null, $endDate = null)
    {
        [$startDate, $endDate] = self::getValidDate($startDate, $endDate);

        return self::whereHas('category', function ($query) use ($type) {
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

    public static function transactionsBetween($startDate = null, $endDate = null)
    {
        [$startDate, $endDate] = self::getValidDate($startDate, $endDate);

        return self::with(['category', 'admin'])
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'desc')
            ->get();
    }

    protected static function getValidDate($startDate = null, $endDate = null)
    {
        $startDate = ($startDate ? Carbon::parse($startDate) : Carbon::now()->startOfMonth())->format('Y-m-d');
        $endDate = ($endDate ? Carbon::parse($endDate) : Carbon::today()->endOfMonth())->format('Y-m-d');

        return [$startDate, $endDate];
    }
}
