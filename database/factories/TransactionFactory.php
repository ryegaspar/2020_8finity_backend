<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Category;
use App\Transaction;
use Carbon\Carbon;
use Faker\Generator as Faker;

$factory->define(Transaction::class, function (Faker $faker) {
    return [
        'category_id' => factory(Category::class),
        'amount'      => 10000,
        'date'        => Carbon::parse("-2 weeks"),
    ];
});
