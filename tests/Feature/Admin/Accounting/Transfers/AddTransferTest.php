<?php

namespace Tests\Feature\Admin\Accounting\Transactions;

use App\Models\Account;
use App\Models\Admin;
use App\Models\Transfer;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AddTransferTest extends TestCase
{
    use RefreshDatabase;

    private function validParams($overrides = [])
    {
        return array_merge([
            'description'  => 'new transfer',
            'from_account' => Account::factory()->create(['balance' => 10000])->id,
            'to_account'   => Account::factory()->create()->id,
            'amount'       => "75",
            'date'         => '2021-01-01',
            'notes'        => 'note'
        ], $overrides);
    }

    /** @test */
    public function only_authenticated_users_can_add_transfer()
    {
        $this->withoutExceptionHandling();
        $admin = Admin::factory()->create();

        $this->actingAs($admin, 'admin')
            ->json('post', 'admin/accounting/transfers', $this->validParams())
            ->assertStatus(201);
    }

    /** @test */
    public function guests_cannot_add_transfer()
    {
        $this->json('post', 'admin/accounting/transfers', $this->validParams())
            ->assertStatus(401);
    }

    /** @test */
    public function adding_a_transfer()
    {
        $admin = Admin::factory()->create();
        $account1 = Account::factory()->create(['balance' => 10000]);
        $account2 = Account::factory()->create();

        $this->actingAs($admin, 'admin')
            ->json('post', 'admin/accounting/transfers', $this->validParams([
                'from_account' => $account1->id,
                'to_account'   => $account2->id,
                'amount'       => 75
            ]))
            ->assertStatus(201);

        tap(Transfer::first(), function ($transfer) use ($admin, $account1, $account2) {

            $this->assertEquals('new transfer', $transfer->description);
            $this->assertEquals($account1->id, $transfer->from_account);
            $this->assertEquals($account2->id, $transfer->to_account);
            $this->assertEquals($admin->id, $transfer->admin_id);
            $this->assertEquals(7500, $transfer->amount);
            $this->assertEquals(Carbon::parse('2021-01-01'), $transfer->date);
            $this->assertEquals('note', $transfer->notes);
        });
    }

    /** @test */
    public function description_is_required()
    {
        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->json('post', 'admin/accounting/transfers', $this->validParams([
                'description' => ''
            ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('description');
    }

    /** @test */
    public function from_account_is_required()
    {
        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->json('post', 'admin/accounting/transfers', $this->validParams([
                'from_account' => ''
            ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('from_account');
    }

    /** @test */
    public function from_account_must_be_a_valid_account()
    {
        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->json('post', 'admin/accounting/transfers', $this->validParams([
                'from_account' => 999
            ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('from_account');
    }

    /** @test */
    public function to_account_is_required()
    {
        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->json('post', 'admin/accounting/transfers', $this->validParams([
                'to_account' => ''
            ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('to_account');
    }

    /** @test */
    public function to_account_must_be_a_valid_account()
    {
        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->json('post', 'admin/accounting/transfers', $this->validParams([
                'to_account' => 999
            ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('to_account');
    }

    /** @test */
    public function transfers_cannot_be_on_the_same_account()
    {
        $admin = Admin::factory()->create();
        $account = Account::factory()->create(['balance' => 10000]);

        $response = $this->actingAs($admin, 'admin')
            ->json('post', 'admin/accounting/transfers', $this->validParams([
                'from_account' => $account->id,
                'to_account'   => $account->id,
                'amount'       => 25
            ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('to_account');
    }

    /** @test */
    public function cannot_transfer_from_a_disabled_account()
    {
        $admin = Admin::factory()->create();
        $account = Account::factory()->create(['balance' => 10000, 'is_active' => false]);

        $response = $this->actingAs($admin, 'admin')
            ->json('post', 'admin/accounting/transfers', $this->validParams([
                'from_account' => $account->id,
                'amount'       => 25
            ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('from_account');
    }

    /** @test */
    public function cannot_transfer_to_a_disabled_account()
    {
        $admin = Admin::factory()->create();
        $account1 = Account::factory()->create(['balance' => 10000]);
        $account2 = Account::factory()->create(['balance' => 10000, 'is_active' => false]);

        $response = $this->actingAs($admin, 'admin')
            ->json('post', 'admin/accounting/transfers', $this->validParams([
                'from_account' => $account1->id,
                'to_account'   => $account2->id,
                'amount'       => 25
            ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('to_account');
    }

    /** @test */
    public function amount_is_required()
    {
        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->json('post', 'admin/accounting/transfers', $this->validParams([
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
            ->json('post', 'admin/accounting/transfers', $this->validParams([
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
            ->json('post', 'admin/accounting/transfers', $this->validParams([
                'amount' => '-1'
            ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('amount');
    }

    /** @test */
    public function date_is_required()
    {
        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->json('post', 'admin/accounting/transfers', $this->validParams([
                'date' => ''
            ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('date');
    }

    /** @test */
    public function date_must_be_valid_date()
    {
        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->json('post', 'admin/accounting/transfers', $this->validParams([
                'date' => 'abc'
            ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('date');
    }

    /** @test */
    public function notes_are_optional()
    {
        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->json('post', 'admin/accounting/transfers', $this->validParams([
                'notes' => ''
            ]));

        $response->assertStatus(201);
    }

    /** @test */
    public function account_from_must_have_sufficient_balance_to_transfer_to()
    {
        $admin = Admin::factory()->create();
        $account1 = Account::factory()->create(['balance' => 10000]);

        $response = $this->actingAs($admin, 'admin')
            ->json('post', 'admin/accounting/transfers', $this->validParams([
                'from_account' => $account1->id,
                'amount'       => 200
            ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('amount');
    }
}
