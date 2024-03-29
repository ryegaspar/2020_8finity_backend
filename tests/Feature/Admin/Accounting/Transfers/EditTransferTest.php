<?php

namespace Tests\Feature\Admin\Accounting\Transactions;

use App\Models\Account;
use App\Models\Admin;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\Transfer;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EditTransferTest extends TestCase
{
    use RefreshDatabase;

    private $admin;
    private $account1;
    private $account2;
    private $account3;
    private $oldTransfer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::factory()->create();
        $this->account1 = Account::factory()->create();
        $this->account2 = Account::factory()->create();
        $this->account3 = Account::factory()->create();

        Transaction::factory()->create([
            'account_id'  => $this->account1->id,
            'admin_id'    => $this->admin->id,
            'category_id' => Category::factory()->income(),
            'amount'      => 10000
        ]);

        $this->oldTransfer = Transfer::factory()->create([
            'from_account' => $this->account1->id,
            'to_account'   => $this->account2->id,
            'admin_id'     => $this->admin->id,
            'amount'       => 2000
        ]);
    }

    private function newTransfer($overrides = [])
    {
        return array_merge([
            'description'  => 'new transfer',
            'account_id'   => $this->admin->id,
            'from_account' => $this->account1->id,
            'to_account'   => $this->account3->id,
            'amount'       => 25,
            'date'         => '2021-01-01',
            'notes'        => 'new note'
        ], $overrides);
    }

    /** @test */
    public function only_authenticated_users_can_update_their_transfers()
    {
        $this->actingAs($this->admin, 'admin')
            ->json('patch', "admin/accounting/transfers/{$this->oldTransfer->id}", $this->newTransfer())
            ->assertStatus(204);
    }

    /** @test */
    public function guests_cannot_update_transfer()
    {
        $this->json('patch', "admin/accounting/transfers/{$this->oldTransfer->id}", $this->newTransfer())
            ->assertStatus(401);
    }

    /** @test */
    public function cannot_update_transfer_if_not_owned()
    {
        $admin = Admin::factory()->create();

        $this->actingAs($admin, 'admin')
            ->json('patch', "admin/accounting/transfers/{$this->oldTransfer->id}", $this->newTransfer())
            ->assertStatus(403);
    }

    /** @test */
    public function updating_a_transfer()
    {
        $this->actingAs($this->admin, 'admin')
            ->json('patch', "admin/accounting/transfers/{$this->oldTransfer->id}",
                $this->newTransfer())
            ->assertStatus(204);

        tap(Transfer::first(), function ($transfer) {
            $this->assertEquals('new transfer', $transfer->description);
            $this->assertEquals($this->admin->id, $transfer->admin_id);
            $this->assertEquals($this->account1->id, $transfer->from_account);
            $this->assertEquals($this->account3->id, $transfer->to_account);
            $this->assertEquals(2500, $transfer->amount);
            $this->assertEquals(Carbon::parse('2021-01-01'), $transfer->date);
            $this->assertEquals('new note', $transfer->notes);
        });
    }

    /** @test */
    public function description_is_required()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->json('patch', "admin/accounting/transfers/{$this->oldTransfer->id}", $this->newTransfer([
                'description' => ''
            ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('description');
    }

    /** @test */
    public function from_account_is_required()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->json('patch', "admin/accounting/transfers/{$this->oldTransfer->id}", $this->newTransfer([
                'from_account' => ''
            ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('from_account');
    }

    /** @test */
    public function from_account_must_be_a_valid_account()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->json('patch', "admin/accounting/transfers/{$this->oldTransfer->id}", $this->newTransfer([
                'from_account' => 999
            ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('from_account');
    }

    /** @test */
    public function to_account_is_required()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->json('patch', "admin/accounting/transfers/{$this->oldTransfer->id}", $this->newTransfer([
                'to_account' => ''
            ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('to_account');
    }

    /** @test */
    public function to_account_must_be_a_valid_account()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->json('patch', "admin/accounting/transfers/{$this->oldTransfer->id}", $this->newTransfer([
                'to_account' => 999
            ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('to_account');
    }

    /** @test */
    public function transfers_cannot_be_on_the_same_account()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->json('patch', "admin/accounting/transfers/{$this->oldTransfer->id}", $this->newTransfer([
                'from_account' => $this->account1->id,
                'to_account'   => $this->account1->id,
                'amount'       => 25
            ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('to_account');
    }

    /** @test */
    public function cannot_transfer_from_a_disabled_account()
    {
        $account = Account::factory()->create(['is_active' => false]);

        $response = $this->actingAs($this->admin, 'admin')
            ->json('patch', "admin/accounting/transfers/{$this->oldTransfer->id}", $this->newTransfer([
                'from_account' => $account->id,
                'amount'       => 25
            ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('from_account');
    }

    /** @test */
    public function cannot_be_a_disabled_account()
    {
        $account = Account::factory()->create(['is_active' => false]);

        $response = $this->actingAs($this->admin, 'admin')
            ->json('patch', "admin/accounting/transfers/{$this->oldTransfer->id}", $this->newTransfer([
                'to_account' => $account->id,
                'amount'     => 25
            ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('to_account');
    }

    /** @test */
    public function amount_is_required()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->json('patch', "admin/accounting/transfers/{$this->oldTransfer->id}", $this->newTransfer([
                'amount' => ''
            ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('amount');
    }

    /** @test */
    public function amount_must_be_a_valid_decimal()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->json('patch', "admin/accounting/transfers/{$this->oldTransfer->id}", $this->newTransfer([
                'amount' => 'abc'
            ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('amount');
    }

    /** @test */
    public function amount_must_be_greater_than_zero()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->json('patch', "admin/accounting/transfers/{$this->oldTransfer->id}", $this->newTransfer([
                'amount' => '-1'
            ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('amount');
    }

    /** @test */
    public function date_is_required()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->json('patch', "admin/accounting/transfers/{$this->oldTransfer->id}", $this->newTransfer([
                'date' => ''
            ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('date');
    }

    /** @test */
    public function date_must_be_valid_date()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->json('patch', "admin/accounting/transfers/{$this->oldTransfer->id}", $this->newTransfer([
                'date' => 'abc'
            ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('date');
    }

    /** @test */
    public function notes_are_optional()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->json('patch', "admin/accounting/transfers/{$this->oldTransfer->id}", $this->newTransfer([
                'notes' => ''
            ]));

        $response->assertStatus(204);
    }
}
