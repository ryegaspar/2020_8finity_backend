<?php

namespace Tests\Feature\Admin\Accounting\Accounts;

use App\Models\Account;
use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetAccountTest extends TestCase
{
    use RefreshDatabase;

    private $defaultValue = [
        [
            'id'            => 1,
            'name'          => 'default',
            'balance'       => 0,
            'check_balance' => 0,
            'is_active'     => true,
        ],
    ];

    /** @test */
    public function only_authenticated_users_can_view_accounts()
    {
        $admin = Admin::factory()->create();

        $this->actingAs($admin, 'admin')
            ->get('admin/accounting/accounts')
            ->assertStatus(200);
    }

    /** @test */
    public function guests_cannot_view_accounts()
    {
        $this->withHeaders(['accept' => 'application/json'])
            ->get('admin/accounting/accounts')
            ->assertStatus(401);
    }

    /** @test */
    public function can_get_all_accounts()
    {
        $admin = Admin::factory()->create();

        $this->actingAs($admin, 'admin')
            ->get('admin/accounting/accounts')
            ->assertStatus(200)
            ->assertExactJson([
                'data' => $this->defaultValue
            ]);
    }

    /** @test */
    public function can_get_active_accounts()
    {
        $admin = Admin::factory()->create();

        Account::factory()->create(['is_active' => false]);

        $response = $this->actingAs($admin, 'admin')
            ->get('admin/accounting/accounts/?active')
            ->assertStatus(200)
            ->assertExactJson([
                'data' => $this->defaultValue
            ]);

    }

    /** @test */
    public function can_sorted_by_name()
    {
        $admin = Admin::factory()->create();

        $account = Account::factory()->create(['name' => 'a_first']);

        $this->actingAs($admin, 'admin')
            ->get('admin/accounting/accounts?sort=name|asc')
            ->assertJson([
                'data' => [
                    [
                        'id'   => $account->id,
                        'name' => "a_first",
                    ],
                    [
                        'id'   => 1,
                        'name' => "default",
                    ],
                ]
            ]);
    }
}
