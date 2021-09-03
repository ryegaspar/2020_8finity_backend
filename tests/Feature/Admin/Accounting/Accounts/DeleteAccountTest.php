<?php

namespace Tests\Feature\Admin\Accounting\Accounts;

use App\Models\Account;
use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteAccountTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function only_authenticated_users_can_delete_an_account()
    {
        $admin = Admin::factory()->create();

        $account = Account::factory()->create();

        $this->actingAs($admin, 'admin')
            ->json('delete', "admin/accounting/accounts/{$account->id}")
            ->assertStatus(204);
    }

    /** @test */
    public function guests_cannot_delete_transaction()
    {
        $account = Account::factory()->create();

        $this->json('delete', "admin/accounting/accounts/{$account->id}")
            ->assertStatus(401);
    }

    /** @test */
    public function cannot_delete_first_account()
    {
        $admin = Admin::factory()->create();

        $account = Account::find(1);

        $this->actingAs($admin, 'admin')
            ->json('delete', "admin/accounting/accounts/{$account->id}")
            ->assertStatus(422);
    }

    /** @test */
    public function deleting_an_account()
    {
        $admin = Admin::factory()->create();

        $account = Account::factory()->create();

        $this->actingAs($admin, 'admin')
            ->json('delete', "admin/accounting/accounts/{$account->id}")
            ->assertStatus(204);

        $this->assertDatabaseMissing('accounts', ['id' => $account->id]);

        $this->assertEquals(1, Account::count());
    }

    // Todo
//    /** @test */
//    public function cannot_delete_if_account_has_transaction()
//    {
//        $admin = Admin::factory()->create();
//
//        $category = Category::factory()->create();
//
//        $transaction = Transaction::factory()->create([
//            'category_id' => $category->id,
//            'admin_id'    => $admin->id
//        ]);
//
//        $this->actingAs($admin, 'admin')
//            ->json('delete', "admin/accounting/categories/{$category->id}")
//            ->assertStatus(409);
//
//        $this->assertDatabaseHas('categories', ['id' => $category->id]);
//    }
}
