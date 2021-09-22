<?php

namespace Tests\Feature\Admin\Accounting\Checks;

use App\Models\Account;
use App\Models\Admin;
use App\Models\Category;
use App\Models\Check;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClearCheckTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function only_authenticated_users_can_clear_checks()
    {
        $admin = Admin::factory()->create();

        $check = Check::factory()->create(['admin_id' => $admin->id]);

        $this->actingAs($admin, 'admin')
            ->json('patch', "admin/accounting/checks/process/{$check->id}")
            ->assertStatus(204);
    }

    /** @test */
    public function guests_cannot_clear_check()
    {
        $check = Check::factory()->create();

        $this->json('patch', "admin/accounting/checks/process/{$check->id}")
            ->assertStatus(401);
    }

    /** @test */
    public function cannot_clear_check_if_not_owned()
    {
        $admin = Admin::factory()->create();

        $check = Check::factory()->create(['admin_id' => Admin::factory()->create()]);

        $this->actingAs($admin, 'admin')
            ->json('patch', "admin/accounting/checks/process/{$check->id}")
            ->assertStatus(403);
    }

    /** @test */
    public function clearing_a_check()
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
            ->json('patch', "admin/accounting/checks/process/{$check->id}")
            ->assertStatus(204);

        tap(Check::first(), function ($check) {
            $this->assertEquals('cleared', $check->status);
        });

        $this->assertEquals(0, $account->fresh()->check_balance);
    }

    /** @test */
    public function clearing_a_check_creates_a_transaction_and_updates_account_balance()
    {
        $admin = Admin::factory()->create();
        $account = Account::factory()->create();
        $category = Category::factory()->income()->create();
        $check = Check::factory()->create([
            'description' => 'new check',
            'admin_id'    => $admin->id,
            'account_id'  => $account->id,
            'category_id' => $category->id,
            'amount'      => 10000,
            'notes'       => 'new check note'
        ]);

        $this->assertEquals(0, $account->fresh()->balance);
        $this->assertEquals(10000, $account->fresh()->check_balance);

        $this->actingAs($admin, 'admin')
            ->json('patch', "admin/accounting/checks/process/{$check->id}")
            ->assertStatus(204);

        $this->assertEquals(1, Transaction::count());

        tap(Transaction::first(), function ($transaction) use ($check) {
            $this->assertEquals($check->description, $transaction->description);
            $this->assertEquals($check->category_id, $transaction->category_id);
            $this->assertEquals($check->admin_id, $transaction->admin_id);
            $this->assertEquals($check->account_id, $transaction->account_id);
            $this->assertEquals($check->amount, $transaction->amount);
            $this->assertEquals($check->notes, $transaction->notes);

            $this->assertEquals($check->fresh()->transaction_id, $transaction->id);
        });

        $this->assertEquals(10000, $account->fresh()->balance);
        $this->assertEquals(0, $account->fresh()->check_balance);
    }
}
