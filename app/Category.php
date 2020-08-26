<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    public const EXPENSE = 'out';
    public const INCOME = 'in';

    protected $guarded = [];
}
