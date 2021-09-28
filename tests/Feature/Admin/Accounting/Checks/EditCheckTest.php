<?php

namespace Tests\Feature\Admin\Accounting\Checks;

use App\Models\Account;
use App\Models\Admin;
use App\Models\Category;
use App\Models\Check;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EditCheckTest extends TestCase
{
    use RefreshDatabase;

    private $oldCategory;
    private $newCategory;

    private function oldCheck($overrides = [])
    {
        return array_merge([
            'description' => 'old check',
            'category_id' => ($this->oldCategory = Category::factory()->income()->create())->id,
            'account_id'  => 1,
            'amount'      => "100.25",
            'due_date'    => '2020-01-01',
            'notes'       => 'old note',
        ], $overrides);
    }

    private function newCheck($overrides = [])
    {
        return array_merge([
            'description' => 'new check',
            'category_id' => ($this->newCategory = Category::factory()->expense()->create())->id,
            'account_id'  => 1,
            'amount'      => "200.25",
            'due_date'    => '2021-01-01',
            'notes'       => 'new note'
        ], $overrides);
    }

    /** @test */
    public function only_authenticated_users_can_update_their_checks()
    {
        $admin = Admin::factory()->create();

        $check = Check::factory()->create($this->oldCheck(['admin_id' => $admin->id]));

        $this->actingAs($admin, 'admin')
            ->json('patch', "admin/accounting/checks/{$check->id}", $this->newCheck())
            ->assertStatus(204);
    }

    /** @test */
    public function guests_cannot_update_check()
    {
        $check = Check::factory()->create($this->oldCheck());

        $this->json('patch', "admin/accounting/checks/{$check->id}", $this->newCheck())
            ->assertStatus(401);
    }

    /** @test */
    public function cannot_update_check_if_not_owned()
    {
        $admin = Admin::factory()->create();

        $check = Check::factory()->create($this->oldCheck(['admin_id' => Admin::factory()->create()]));

        $this->actingAs($admin, 'admin')
            ->json('patch', "admin/accounting/checks/{$check->id}", $this->newCheck())
            ->assertStatus(403);
    }

    /** @test */
    public function checks_can_only_be_updated_when_its_status_is_pending()
    {
        $admin = Admin::factory()->create();

        $checkCleared = Check::factory()->create($this->oldCheck(['admin_id' => $admin->id, 'status' => 'cleared']));
        $checkCancelled = Check::factory()->create($this->oldCheck([
            'admin_id' => $admin->id,
            'status'   => 'cancelled'
        ]));

        $this->actingAs($admin, 'admin')
            ->json('patch', "admin/accounting/checks/{$checkCleared->id}", $this->newCheck())
            ->assertStatus(403);

        $this->actingAs($admin, 'admin')
            ->json('patch', "admin/accounting/checks/{$checkCancelled->id}", $this->newCheck())
            ->assertStatus(403);
    }

    /** @test */
    public function updating_a_check()
    {
        $admin = Admin::factory()->create();
        $check = Check::factory()->create($this->oldCheck(['admin_id' => $admin->id]));
        $account = Account::factory()->create();

        $this->actingAs($admin, 'admin')
            ->json('patch', "admin/accounting/checks/{$check->id}",
                $this->newCheck([
                    'account_id' => $account->id,
                ])
            )
            ->assertStatus(204);

        tap(Check::first(), function ($check) use ($admin, $account) {
            $this->assertEquals('new check', $check->description);
            $this->assertEquals($this->newCategory->id, $check->category_id);
            $this->assertEquals($admin->id, $check->admin_id);
            $this->assertEquals($account->id, $check->account_id);
            $this->assertEquals(-20025, $check->amount);
            $this->assertEquals(Carbon::parse('2021-01-01'), $check->due_date);
            $this->assertEquals('new note', $check->notes);
        });
    }

    /** @test */
    public function description_is_required()
    {
        $admin = Admin::factory()->create();
        $check = Check::factory()->create($this->oldCheck(['admin_id' => $admin->id]));

        $response = $this->actingAs($admin, 'admin')
            ->json('patch', "admin/accounting/checks/{$check->id}", $this->newCheck([
                'description' => ''
            ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('description');
    }

    /** @test */
    public function category_is_required()
    {
        $admin = Admin::factory()->create();
        $check = Check::factory()->create($this->oldCheck(['admin_id' => $admin->id]));

        $response = $this->actingAs($admin, 'admin')
            ->json('patch', "admin/accounting/checks/{$check->id}", $this->newCheck([
                'category_id' => ''
            ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('category_id');
    }

    /** @test */
    public function must_be_a_valid_category()
    {
        $admin = Admin::factory()->create();
        $check = Check::factory()->create($this->oldCheck(['admin_id' => $admin->id]));

        $response = $this->actingAs($admin, 'admin')
            ->json('patch', "admin/accounting/checks/{$check->id}", $this->newCheck([
                'category_id' => 999
            ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('category_id');
    }

    /** @test */
    public function account_is_required()
    {
        $admin = Admin::factory()->create();
        $check = Check::factory()->create($this->oldCheck(['admin_id' => $admin->id]));

        $response = $this->actingAs($admin, 'admin')
            ->json('patch', "admin/accounting/checks/{$check->id}", $this->newCheck([
                'account_id' => ''
            ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('account_id');
    }

    /** @test */
    public function must_be_a_valid_account()
    {
        $admin = Admin::factory()->create();
        $check = Check::factory()->create($this->oldCheck(['admin_id' => $admin->id]));

        $response = $this->actingAs($admin, 'admin')
            ->json('patch', "admin/accounting/checks/{$check->id}", $this->newCheck([
                'account_id' => 999
            ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('account_id');
    }

    /** @test */
    public function cannot_be_a_disabled_account()
    {
        $admin = Admin::factory()->create();
        $account = Account::factory()->create(['balance' => 10000, 'is_active' => false]);
        $check = Check::factory()->create($this->oldCheck(['admin_id' => $admin->id]));

        $response = $this->actingAs($admin, 'admin')
            ->json('patch', "admin/accounting/checks/{$check->id}", $this->newCheck([
                'account_id' => $account->id,
                'amount'     => 25
            ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('account_id');
    }

    /** @test */
    public function amount_is_required()
    {
        $admin = Admin::factory()->create();
        $check = Check::factory()->create($this->oldCheck(['admin_id' => $admin->id]));

        $response = $this->actingAs($admin, 'admin')
            ->json('patch', "admin/accounting/checks/{$check->id}", $this->newCheck([
                'amount' => ''
            ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('amount');
    }

    /** @test */
    public function amount_must_be_a_valid_decimal()
    {
        $admin = Admin::factory()->create();
        $check = Check::factory()->create($this->oldCheck(['admin_id' => $admin->id]));

        $response = $this->actingAs($admin, 'admin')
            ->json('patch', "admin/accounting/checks/{$check->id}", $this->newCheck([
                'amount' => 'abc'
            ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('amount');
    }

    /** @test */
    public function amount_must_be_greater_than_zero()
    {
        $admin = Admin::factory()->create();
        $check = Check::factory()->create($this->oldCheck(['admin_id' => $admin->id]));

        $response = $this->actingAs($admin, 'admin')
            ->json('patch', "admin/accounting/checks/{$check->id}", $this->newCheck([
                'amount' => '-1'
            ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('amount');
    }

    /** @test */
    public function due_date_is_required()
    {
        $admin = Admin::factory()->create();
        $check = Check::factory()->create($this->oldCheck(['admin_id' => $admin->id]));

        $response = $this->actingAs($admin, 'admin')
            ->json('patch', "admin/accounting/checks/{$check->id}", $this->newCheck([
                'due_date' => ''
            ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('due_date');
    }

    /** @test */
    public function due_date_must_be_valid_date()
    {
        $admin = Admin::factory()->create();
        $check = Check::factory()->create($this->oldCheck(['admin_id' => $admin->id]));

        $response = $this->actingAs($admin, 'admin')
            ->json('patch', "admin/accounting/checks/{$check->id}", $this->newCheck([
                'due_date' => 'abc'
            ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('due_date');
    }

    /** @test */
    public function notes_are_optional()
    {
        $admin = Admin::factory()->create();
        $check = Check::factory()->create($this->oldCheck(['admin_id' => $admin->id]));

        $response = $this->actingAs($admin, 'admin')
            ->json('patch', "admin/accounting/checks/{$check->id}", $this->newCheck([
                'notes' => ''
            ]));

        $response->assertStatus(204);
    }
}
