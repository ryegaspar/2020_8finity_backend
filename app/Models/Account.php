<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeTableView($query)
    {
        return $query->when(request('sort') ?? null, function ($query) {
            $sort = explode(',', request('sort'));

            foreach ($sort as $item) {
                list ($sortCol, $sortDir) = explode('|', $item);
                $query->orderBy($sortCol, $sortDir);
            }
        });
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
