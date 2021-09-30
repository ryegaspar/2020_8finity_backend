<?php

namespace Tests\Feature\Admin\Accounting\Transactions;

use App\Models\Admin;
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
    public function deleting_a_transfer()
    {
        $admin = Admin::factory()->create();

        $transfer = Transfer::factory()->create(['admin_id' => $admin->id]);

        $this->actingAs($admin, 'admin')
            ->json('delete', "admin/accounting/transfers/{$transfer->id}")
            ->assertStatus(204);

        $this->assertDatabaseMissing('transfers', ['id' => $transfer->id]);

        $this->assertEquals(0, Transaction::count());
    }
}
