<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Admin;
use App\Models\Transfer;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransferFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Transfer::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'from_account' => Account::factory(),
            'to_account'   => Account::factory(),
            'admin_id'     => Admin::factory(),
            'amount'       => 1000,
            'description'  => $this->faker->sentence,
            'date'         => Carbon::today()->toDateString(),
        ];
    }
}
