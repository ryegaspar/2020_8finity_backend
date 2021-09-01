<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Admin;
use App\Models\Category;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition()
    {
        $category = Category::factory()->create();

        return [
            'category_id' => $category->id,
            'admin_id'    => Admin::factory(),
            'account_id'  => Account::factory(),
            'amount'      => 10000 * ($category->type === 'in' ? 1 : -1),
            'date'        => Carbon::now(),
        ];
    }
}
