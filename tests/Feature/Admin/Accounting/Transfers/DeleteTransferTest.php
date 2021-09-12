<?php

namespace Tests\Feature\Admin\Accounting\Transactions;

use App\Models\Account;
use App\Models\Admin;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\Transfer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteTransferTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function only_authenticated_users_can_delete_their_transfers()
    {
        $admin = Admin::factory()->create();

        $transfer = Transfer::factory()->create(['admin_id' => $admin->id]);

        $this->actingAs($admin, 'admin')
            ->json('delete', "admin/accounting/transfers/{$transfer->id}")
            ->assertStatus(204);
    }

    /** @test */
    public function guests_cannot_delete_a_transfer()
    {
        $transfer = Transfer::factory()->create();

        $this->json('delete', "admin/accounting/transfers/{$transfer->id}")
            ->assertStatus(401);
    }

    /** @test */
    public function cannot_delete_transfer_if_not_owned()
    {
        $admin = Admin::factory()->create();

        $transfer = Transfer::factory()->create();

        $this->actingAs($admin, 'admin')
            ->json('delete', "admin/accounting/transfers/{$transfer->id}")
            ->assertStatus(403);
    }

    /** @test */
    public function deleting_a_transaction()
    {
        $admin = Admin::factory()->create();

        $transfer = Transfer::factory()->create(['admin_id' => $admin->id]);

        $this->actingAs($admin, 'admin')
            ->json('delete', "admin/accounting/transfers/{$transfer->id}")
            ->assertStatus(204);

        $this->assertDatabaseMissing('transfers', ['id' => $transfer->id]);

        $this->assertEquals(0, Transaction::count());
    }

    /** @test */
    public function deleting_a_transfer_updates_account_balance()
    {
        $admin = Admin::factory()->create();
        $account1 = Account::factory()->create();
        $account2 = Account::factory()->create();

        Transaction::factory()->create([
            'account_id'  => $account1->id,
            'admin_id'    => $admin->id,
            'category_id' => Category::factory()->income(),
            'amount'      => 10000
        ]);

        $transfer = Transfer::factory()->create([
            'from_account' => $account1->id,
            'to_account'   => $account2->id,
            'admin_id'     => $admin->id,
            'amount'       => 2000
        ]);

        $this->assertEquals(8000, $account1->fresh()->balance);
        $this->assertEquals(2000, $account2->fresh()->balance);

        $this->actingAs($admin, 'admin')
            ->json('delete', "admin/accounting/transfers/{$transfer->id}");

        $this->assertEquals(10000, $account1->fresh()->balance);
        $this->assertEquals(0, $account2->fresh()->balance);
    }
}
