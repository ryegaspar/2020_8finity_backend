<?php

namespace Tests\Feature\Admin\Transactions;

use App\Models\Admin;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class deleteTransactionsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function only_authenticated_users_can_delete_their_transactions()
    {
        $admin = Admin::factory()->create();

        $transaction = Transaction::factory()->create(['admin_id' => $admin->id]);

        $this->actingAs($admin, 'admin')
            ->json('delete', "admin/transactions/{$transaction->id}")
            ->assertStatus(204);
    }

    /** @test */
    public function guests_cannot_delete_transaction()
    {
        $transaction = Transaction::factory()->create();

        $this->json('delete', "admin/transactions/{$transaction->id}")
            ->assertStatus(401);
    }

    /** @test */
    public function cannot_delete_transaction_if_not_owned()
    {
        $admin = Admin::factory()->create();

        $transaction = Transaction::factory()->create();

        $this->actingAs($admin, 'admin')
            ->json('delete', "admin/transactions/{$transaction->id}")
            ->assertStatus(404);
    }

    /** @test */
    public function deleting_a_transaction()
    {
        $admin = Admin::factory()->create();

        $transaction = Transaction::factory()->create(['admin_id' => $admin->id]);

        $this->actingAs($admin, 'admin')
            ->json('delete', "admin/transactions/{$transaction->id}")
            ->assertStatus(204);

        $this->assertDatabaseMissing('transactions', ['id' => $transaction->id]);

        $this->assertEquals(0, Transaction::count());
    }
}
