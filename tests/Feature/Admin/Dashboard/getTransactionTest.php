<?php

namespace Tests\Feature\Admin\Dashboard;

use App\Models\Account;
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
    public function only_authenticated_users_can_view_dashboard_transactions()
    {
        $admin = Admin::factory()->create();

        $this->actingAs($admin, 'admin')
            ->get('admin/dashboard/transactions')
            ->assertStatus(200);
    }

    /** @test */
    public function guests_cannot_view_dashboard_transactions()
    {
        $this->withHeaders(['accept' => 'application/json'])
            ->get('admin/dashboard/transactions')
            ->assertStatus(401);
    }

    /** @test */
    public function can_view_transactions()
    {
        $admin = Admin::factory()->create();

        $transactionDay = Carbon::parse("first day of this month")->format("Y-m-d");
        $categoryIncome = Category::factory()->income()->create();
        $categoryExpense = Category::factory()->expense()->create();

        $account = Account::factory()->create();

        $transaction1 = Transaction::create([
            'category_id' => $categoryIncome->id,
            'admin_id'    => $admin->id,
            'account_id'  => $account->id,
            'amount'      => 12000,
            'date'        => $transactionDay
        ]);

        $transaction2 = Transaction::create([
            'category_id' => $categoryExpense->id,
            'admin_id'    => $admin->id,
            'account_id'  => $account->id,
            'amount'      => 8000,
            'date'        => $transactionDay
        ]);

        $this->actingAs($admin, 'admin')
            ->withHeaders(['accept' => 'application/json'])
            ->getJson('admin/dashboard/transactions')
            ->assertExactJson([
                'data' => [
                    [
                        'id'               => $transaction1->id,
                        'description'      => $transaction1->description,
                        'notes'            => $transaction1->notes,
                        'amount'           => "12000",
                        'date'             => $transactionDay,
                        'category_type'    => 'income',
                        'category_icon'    => $categoryIncome->icon,
                        'category_name'    => $categoryIncome->name,
                        'category_id'      => "{$categoryIncome->id}",
                        'admin_id'         => $admin->id,
                        'admin_username'   => $admin->username,
                        'account_id'       => $account->id,
                        'account_name'     => $account->name,
                        'account_status'   => $account->is_active
                    ],
                    [
                        'id'               => $transaction2->id,
                        'description'      => $transaction2->description,
                        'notes'            => $transaction2->notes,
                        'amount'           => "-8000",
                        'date'             => $transactionDay,
                        'category_type'    => 'expense',
                        'category_icon'    => $categoryExpense->icon,
                        'category_name'    => $categoryExpense->name,
                        'category_id'      => "{$categoryExpense->id}",
                        'admin_id'         => $admin->id,
                        'admin_username'   => $admin->username,
                        'account_id'       => $account->id,
                        'account_name'     => $account->name,
                        'account_status'   => $account->is_active
                    ]
                ]
            ]);
    }
}
