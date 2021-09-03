<?php

namespace Tests\Feature\Admin\Accounting\Transactions;

use App\Models\Account;
use App\Models\Admin;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TransactionObserverTest extends TestCase
{
    use RefreshDatabase;

    private function oldTransaction($overrides = [])
    {
        return array_merge([
            'description'   => 'old transaction',
            'category_id'   => 1,
            'account_id'    => 1,
            'amount'        => "100.25",
            'date'          => '2021-01-01',
            'notes'         => 'old note'
        ], $overrides);
    }

    private function newTransaction($overrides = [])
    {
        return array_merge([
            'description' => 'new transaction',
            'category_id' => 1,
            'account_id'  => 1,
            'amount'      => "200.25",
            'date'        => '2021-01-01',
            'notes'       => 'new note'
        ], $overrides);
    }

    /** @test */
    public function adding_a_transaction_with_an_income_category_has_positive_amount()
    {
        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->json('post', 'admin/accounting/transactions',
                $this->oldTransaction([
                    'category_id'   => Category::factory()->income()->create()->id,
                    'category_type' => 'income',
                    'amount'        => 100
                ])
            );

        tap(Transaction::first(), function ($transaction) use ($response, $admin) {
            $response->assertStatus(201);

            $this->assertGreaterThan(0, $transaction->amount);
            $this->assertEquals(10000, $transaction->amount);
        });
    }

    /** @test */
    public function adding_a_transaction_with_an_expense_category_has_negative_amount()
    {
        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->json('post', 'admin/accounting/transactions',
                $this->oldTransaction([
                    'category_id'   => Category::factory()->expense()->create()->id,
                    'category_type' => 'expense',
                    'amount'        => 100
                ])
            );

        tap(Transaction::first(), function ($transaction) use ($response, $admin) {
            $response->assertStatus(201);

            $this->assertLessThan(0, $transaction->amount);
            $this->assertEquals(-10000, $transaction->amount);
        });
    }

    /** @test */
    public function updating_a_transaction_with_an_income_category_has_positive_amount()
    {
        $admin = Admin::factory()->create();
        $transaction = Transaction::factory()->create($this->oldTransaction(['admin_id' => $admin->id]));

        $response = $this->actingAs($admin, 'admin')
            ->json('patch', "admin/accounting/transactions/{$transaction->id}",
                $this->newTransaction([
                    'category_id' => Category::factory()->income()->create(['name' => 'income'])->id,
                    'amount'      => 100
                ])
            );

        tap(Transaction::first(), function ($transaction) use ($response, $admin) {
            $response->assertStatus(204);

            $this->assertGreaterThan(0, $transaction->amount);
            $this->assertEquals(10000, $transaction->amount);
        });
    }

    /** @test */
    public function updating_a_transaction_with_an_expense_category_has_negative_amount()
    {
        $admin = Admin::factory()->create();
        $transaction = Transaction::factory()->create($this->oldTransaction(['admin_id' => $admin->id]));

        $response = $this->actingAs($admin, 'admin')
            ->json('patch', "admin/accounting/transactions/{$transaction->id}",
                $this->newTransaction([
                    'category_id'   => Category::factory()->expense()->create(['name' => 'expense'])->id,
                    'category_type' => 'expense',
                    'amount'        => 100
                ])
            );

        tap(Transaction::first(), function ($transaction) use ($response, $admin) {
            $response->assertStatus(204);

            $this->assertLessThan(0, $transaction->amount);
            $this->assertEquals(-10000, $transaction->amount);
        });
    }

}
