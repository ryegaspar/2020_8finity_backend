<?php

namespace Tests\Feature\Admin\Accounting\Accounts;

use App\Models\Account;
use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AddAccountTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function only_authenticated_users_can_add_accounts()
    {
        $admin = Admin::factory()->create();

        $this->actingAs($admin, 'admin')
            ->json('post', 'admin/accounting/accounts', ['name' => 'new'])
            ->assertStatus(201);
    }

    /** @test */
    public function guests_cannot_add_accounts()
    {
        $this->json('post', 'admin/accounting/accounts', ['name' => 'new'])
            ->assertStatus(401);
    }

    /** @test */
    public function adding_a_accounts()
    {
        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->json('post', 'admin/accounting/accounts', ['name' => 'new']);

        tap(Account::latest()->first(), function ($account) use ($response, $admin) {
            $response->assertStatus(201);

            $this->assertEquals('new', $account->name);
            $this->assertTrue($account->is_active);
        });
    }

    /** @test */
    public function name_is_required()
    {
        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->json('post', 'admin/accounting/accounts', ['name' => '']);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('name');
    }

    /** @test */
    public function name_must_be_unique()
    {
        $admin = Admin::factory()->create();
        Account::factory()->create([
            'name' => 'new account'
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->json('post', 'admin/accounting/accounts', ['name' => 'new account']);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('name');

    }
}
