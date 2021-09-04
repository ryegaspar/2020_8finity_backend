<?php

namespace Tests\Feature\Admin\Accounting\Transactions;

use App\Models\Account;
use App\Models\Admin;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteTransactionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function only_authenticated_users_can_delete_their_transactions()
    {
        $admin = Admin::factory()->create();

        $transaction = Transaction::factory()->create(['admin_id' => $admin->id]);

        $this->actingAs($admin, 'admin')
            ->json('delete', "admin/accounting/transactions/{$transaction->id}")
            ->assertStatus(204);
    }

    /** @test */
    public function guests_cannot_delete_transaction()
    {
        $transaction = Transaction::factory()->create();

        $this->json('delete', "admin/accounting/transactions/{$transaction->id}")
            ->assertStatus(401);
    }

    /** @test */
    public function cannot_delete_transaction_if_not_owned()
    {
        $admin = Admin::factory()->create();

        $transaction = Transaction::factory()->create();

        $this->actingAs($admin, 'admin')
            ->json('delete', "admin/accounting/transactions/{$transaction->id}")
            ->assertStatus(403);
    }

    /** @test */
    public function deleting_a_transaction()
    {
        $admin = Admin::factory()->create();

        $transaction = Transaction::factory()->create(['admin_id' => $admin->id]);

        $this->actingAs($admin, 'admin')
            ->json('delete', "admin/accounting/transactions/{$transaction->id}")
            ->assertStatus(204);

        $this->assertDatabaseMissing('transactions', ['id' => $transaction->id]);

        $this->assertEquals(0, Transaction::count());
    }

    /** @test */
    public function deleting_a_transaction_updates_account_balance()
    {
        $admin = Admin::factory()->create();

        $account = Account::factory()->create();
        $category = Category::factory()->income()->create();

        $transaction = Transaction::factory()->create([
            'admin_id'    => $admin->id,
            'account_id'  => $account->id,
            'category_id' => $category->id,
            'amount'      => 10000
        ]);

        $this->assertEquals(10000, $account->fresh()->balance);

        $this->actingAs($admin, 'admin')
            ->json('delete', "admin/accounting/transactions/{$transaction->id}");

        $this->assertEquals(0, $account->fresh()->balance);
    }
}
