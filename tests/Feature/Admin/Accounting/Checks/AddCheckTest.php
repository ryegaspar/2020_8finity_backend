<?php

namespace Tests\Feature\Admin\Accounting\Checks;

use App\Models\Account;
use App\Models\Admin;
use App\Models\Check;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AddCheckTest extends TestCase
{
    use RefreshDatabase;

    private function validParams($overrides = [])
    {
        return array_merge([
            'category_id' => 1,
            'account_id'  => 1,
            'amount'      => 100.25,
            'description' => 'new check',
            'notes'       => 'note',
            'due_date'    => '2021-01-01'
        ], $overrides);
    }

    /** @test */
    public function only_authenticated_users_can_add_checks()
    {
        $admin = Admin::factory()->create();

        $this->actingAs($admin, 'admin')
            ->json('post', 'admin/accounting/checks', $this->validParams())
            ->assertStatus(201);
    }

    /** @test */
    public function guests_cannot_add_checks()
    {
        $this->json('post', 'admin/accounting/checks', $this->validParams())
            ->assertStatus(401);
    }

    /** @test */
    public function adding_a_check()
    {
        $admin = Admin::factory()->create();

        $this->actingAs($admin, 'admin')
            ->json('post', 'admin/accounting/checks', $this->validParams())
            ->assertStatus(201);

        tap(Check::first(), function ($check) use ($admin) {
            $this->assertEquals('new check', $check->description);
            $this->assertEquals(1, $check->category_id);
            $this->assertEquals($admin->id, $check->admin_id);
            $this->assertEquals(1, $check->account_id);
            $this->assertEquals(10025, $check->amount);
            $this->assertEquals(Carbon::parse('2021-01-01'), $check->due_date);
            $this->assertEquals('note', $check->notes);
        });
    }

    /** @test */
    public function description_is_required()
    {
        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->json('post', 'admin/accounting/checks', $this->validParams([
                'description' => ''
            ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('description');
    }

    /** @test */
    public function category_is_required()
    {
        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->json('post', 'admin/accounting/checks', $this->validParams([
                'category_id' => ''
            ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('category_id');
    }

    /** @test */
    public function must_be_a_valid_category()
    {
        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->json('post', 'admin/accounting/checks', $this->validParams([
                'category_id' => 999
            ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('category_id');
    }

    /** @test */
    public function account_is_required()
    {
        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->json('post', 'admin/accounting/checks', $this->validParams([
                'account_id' => ''
            ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('account_id');
    }

    /** @test */
    public function must_be_a_valid_account()
    {
        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->json('post', 'admin/accounting/checks', $this->validParams([
                'account_id' => 999
            ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('account_id');
    }

    /** @test */
    public function cannot_add_check_with_a_disabled_account()
    {
        $admin = Admin::factory()->create();
        $account = Account::factory()->create(['balance' => 10000, 'is_active' => false]);

        $response = $this->actingAs($admin, 'admin')
            ->json('post', 'admin/accounting/checks', $this->validParams([
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

        $response = $this->actingAs($admin, 'admin')
            ->json('post', 'admin/accounting/checks', $this->validParams([
                'amount' => ''
            ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('amount');
    }

    /** @test */
    public function amount_must_be_a_valid_decimal()
    {
        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->json('post', 'admin/accounting/checks', $this->validParams([
                'amount' => 'abc'
            ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('amount');
    }

    /** @test */
    public function amount_must_be_greater_than_zero()
    {
        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->json('post', 'admin/accounting/checks', $this->validParams([
                'amount' => '-1'
            ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('amount');
    }

    /** @test */
    public function due_date_is_required()
    {
        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->json('post', 'admin/accounting/checks', $this->validParams([
                'due_date' => ''
            ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('due_date');
    }

    /** @test */
    public function due_date_must_be_valid_date()
    {
        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->json('post', 'admin/accounting/checks', $this->validParams([
                'due_date' => 'abc'
            ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('due_date');
    }

    /** @test */
    public function notes_are_optional()
    {
        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->json('post', 'admin/accounting/checks', $this->validParams([
                'notes' => ''
            ]));

        $response->assertStatus(201);
    }
}
