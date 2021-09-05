<?php

namespace Tests\Feature\Admin\Accounting\Accounts;

use App\Models\Account;
use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EditAccountTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function only_authenticated_users_can_update_accounts()
    {
        $admin = Admin::factory()->create();

        $this->withExceptionHandling();

        $account = Account::factory()->create();

        $this->actingAs($admin, 'admin')
            ->json('patch', "admin/accounting/accounts/{$account->id}", ['name' => 'new', 'is_active' => true])
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

        $this->actingAs($admin, 'admin')
            ->json('patch', "admin/accounting/accounts/{$account->id}", ['name' => 'new', 'is_active' => false])
            ->assertStatus(204);

        tap(Account::latest()->first(), function ($account) use ($admin) {
            $this->assertEquals('new', $account->name);
            $this->assertFalse($account->is_active);
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

    /** @test */
    public function name_must_be_unique()
    {
        $admin = Admin::factory()->create();
        Account::factory()->create([
            'name' => 'old account'
        ]);

        $account = Account::factory()->create(['name' => 'new account']);

        $response = $this->actingAs($admin, 'admin')
            ->json('patch', "admin/accounting/accounts/{$account->id}", ['name' => 'old account']);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('name');

    }

    /** @test */
    public function status_must_be_boolean()
    {
        $admin = Admin::factory()->create();
        $account = Account::factory()->create(['name' => 'old']);

        $response = $this->actingAs($admin, 'admin')
            ->json('patch', "admin/accounting/accounts/{$account->id}", ['name' => 'new', 'is_active' => 'hello']);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('is_active');
    }

    /** @test */
    public function deactivating_account()
    {
        $admin = Admin::factory()->create();
        $account = Account::factory()->create(['name' => 'my account']);

        $response = $this->actingAs($admin, 'admin')
            ->json('patch', "admin/accounting/accounts/{$account->id}", ['name' => 'my account', 'is_active' => false]);

        $response->assertStatus(204);
        $this->assertFalse($account->fresh()->is_active);
    }

    /** @test */
    public function deactivating_account_requires_zero_balance_or_less()
    {
        $admin = Admin::factory()->create();
        $account = Account::factory()->create(['name' => 'my account', 'balance' => 100]);

        $response = $this->actingAs($admin, 'admin')
            ->json('patch', "admin/accounting/accounts/{$account->id}", ['name' => 'my account', 'is_active' => false]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('is_active');
    }

    /** @test
     * this test shows only that when is_active is changed, the error
     * 422 is shown when the account has balance.  if is_active is not changed
     * there will be no error.
     *
     * - only tracks the is_active value if it changed.
     */
    public function can_update_name_even_if_account_is_deactivated_with_a_non_zero_balance()
    {
        $this->withoutExceptionHandling();
        $admin = Admin::factory()->create();
        $account = Account::factory()->create(['name' => 'my account', 'balance' => 100, 'is_active' => false]);

        $response = $this->actingAs($admin, 'admin')
            ->json('patch', "admin/accounting/accounts/{$account->id}", ['name' => 'new account', 'is_active' => false]);

        $response->assertStatus(204);
        $this->assertEquals('new account', $account->fresh()->name);
    }
}
