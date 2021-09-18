<?php

namespace Tests\Feature\Admin\Accounting\Checks;

use App\Models\Account;
use App\Models\Admin;
use App\Models\Category;
use App\Models\Check;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteCheckTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function only_authenticated_users_can_delete_their_checks()
    {
        $admin = Admin::factory()->create();

        $check = Check::factory()->create(['admin_id' => $admin->id]);

        $this->actingAs($admin, 'admin')
            ->json('delete', "admin/accounting/checks/{$check->id}")
            ->assertStatus(204);
    }

    /** @test */
    public function guests_cannot_delete_checks()
    {
        $check = Check::factory()->create();

        $this->json('delete', "admin/accounting/checks/{$check->id}")
            ->assertStatus(401);
    }

    /** @test */
    public function cannot_delete_check_if_not_owned()
    {
        $admin = Admin::factory()->create();

        $check = Check::factory()->create();

        $this->actingAs($admin, 'admin')
            ->json('delete', "admin/accounting/checks/{$check->id}")
            ->assertStatus(403);
    }

    /** @test */
    public function checks_can_only_be_deleted_when_its_status_is_pending()
    {
        $admin = Admin::factory()->create();

        $checkCleared = Check::factory()->create(['admin_id' => $admin->id, 'status' => 'cleared']);
        $checkCancelled = Check::factory()->create(['admin_id' => $admin->id, 'status' => 'cancelled']);

        $this->actingAs($admin, 'admin')
            ->json('delete', "admin/accounting/checks/{$checkCleared->id}")
            ->assertStatus(403);

        $this->actingAs($admin, 'admin')
            ->json('delete', "admin/accounting/checks/{$checkCancelled->id}")
            ->assertStatus(403);
    }

    /** @test */
    public function deleting_a_check()
    {
        $admin = Admin::factory()->create();

        $check = Check::factory()->create(['admin_id' => $admin->id]);

        $this->actingAs($admin, 'admin')
            ->json('delete', "admin/accounting/checks/{$check->id}")
            ->assertStatus(204);

        $this->assertDatabaseMissing('checks', ['id' => $check->id]);

        $this->assertEquals(0, Check::count());
    }

    /** @test */
    public function deleting_a_check_updates_account_balance()
    {
        $admin = Admin::factory()->create();

        $account = Account::factory()->create();
        $category = Category::factory()->income()->create();

        $check = Check::factory()->create([
            'admin_id'    => $admin->id,
            'account_id'  => $account->id,
            'category_id' => $category->id,
            'amount'      => 10000
        ]);

        $this->assertEquals(10000, $account->fresh()->check_balance);

        $this->actingAs($admin, 'admin')
            ->json('delete', "admin/accounting/checks/{$check->id}");

        $this->assertEquals(0, $account->fresh()->check_balance);
    }
}
