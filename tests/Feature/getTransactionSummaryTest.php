<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Category;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class getTransactionSummaryTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function only_authenticated_users_can_view_transactions()
    {
        $admin = Admin::factory()->create();

        $this->actingAs($admin, 'admin')
            ->withHeaders(['accept' => 'application/json'])
            ->get('admin/transactions/summary')
            ->assertStatus(200);
    }

    /** @test */
    public function guests_cannot_view_categories()
    {
        $this->withHeaders(['accept' => 'application/json'])
            ->get('admin/transactions/summary')
            ->assertStatus(401);
    }

    /** @test */
    public function can_view_transaction_summary()
    {
        $transactionDay1 = Carbon::now()->startOfMonth()->format("Y-m-d"); // first day of this month
        $transactionDay2 = Carbon::now()->startOfMonth()->addDays(14)->format("Y-m-d"); // 15th of the month

        $categoryIncome = Category::factory()->income()->create();
        $categoryExpense = Category::factory()->expense()->create();

        $admin = Admin::factory()->create();

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
            'amount'      => 13000,
            'date'        => $transactionDay2
        ]);

        $transaction4 = Transaction::create([
            'category_id' => $categoryExpense->id,
            'admin_id'    => $admin->id,
            'amount'      => 2000,
            'date'        => $transactionDay2
        ]);

        $this->actingAs($admin, 'admin')
            ->withHeaders(['accept' => 'application/json'])
            ->getJson('admin/transactions/summary')
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
