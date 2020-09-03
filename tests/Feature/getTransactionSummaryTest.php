<?php

namespace Tests\Feature;

use App\Category;
use App\Transaction;
use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class getTransactionSummaryTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function only_authenticated_users_can_view_transactions()
    {
        $user = factory(User::class)->create();

        $this->actingAs($user)
            ->get('/transactions/summary')
            ->assertStatus(200);
    }

    /** @test */
    public function guests_cannot_view_categories()
    {
        $this->get('/transactions/summary')
            ->assertStatus(302);
    }


    /** @test */
    public function can_view_transaction_summary()
    {
        $transactionDay1 = Carbon::now()->startOfMonth()->format("Y-m-d"); // first day of this month
        $transactionDay2 = Carbon::now()->startOfMonth()->addDays(14)->format("Y-m-d"); // 15th of the month

        $categoryIncome = factory(Category::class)->states('income')->create();
        $categoryExpense = factory(Category::class)->states('expense')->create();

        $transaction1 = Transaction::create([
            'category_id' => $categoryIncome->id,
            'amount'      => 12000,
            'date'        => $transactionDay1
        ]);

        $transaction2 = Transaction::create([
            'category_id' => $categoryExpense->id,
            'amount'      => 8000,
            'date'        => $transactionDay1
        ]);

        $transaction3 = Transaction::create([
            'category_id' => $categoryIncome->id,
            'amount'      => 13000,
            'date'        => $transactionDay2
        ]);

        $transaction4 = Transaction::create([
            'category_id' => $categoryExpense->id,
            'amount'      => 2000,
            'date'        => $transactionDay2
        ]);

        $user = factory(User::class)->create();

        $this->actingAs($user)
            ->getJson('transactions/summary')
            ->assertExactJson([
                'data' => [
                    'income'  => [
                        'amount'           => '25000',
                        'amount_formatted' => '₱250.00'
                    ],
                    'expense' => [
                        'amount'           => '10000',
                        'amount_formatted' => '₱100.00'
                    ],
                    'total'   => [
                        'amount'           => '15000',
                        'amount_formatted' => '₱150.00'
                    ]
                ]
            ]);
    }
}