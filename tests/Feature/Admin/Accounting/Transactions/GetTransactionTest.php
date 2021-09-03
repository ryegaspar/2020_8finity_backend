<?php

namespace Tests\Feature\Admin\Accounting\Transactions;

use App\Models\Account;
use App\Models\Admin;
use App\Models\Category;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class GetTransactionTest extends TestCase
{
    use RefreshDatabase;

    protected $transactionDay1;
    protected $transactionDay2;
    protected $categoryIncome;
    protected $categoryExpense;
    protected $transaction1;
    protected $transaction2;
    protected $transaction3;
    protected $admin;
    protected $account;

    private function create_transactions()
    {
        $this->admin = Admin::factory()->create();

        $this->transactionDay1 = Carbon::parse("first day of this month")->format("Y-m-d");
        $this->transactionDay2 = Carbon::parse("first day of this month")->addDay()->format("Y-m-d");
        $this->categoryIncome = Category::factory()->income()->create();
        $this->categoryExpense = Category::factory()->expense()->create();

        $this->account = Account::factory()->create();

        $this->transaction1 = Transaction::create([
            'category_id' => $this->categoryIncome->id,
            'description' => 'transaction1',
            'admin_id'    => $this->admin->id,
            'account_id'  => $this->account->id,
            'amount'      => 12000,
            'date'        => $this->transactionDay1
        ]);

        $this->transaction2 = Transaction::create([
            'category_id' => $this->categoryExpense->id,
            'description' => 'transaction2',
            'admin_id'    => $this->admin->id,
            'account_id'  => $this->account->id,
            'amount'      => 8000,
            'date'        => $this->transactionDay1
        ]);

        $this->transaction3 = Transaction::create([
            'category_id' => $this->categoryIncome->id,
            'description' => 'transaction3',
            'account_id'  => $this->account->id,
            'admin_id'    => $this->admin->id,
            'amount'      => 7000,
            'date'        => $this->transactionDay2
        ]);
    }

    /** @test */
    public function only_authenticated_users_can_view_transactions_page()
    {
        $admin = Admin::factory()->create();

        $this->actingAs($admin, 'admin')
            ->get('admin/accounting/transactions')
            ->assertStatus(200);
    }

    /** @test */
    public function guests_cannot_view_transactions_page()
    {
        $this->withHeaders(['accept' => 'application/json'])
            ->get('admin/accounting/transactions')
            ->assertStatus(401);
    }

    /** @test */
    public function can_view_transactions_and_is_ordered_by_date_by_default()
    {
        $this->create_transactions();

        $this->actingAs($this->admin, 'admin')
            ->withHeaders(['accept' => 'application/json'])
            ->getJson('admin/accounting/transactions')
            ->assertJson([
                'data' => [
                    [
                        'id'   => $this->transaction1->id,
                        'date' => $this->transactionDay1,
                    ],
                    [
                        'id'   => $this->transaction2->id,
                        'date' => $this->transactionDay1,
                    ],
                    [
                        'id'   => $this->transaction3->id,
                        'date' => $this->transactionDay2,
                    ],
                ]
            ]);
    }

    /** @test */
    public function has_paginated_data()
    {
        $admin = Admin::factory()->create();

        Transaction::factory()->create([
            'admin_id' => $admin->id
        ]);

        $this->actingAs($admin, 'admin')
            ->withHeaders(['accept' => 'application/json'])
            ->getJson('admin/accounting/transactions')
            ->assertJsonStructure([
                'total',
                'per_page',
                'current_page',
                'last_page',
                'next_page_url',
                'prev_page_url',
                'from',
                'to',
                'data',
            ]);
    }

    /** @test */
    public function can_sort_by_amount()
    {
        $this->create_transactions();

        $this->actingAs($this->admin, 'admin')
            ->withHeaders(['accept' => 'application/json'])
            ->getJson('admin/accounting/transactions?sort=amount|asc')
            ->assertJson([
                'data' => [
                    [
                        'id'     => $this->transaction2->id,
                        'amount' => "-8000",
                    ],
                    [
                        'id'     => $this->transaction3->id,
                        'amount' => "7000",
                    ],
                    [
                        'id'     => $this->transaction1->id,
                        'amount' => "12000",
                    ],
                ]
            ]);
    }

    /** @test */
    public function can_sort_by_date()
    {
        $this->create_transactions();

        $this->actingAs($this->admin, 'admin')
            ->withHeaders(['accept' => 'application/json'])
            ->getJson('admin/accounting/transactions?sort=date|asc')
            ->assertJson([
                'data' => [
                    [
                        'id'   => $this->transaction1->id,
                        'date' => $this->transactionDay1,
                    ],
                    [
                        'id'   => $this->transaction2->id,
                        'date' => $this->transactionDay1,
                    ],
                    [
                        'id'   => $this->transaction3->id,
                        'date' => $this->transactionDay2,
                    ],
                ]
            ]);
    }

    /** @test */
    public function can_filter_income_only()
    {
        $this->create_transactions();

        $this->actingAs($this->admin, 'admin')
            ->withHeaders(['accept' => 'application/json'])
            ->getJson('admin/accounting/transactions?type=income')
            ->assertJson([
                'data' => [
                    [
                        'id' => $this->transaction1->id,
                    ],
                    [
                        'id' => $this->transaction3->id,
                    ],
                ]
            ])
            ->assertJsonMissing([
                'data' => [
                    'id' => $this->transaction2->id
                ]
            ]);
    }

    /** @test */
    public function can_filter_expenses_only()
    {
        $this->create_transactions();

        $this->actingAs($this->admin, 'admin')
            ->withHeaders(['accept' => 'application/json'])
            ->getJson('admin/accounting/transactions?type=expense')
            ->assertJson([
                'data' => [
                    [
                        'id' => $this->transaction2->id,
                    ],
                ]
            ])
            ->assertJsonMissing([
                'data' => [
                    [
                        'id' => $this->transaction1->id
                    ],
                    [
                        'id' => $this->transaction3->id,
                    ],
                ]
            ]);
    }

    /** @test */
    public function can_search_through_description()
    {
        $this->create_transactions();

        $this->actingAs($this->admin, 'admin')
            ->withHeaders(['accept' => 'application/json'])
            ->getJson('admin/accounting/transactions?search=transaction2')
            ->assertJson([
                'data' => [
                    [
                        'id' => $this->transaction2->id,
                    ],
                ]
            ])
            ->assertJsonMissing([
                'data' => [
                    [
                        'id' => $this->transaction1->id
                    ],
                    [
                        'id' => $this->transaction3->id,
                    ],
                ]
            ]);
    }
}
