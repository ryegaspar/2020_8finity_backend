<?php

namespace Tests\Feature\Admin\Accounting\Checks;

use App\Models\Account;
use App\Models\Admin;
use App\Models\Category;
use App\Models\Check;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetCheckTest extends TestCase
{
    use RefreshDatabase;

    protected $checkDay1;
    protected $checkDay2;
    protected $categoryIncome;
    protected $categoryExpense;
    protected $check1;
    protected $check2;
    protected $check3;
    protected $admin;
    protected $account;

    private function create_checks()
    {
        $this->admin = Admin::factory()->create();

        $this->checkDay1 = Carbon::parse("first day of this month")->addMonth()->format("Y-m-d");
        $this->checkDay2 = Carbon::parse("first day of this month")->addMonth()->addDay()->format("Y-m-d");
        $this->categoryIncome = Category::factory()->income()->create();
        $this->categoryExpense = Category::factory()->expense()->create();

        $this->account = Account::factory()->create();

        $this->check1 = Check::create([
            'category_id' => $this->categoryIncome->id,
            'description' => 'check1',
            'admin_id'    => $this->admin->id,
            'account_id'  => $this->account->id,
            'amount'      => 12000,
            'post_date'   => $this->checkDay1
        ]);

        $this->check2 = Check::create([
            'category_id' => $this->categoryExpense->id,
            'description' => 'check2',
            'admin_id'    => $this->admin->id,
            'account_id'  => $this->account->id,
            'amount'      => 8000,
            'post_date'   => $this->checkDay1
        ]);

        $this->check3 = Check::create([
            'category_id' => $this->categoryIncome->id,
            'description' => 'check3',
            'account_id'  => $this->account->id,
            'admin_id'    => $this->admin->id,
            'amount'      => 7000,
            'post_date'   => $this->checkDay2
        ]);
    }

    /** @test */
    public function only_authenticated_users_can_view_checks_page()
    {
        $admin = Admin::factory()->create();

        $this->actingAs($admin, 'admin')
            ->get('admin/accounting/checks')
            ->assertStatus(200);
    }

    /** @test */
    public function guests_cannot_view_checks_page()
    {
        $this->withHeaders(['accept' => 'application/json'])
            ->get('admin/accounting/checks')
            ->assertStatus(401);
    }

    /** @test */
    public function can_view_transactions_and_is_ordered_by_date_by_default()
    {
        $this->create_checks();

        $this->actingAs($this->admin, 'admin')
            ->get('admin/accounting/checks')
            ->assertJson([
                'data' => [
                    [
                        'id'        => $this->check1->id,
                        'post_date' => $this->checkDay1,
                    ],
                    [
                        'id'        => $this->check2->id,
                        'post_date' => $this->checkDay1,
                    ],
                    [
                        'id'        => $this->check3->id,
                        'post_date' => $this->checkDay2,
                    ],
                ]
            ]);
    }

    /** @test */
    public function has_paginated_data()
    {
        $admin = Admin::factory()->create();

        Check::factory()->create([
            'admin_id' => $admin->id
        ]);

        $this->actingAs($admin, 'admin')
            ->get('admin/accounting/checks')
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
        $this->create_checks();

        $this->actingAs($this->admin, 'admin')
            ->get('admin/accounting/checks?sort=amount|asc')
            ->assertJson([
                'data' => [
                    [
                        'id'     => $this->check2->id,
                        'amount' => "-8000",
                    ],
                    [
                        'id'     => $this->check3->id,
                        'amount' => "7000",
                    ],
                    [
                        'id'     => $this->check1->id,
                        'amount' => "12000",
                    ],
                ]
            ]);
    }

    /** @test */
    public function can_sort_by_date()
    {
        $this->create_checks();

        $this->actingAs($this->admin, 'admin')
            ->get('admin/accounting/checks?sort=post_date|asc')
            ->assertJson([
                'data' => [
                    [
                        'id'        => $this->check1->id,
                        'post_date' => $this->checkDay1,
                    ],
                    [
                        'id'        => $this->check2->id,
                        'post_date' => $this->checkDay1,
                    ],
                    [
                        'id'        => $this->check3->id,
                        'post_date' => $this->checkDay2,
                    ],
                ]
            ]);
    }

    /** @test */
    public function can_filter_income_only()
    {
        $this->create_checks();

        $this->actingAs($this->admin, 'admin')
            ->get('admin/accounting/checks?type=income')
            ->assertJson([
                'data' => [
                    [
                        'id' => $this->check1->id,
                    ],
                    [
                        'id' => $this->check3->id,
                    ],
                ]
            ])
            ->assertJsonMissing([
                'data' => [
                    'id' => $this->check2->id
                ]
            ]);
    }

    /** @test */
    public function can_filter_expenses_only()
    {
        $this->create_checks();

        $this->actingAs($this->admin, 'admin')
            ->get('admin/accounting/checks?type=expense')
            ->assertJson([
                'data' => [
                    [
                        'id' => $this->check2->id,
                    ],
                ]
            ])
            ->assertJsonMissing([
                'data' => [
                    [
                        'id' => $this->check1->id
                    ],
                    [
                        'id' => $this->check3->id,
                    ],
                ]
            ]);
    }

    /** @test */
    public function can_search_through_description()
    {
        $this->create_checks();

        $this->actingAs($this->admin, 'admin')
            ->get('admin/accounting/checks?search=check2')
            ->assertJson([
                'data' => [
                    [
                        'id' => $this->check2->id,
                    ],
                ]
            ])
            ->assertJsonMissing([
                'data' => [
                    [
                        'id' => $this->check1->id
                    ],
                    [
                        'id' => $this->check3->id,
                    ],
                ]
            ]);
    }
}
