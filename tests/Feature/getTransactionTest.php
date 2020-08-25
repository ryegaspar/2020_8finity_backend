<?php

namespace Tests\Feature;

use App\Category;
use App\Transaction;
use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class getTransactionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function only_authenticated_users_can_view_transactions()
    {
        $user = factory(User::class)->create();

        $this->actingAs($user)
            ->get('/transactions')
            ->assertStatus(200);
    }

    /** @test */
    public function guests_cannot_view_categories()
    {
        $this->get('/transactions')
            ->assertStatus(302);
    }

    /** @test */
    public function can_view_transactions()
    {
        $user = factory(User::class)->create();

        $transactionDay = Carbon::parse("first day of this month")->format("Y-m-d");
        $categoryIncome = factory(Category::class)->states('income')->create();
        $categoryExpense = factory(Category::class)->states('expense')->create();

        $transaction1 = Transaction::create([
            'category_id' => $categoryIncome->id,
            'amount'      => 12000,
            'date'        => $transactionDay
        ]);

        $transaction2 = Transaction::create([
            'category_id' => $categoryExpense->id,
            'amount'      => 8000,
            'date'        => $transactionDay
        ]);

        $this->actingAs($user)
            ->getJson('transactions')
            ->assertExactJson([
                'data' => [
                    [
                        'id'               => $transaction1->id,
                        'amount'           => "12000",
                        'amount_formatted' => "₱120.00",
                        'date'             => $transactionDay,
                        'category_type'    => 'income',
                        'category_name'    => $categoryIncome->description,
                        'category_id'      => "{$categoryIncome->id}"
                    ],
                    [
                        'id'               => $transaction2->id,
                        'amount'           => "8000",
                        'amount_formatted' => "₱80.00",
                        'date'             => $transactionDay,
                        'category_type'    => 'expense',
                        'category_name'    => $categoryExpense->description,
                        'category_id'      => "{$categoryExpense->id}"
                    ]
                ]
            ]);
    }
}