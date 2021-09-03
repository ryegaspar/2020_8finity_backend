<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition()
    {
        return [
            'type' => 'in',
            'name' => $this->faker->sentence,
            'icon' => 'fa fa-money-bill'
        ];
    }

    public function income()
    {
        return $this->state([
            'type' => 'in',
            'name' => $this->faker->name,
            'icon' => 'fa fa-money-bill'
        ]);
    }

    public function expense()
    {
        return $this->state([
            'type' => 'out',
            'name' => $this->faker->name,
            'icon' => 'fa fa-shopping-cart'
        ]);
    }
}
