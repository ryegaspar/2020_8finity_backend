<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Admin;
use App\Models\Category;
use App\Models\Check;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class CheckFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Check::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'category_id' => Category::factory(),
            'admin_id'    => Admin::factory(),
            'account_id'  => Account::factory(),
            'amount'      => 10000,
            'due_date'    => Carbon::today()->toDateString(),
        ];
    }
}
