<?php

namespace Tests\Feature\Admin\Accounting\Transactions;

use App\Models\Account;
use App\Models\Admin;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\Transfer;
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

    /** @test */
    public function deleting_a_transaction_to_an_account_that_has_transfers_have_correct_account_balance()
    {
        $admin = Admin::factory()->create();
        $account1 = Account::factory()->create();
        $account2 = Account::factory()->create();
        $category = Category::factory()->income()->create();

        Transaction::factory()->create([
            'account_id' => $account1->id,
            'category_id' => $category->id,
            'amount' => 10000
        ]);

        $transaction = Transaction::factory()->create([
            'admin_id'    => $admin->id,
            'account_id'  => $account1->id,
            'category_id' => $category->id,
            'amount'      => 10000
        ]);

        $this->assertEquals(20000, $account1->fresh()->balance);

        Transfer::factory()->create([
            'from_account' => $account1->id,
            'to_account'   => $account2->id,
            'admin_id'     => $admin->id,
            'amount'       => 1000,
        ]);

        $this->assertEquals(19000, $account1->fresh()->balance);
        $this->assertEquals(1000, $account2->fresh()->balance);

        $this->actingAs($admin, 'admin')
            ->json('delete', "admin/accounting/transactions/{$transaction->id}");

        $this->assertEquals(9000, $account1->fresh()->balance);
        $this->assertEquals(1000, $account2->fresh()->balance);
    }
}
