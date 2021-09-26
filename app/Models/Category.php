<?php

namespace App\Models;

use App\Logger\Loggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory, Loggable;

    public $loggable_actions = ['created', 'updated', 'deleted'];
    public $loggable_fields = ['name', 'type', 'icon'];

    public const EXPENSE = 'out';
    public const INCOME = 'in';

    protected $guarded = [];

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

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function checks()
    {
        return $this->hasMany(Check::class);
    }
}
