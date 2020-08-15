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

$factory->state(Category::class, 'income', function ($faker) {
    return [
        'type'        => 'in',
        'description' => 'salary',
        'icon'        => 'fa fa-money-bill'
    ];
});

$factory->state(Category::class, 'expense', function ($faker) {
    return [
        'type'        => 'out',
        'description' => 'shopping',
        'icon'        => 'fa fa-shopping-cart'
    ];
});
