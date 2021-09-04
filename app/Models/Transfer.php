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
}
