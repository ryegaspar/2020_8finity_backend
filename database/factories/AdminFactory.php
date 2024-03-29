<?php

namespace Database\Factories;


use App\Models\Admin;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class AdminFactory extends Factory
{
    protected $model = Admin::class;

    public function definition()
    {
        return [
            'first_name'        => $this->faker->firstName,
            'last_name'         => $this->faker->lastName,
            'username'          => $this->faker->userName,
            'email'             => $this->faker->unique()->safeEmail,
            'email_verified_at' => now(),
            'password'          => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'is_active'         => true,
            'remember_token'    => Str::random(10),
        ];
    }
}
