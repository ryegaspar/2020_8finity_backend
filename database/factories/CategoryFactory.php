<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Category;
use Faker\Generator as Faker;

$factory->define(Category::class, function (Faker $faker) {
    return [
        'description' => $faker->sentence,
        'icon'        => 'fa fa-money-bill'
    ];
});
