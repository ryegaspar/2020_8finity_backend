<?php

namespace Tests\Feature\Admin\Accounting\Logs;

use App\Models\Account;
use App\Models\Admin;
use App\Models\Category;
use App\Models\Log;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetLogTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    private function makeLogEntries()
    {
        $this->admin = Admin::factory()->create();

        $account = Account::factory()->create();
        $category = Category::factory()->income()->create();
        $transaction = Transaction::factory()->create([
            'category_id' => $category->id,
            'account_id'  => $account->id,
            'amount'      => 50
        ]);

        $transaction->update([
            'amount' => 75
        ]);

        Log::find(1)->update([
            'created_at' => Carbon::parse("first day of this month")->timestamp,
            'admin_id'   => $this->admin->id,
        ]);

        Log::find(2)->update([
            'created_at' => Carbon::parse("first day of this month")->addDays(1)->timestamp,
            'admin_id'   => $this->admin->id,
        ]);

        Log::find(3)->update([
            'created_at' => Carbon::parse("first day of this month")->addDays(2)->timestamp,
            'admin_id'   => $this->admin->id,
        ]);

        Log::find(4)->update([
            'created_at' => Carbon::parse("first day of this month")->addDays(3)->timestamp,
            'admin_id'   => $this->admin->id,
        ]);
    }

    /** @test */
    public function only_authenticated_users_can_view_logs_page()
    {
        $admin = Admin::factory()->create();

        $this->actingAs($admin, 'admin')
            ->get('admin/accounting/logs')
            ->assertStatus(200);
    }

    /** @test */
    public function guests_cannot_view_logs_page()
    {
        $this->withHeaders(['accept' => 'application/json'])
            ->get('admin/accounting/logs')
            ->assertStatus(401);

        $this->makeLogEntries();
    }

    /** @test */
    public function can_view_logs_and_is_ordered_by_id_asc_by_default()
    {
        $this->makeLogEntries();

        $this->actingAs($this->admin, 'admin')
            ->get('admin/accounting/logs')
            ->assertJson([
                'data' => [
                    [
                        'id'            => 1,
                        'loggable_type' => 'Account'
                    ],
                    [
                        'id'            => 2,
                        'loggable_type' => 'Category'
                    ],
                    [
                        'id'            => 3,
                        'loggable_type' => 'Transaction',
                    ],
                    [
                        'id'            => 4,
                        'loggable_type' => 'Transaction',
                    ],
                ]
            ]);
    }

    /** @test */
    public function has_paginated_data()
    {
        $admin = Admin::factory()->create();

        $this->actingAs($admin, 'admin')
            ->get('admin/accounting/logs')
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
    public function can_sort_by_created_at()
    {
        $this->makeLogEntries();

        // TODO: having problems with timestamps, so i tested this with the order of logs
        $this->actingAs($this->admin, 'admin')
            ->get('admin/accounting/logs?sort=created_at|desc')
            ->assertJson([
                'data' => [
                    [
                        'id'            => 4,
                        'loggable_type' => 'Transaction',
                    ],
                    [
                        'id'            => 3,
                        'loggable_type' => 'Transaction',
                    ],
                    [
                        'id'            => 2,
                        'loggable_type' => 'Category',
                    ],
                    [
                        'id'            => 1,
                        'loggable_type' => 'Account',
                    ],
                ]
            ]);
    }

    /** @test */
    public function can_filter_by_type()
    {
        $this->makeLogEntries();

        $this->actingAs($this->admin, 'admin')
            ->get('admin/accounting/logs?type=Transaction')
            ->assertJson([
                'data' => [
                    [
                        'id' => 3,
                    ],
                    [
                        'id' => 4,
                    ],
                ]
            ])
            ->assertJsonMissing([
                'data' => [
                    [
                        'id' => 1,
                    ],
                    [
                        'id' => 2
                    ]
                ]
            ]);
    }

    /** @test */
    public function can_filter_by_action()
    {
        $this->makeLogEntries();

        $this->actingAs($this->admin, 'admin')
            ->get('admin/accounting/logs?action=updated')
            ->assertJson([
                'data' => [
                    [
                        'id' => 4,
                    ],
                ]
            ])
            ->assertJsonMissing([
                'data' => [
                    [
                        'id' => 3
                    ],
                    [
                        'id' => 2
                    ],
                    [
                        'id' => 1
                    ],
                ]
            ]);
    }
}
