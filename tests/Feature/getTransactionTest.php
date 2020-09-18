<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Category;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class getTransactionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function only_authenticated_users_can_view_transactions()
    {
        $admin = Admin::factory()->create();

        $this->actingAs($admin, 'admin')
            ->get('/transactions')
            ->assertStatus(200);
    }

    /** @test */
    public function guests_cannot_view_categories()
    {
        $this->withHeaders(['accept' => 'application/json'])
            ->get('/transactions')
            ->assertStatus(401);
    }

    /** @test */
    public function can_view_transactions()
    {
        $admin = Admin::factory()->create();

        $transactionDay = Carbon::parse("first day of this month")->format("Y-m-d");
        $categoryIncome = Category::factory()->income()->create();
        $categoryExpense = Category::factory()->expense()->create();

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

        $this->actingAs($admin, 'admin')
            ->withHeaders(['accept' => 'application/json'])
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
