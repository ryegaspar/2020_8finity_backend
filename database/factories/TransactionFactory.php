<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Transaction;
use Carbon\Carbon;
use Faker\Generator as Faker;

$factory->define(Transaction::class, function (Faker $faker) {
    return [
        'amount' => 10000,
        'date' => Carbon::parse("-2 weeks"),
    ];
});
