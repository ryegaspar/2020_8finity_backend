<?php

namespace Tests\Feature\Admin\Accounting\Transactions;

use App\Models\Account;
use App\Models\Admin;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\Transfer;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class GetTransferTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $account1;
    protected $account2;
    protected $day1;
    protected $day2;
    protected $transfer1;
    protected $transfer2;
    protected $transfer3;

    private function create_transfers()
    {
        $this->admin = Admin::factory()->create();

        $this->account1 = Account::factory()->create();
        $this->account2 = Account::factory()->create();
        $this->day1 = Carbon::parse("first day of this month")->format("Y-m-d");
        $this->day2 = Carbon::parse("first day of this month")->addDay()->format("Y-m-d");

        Transaction::factory()->create([
            'category_id' => Category::factory()->income(),
            'admin_id'    => $this->admin->id,
            'account_id'  => $this->account1->id,
            'amount'      => 10000,
            'date'        => $this->day1,
        ]);

        $this->transfer1 = Transfer::create([
            'from_account' => $this->account1->id,
            'to_account'   => $this->account2->id,
            'admin_id'     => $this->admin->id,
            'amount'       => 8000,
            'description'  => 'transfer1',
            'date'         => $this->day1
        ]);

        $this->transfer2 = Transfer::create([
            'from_account' => $this->account2->id,
            'to_account'   => $this->account1->id,
            'admin_id'     => $this->admin->id,
            'amount'       => 5000,
            'description'  => 'transfer2',
            'date'         => $this->day1
        ]);

        $this->transfer3 = Transfer::create([
            'from_account' => $this->account1->id,
            'to_account'   => $this->account2->id,
            'admin_id'     => $this->admin->id,
            'amount'       => 3000,
            'description'  => 'transfer3',
            'date'         => $this->day2
        ]);
    }

    /** @test */
    public function only_authenticated_users_can_view_transfers_page()
    {
        $admin = Admin::factory()->create();

        $this->actingAs($admin, 'admin')
            ->get('admin/accounting/transfers')
            ->assertStatus(200);
    }

    /** @test */
    public function guests_cannot_view_transfers_page()
    {
        $this->withHeaders(['accept' => 'application/json'])
            ->get('admin/accounting/transfers')
            ->assertStatus(401);
    }

    /** @test */
    public function can_view_transfers_and_is_ordered_by_date_by_default()
    {
        $this->create_transfers();

        $this->actingAs($this->admin, 'admin')
            ->get('admin/accounting/transfers')
            ->assertJson([
                'data' => [
                    [
                        'id'   => $this->transfer1->id,
                        'date' => $this->day1,
                    ],
                    [
                        'id'   => $this->transfer2->id,
                        'date' => $this->day1,
                    ],
                    [
                        'id'   => $this->transfer3->id,
                        'date' => $this->day2,
                    ],
                ]
            ]);
    }

    /** @test */
    public function has_paginated_data()
    {
        $admin = Admin::factory()->create();

        Transfer::factory()->create([
            'admin_id' => $admin->id
        ]);

        $this->actingAs($admin, 'admin')
            ->get('admin/accounting/transfers')
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
        $this->create_transfers();

        $this->actingAs($this->admin, 'admin')
            ->get('admin/accounting/transfers?sort=amount|asc')
            ->assertJson([
                'data' => [
                    [
                        'id'     => $this->transfer3->id,
                        'amount' => 3000,
                    ],
                    [
                        'id'     => $this->transfer2->id,
                        'amount' => 5000,
                    ],
                    [
                        'id'     => $this->transfer1->id,
                        'amount' => 8000,
                    ],
                ]
            ]);
    }

    /** @test */
    public function can_sort_by_date()
    {
        $this->create_transfers();

        $this->actingAs($this->admin, 'admin')
            ->get('admin/accounting/transfers?sort=date|asc')
            ->assertJson([
                'data' => [
                    [
                        'id'   => $this->transfer1->id,
                        'date' => $this->day1,
                    ],
                    [
                        'id'   => $this->transfer2->id,
                        'date' => $this->day1,
                    ],
                    [
                        'id'   => $this->transfer3->id,
                        'date' => $this->day2,
                    ],
                ]
            ]);
    }

    /** @test */
    public function can_filter_from_accounts()
    {
        $this->create_transfers();

        $response = $this->actingAs($this->admin, 'admin')
            ->get("admin/accounting/transfers?from_account={$this->account1->id}")
            ->assertJson([
                'data' => [
                    [
                        'id' => $this->transfer1->id,
                    ],
                    [
                        'id' => $this->transfer3->id
                    ]
                ]
            ])
            ->assertJsonMissing([
                'data' => [
                    [
                        'id' => $this->transfer2->id
                    ],
                ]
            ]);
    }

    /** @test */
    public function can_filter_to_accounts()
    {
        $this->create_transfers();

        $response = $this->actingAs($this->admin, 'admin')
            ->get("admin/accounting/transfers?to_account={$this->account1->id}")
            ->assertJson([
                'data' => [
                    [
                        'id' => $this->transfer2->id,
                    ],
                ]
            ])
            ->assertJsonMissing([
                'data' => [
                    [
                        'id' => $this->transfer1->id
                    ],
                    [
                        'id' => $this->transfer3->id
                    ]
                ]
            ]);
    }

    /** @test */
    public function can_search_through_description()
    {
        $this->create_transfers();

        $this->actingAs($this->admin, 'admin')
            ->get('admin/accounting/transfers?search=transfer2')
            ->assertJson([
                'data' => [
                    [
                        'id' => $this->transfer2->id,
                    ],
                ]
            ])
            ->assertJsonMissing([
                'data' => [
                    [
                        'id' => $this->transfer1->id
                    ],
                    [
                        'id' => $this->transfer3->id,
                    ],
                ]
            ]);
    }
}
