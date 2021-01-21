<?php

namespace Tests\Feature\Admin\Transactions;

use App\Models\Admin;
use App\Models\Category;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class getTransactionsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_view_transactions()
    {
        $admin = Admin::factory()->create();

        $transactionDay1 = Carbon::parse("first day of this month")->format("Y-m-d");
        $transactionDay2 = Carbon::parse("first day of this month")->addDay()->format("Y-m-d");
        $categoryIncome = Category::factory()->income()->create();
        $categoryExpense = Category::factory()->expense()->create();

        $transaction1 = Transaction::create([
            'category_id' => $categoryIncome->id,
            'admin_id'    => $admin->id,
            'amount'      => 12000,
            'date'        => $transactionDay1
        ]);

        $transaction2 = Transaction::create([
            'category_id' => $categoryExpense->id,
            'admin_id'    => $admin->id,
            'amount'      => 8000,
            'date'        => $transactionDay1
        ]);

        $transaction3 = Transaction::create([
            'category_id' => $categoryIncome->id,
            'admin_id'    => $admin->id,
            'amount'      => 7000,
            'date'        => $transactionDay2
        ]);

        $this->actingAs($admin, 'admin')
            ->withHeaders(['accept' => 'application/json'])
            ->getJson('admin/transactions')
            ->assertJson([
                'data'         => [
                    [
                        'id'               => $transaction1->id,
                        'description'      => $transaction1->description,
                        'notes'            => $transaction1->notes,
                        'amount'           => "12000",
                        'amount_formatted' => "₱120.00",
                        'date'             => $transactionDay1,
                        'category_type'    => 'income',
                        'category_icon'    => $categoryIncome->icon,
                        'category_name'    => $categoryIncome->name,
                        'category_id'      => "{$categoryIncome->id}",
                        'admin_id'         => $admin->id,
                        'admin_first_name' => $admin->first_name,
                        'admin_last_name'  => $admin->last_name
                    ],
                    [
                        'id'               => $transaction2->id,
                        'description'      => $transaction2->description,
                        'notes'            => $transaction2->notes,
                        'amount'           => "8000",
                        'amount_formatted' => "₱80.00",
                        'date'             => $transactionDay1,
                        'category_type'    => 'expense',
                        'category_icon'    => $categoryExpense->icon,
                        'category_name'    => $categoryExpense->name,
                        'category_id'      => "{$categoryExpense->id}",
                        'admin_id'         => $admin->id,
                        'admin_first_name' => $admin->first_name,
                        'admin_last_name'  => $admin->last_name
                    ],
                    [
                        'id'               => $transaction3->id,
                        'description'      => $transaction3->description,
                        'notes'            => $transaction3->notes,
                        'amount'           => "7000",
                        'amount_formatted' => "₱70.00",
                        'date'             => $transactionDay2,
                        'category_type'    => 'income',
                        'category_icon'    => $categoryIncome->icon,
                        'category_name'    => $categoryIncome->name,
                        'category_id'      => "{$categoryIncome->id}",
                        'admin_id'         => $admin->id,
                        'admin_first_name' => $admin->first_name,
                        'admin_last_name'  => $admin->last_name
                    ],
                ]
            ]);
    }
}
