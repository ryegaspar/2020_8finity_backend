<?php

namespace Tests\Feature\Admin\Accounting\Checks;

use App\Models\Account;
use App\Models\Admin;
use App\Models\Check;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CancelCheckTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function only_authenticated_users_can_cancel_checks()
    {
        $admin = Admin::factory()->create();

        $check = Check::factory()->create(['admin_id' => $admin->id]);

        $this->actingAs($admin, 'admin')
            ->json('delete', "admin/accounting/checks/process/{$check->id}")
            ->assertStatus(204);
    }

    /** @test */
    public function guests_cannot_cancel_check()
    {
        $check = Check::factory()->create();

        $this->json('delete', "admin/accounting/checks/process/{$check->id}")
            ->assertStatus(401);
    }

    /** @test */
    public function cannot_cancel_check_if_not_owned()
    {
        $admin = Admin::factory()->create();

        $check = Check::factory()->create(['admin_id' => Admin::factory()->create()]);

        $this->actingAs($admin, 'admin')
            ->json('delete', "admin/accounting/checks/process/{$check->id}")
            ->assertStatus(403);
    }

    /** @test */
    public function cancelling_a_check()
    {
        $admin = Admin::factory()->create();
        $account = Account::factory()->create();
        $check = Check::factory()->create([
            'admin_id'   => $admin->id,
            'account_id' => $account->id,
            'amount'     => 10000
        ]);

        $this->assertEquals(10000, $account->fresh()->check_balance);

        $this->actingAs($admin, 'admin')
            ->json('delete', "admin/accounting/checks/process/{$check->id}")
            ->assertStatus(204);

        tap(Check::first(), function ($check) {
            $this->assertEquals('cancelled', $check->status);
        });

        $this->assertEquals(0, $account->fresh()->check_balance);
    }
}
