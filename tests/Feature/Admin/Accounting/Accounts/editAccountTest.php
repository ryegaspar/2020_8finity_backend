<?php

namespace Tests\Feature\Admin\Accounting\Accounts;

use App\Models\Account;
use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class editAccountTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function only_authenticated_users_can_update_accounts()
    {
        $admin = Admin::factory()->create();

        $account = Account::factory()->create(['name' => 'old']);

        $this->actingAs($admin, 'admin')
            ->json('patch', "admin/accounting/accounts/{$account->id}", ['name' => 'new'])
            ->assertStatus(204);
    }

    /** @test */
    public function guests_cannot_update_accounts()
    {
        $account = Account::factory()->create(['name' => 'old']);

        $this->json('patch', "admin/accounting/accounts/{$account->id}", ['name' => 'new'])
            ->assertStatus(401);
    }

    /** @test */
    public function updating_an_account()
    {
        $admin = Admin::factory()->create();
        $account = Account::factory()->create(['name' => 'old']);

        $response = $this->actingAs($admin, 'admin')
            ->json('patch', "admin/accounting/accounts/{$account->id}", ['name' => 'new']);

        tap(Account::latest()->first(), function ($account) use ($response, $admin) {
            $response->assertStatus(204);

            $this->assertEquals('new', $account->name);
        });
    }

    /** @test */
    public function name_is_required()
    {
        $admin = Admin::factory()->create();
        $account = Account::factory()->create(['name' => 'old']);

        $response = $this->actingAs($admin, 'admin')
            ->json('patch', "admin/accounting/accounts/{$account->id}", ['name' => '']);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('name');
    }
}
