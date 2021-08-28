<?php

namespace Tests\Feature\Admin\Accounting\Transactions;

use App\Models\Admin;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class editTransactionTest extends TestCase
{
    use RefreshDatabase;

    private function oldTransaction($overrides = [])
    {
        return array_merge([
            'description' => 'old transaction',
            'category_id' => 1,
            'amount'      => "100.25",
            'date'        => '2020-01-01',
            'notes'       => 'old note',
        ], $overrides);
    }

    private function newTransaction($overrides = [])
    {
        return array_merge([
            'description' => 'new transaction',
            'category_id' => 2,
            'amount'      => "200.25",
            'date'        => '2021-01-01',
            'notes'       => 'new note'
        ], $overrides);
    }

    /** @test */
    public function only_authenticated_users_can_update_their_transactions()
    {
        $admin = Admin::factory()->create();

        $transaction = Transaction::factory()->create($this->oldTransaction(['admin_id' => $admin->id]));

        $this->actingAs($admin, 'admin')
            ->json('patch', "admin/accounting/transactions/{$transaction->id}", $this->newTransaction())
            ->assertStatus(204);
    }

    /** @test */
    public function guests_cannot_update_transaction()
    {
        $transaction = Transaction::factory()->create($this->oldTransaction());

        $this->json('patch', "admin/accounting/transactions/{$transaction->id}", $this->newTransaction())
            ->assertStatus(401);
    }

    /** @test */
    public function cannot_update_transaction_if_not_owned()
    {
        $admin = Admin::factory()->create();

        $transaction = Transaction::factory()->create($this->oldTransaction(['admin_id' => Admin::factory()->create()]));

        $this->actingAs($admin, 'admin')
            ->json('patch', "admin/accounting/transactions/{$transaction->id}", $this->newTransaction())
            ->assertStatus(403);
    }

    /** @test */
    public function updating_a_transaction()
    {
        $admin = Admin::factory()->create();
        $transaction = Transaction::factory()->create($this->oldTransaction(['admin_id' => $admin->id]));

        $response = $this->actingAs($admin, 'admin')
            ->json('patch', "admin/accounting/transactions/{$transaction->id}", $this->newTransaction());

        tap(Transaction::first(), function ($transaction) use ($response, $admin) {
            $response->assertStatus(204);

            $this->assertEquals('new transaction', $transaction->description);
            $this->assertEquals(2, $transaction->category_id);
            $this->assertEquals($admin->id, $transaction->admin_id);
            $this->assertEquals(20025, $transaction->amount);
            $this->assertEquals(Carbon::parse('2021-01-01'), $transaction->date);
            $this->assertEquals('new note', $transaction->notes);
        });
    }

    /** @test */
    public function description_is_required()
    {
        $admin = Admin::factory()->create();
        $transaction = Transaction::factory()->create($this->oldTransaction(['admin_id' => $admin->id]));

        $response = $this->actingAs($admin, 'admin')
            ->json('patch', "admin/accounting/transactions/{$transaction->id}", $this->newTransaction([
                'description' => ''
            ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('description');
    }

    /** @test */
    public function category_is_required()
    {
        $admin = Admin::factory()->create();
        $transaction = Transaction::factory()->create($this->oldTransaction(['admin_id' => $admin->id]));

        $response = $this->actingAs($admin, 'admin')
            ->json('patch', "admin/accounting/transactions/{$transaction->id}", $this->newTransaction([
                'category_id' => ''
            ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('category_id');
    }

    /** @test */
    public function must_be_a_valid_category()
    {
        $admin = Admin::factory()->create();
        $transaction = Transaction::factory()->create($this->oldTransaction(['admin_id' => $admin->id]));

        $response = $this->actingAs($admin, 'admin')
            ->json('patch', "admin/accounting/transactions/{$transaction->id}", $this->newTransaction([
                'category_id' => 999
            ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('category_id');
    }

    /** @test */
    public function amount_is_required()
    {
        $admin = Admin::factory()->create();
        $transaction = Transaction::factory()->create($this->oldTransaction(['admin_id' => $admin->id]));

        $response = $this->actingAs($admin, 'admin')
            ->json('patch', "admin/accounting/transactions/{$transaction->id}", $this->newTransaction([
                'amount' => ''
            ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('amount');
    }

    /** @test */
    public function amount_must_be_a_valid_decimal()
    {
        $admin = Admin::factory()->create();
        $transaction = Transaction::factory()->create($this->oldTransaction(['admin_id' => $admin->id]));

        $response = $this->actingAs($admin, 'admin')
            ->json('patch', "admin/accounting/transactions/{$transaction->id}", $this->newTransaction([
                'amount' => 'abc'
            ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('amount');
    }

    /** @test */
    public function amount_must_be_greater_than_zero()
    {
        $admin = Admin::factory()->create();
        $transaction = Transaction::factory()->create($this->oldTransaction(['admin_id' => $admin->id]));

        $response = $this->actingAs($admin, 'admin')
            ->json('patch', "admin/accounting/transactions/{$transaction->id}", $this->newTransaction([
                'amount' => '-1'
            ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('amount');
    }

    /** @test */
    public function date_is_required()
    {
        $admin = Admin::factory()->create();
        $transaction = Transaction::factory()->create($this->oldTransaction(['admin_id' => $admin->id]));

        $response = $this->actingAs($admin, 'admin')
            ->json('patch', "admin/accounting/transactions/{$transaction->id}", $this->newTransaction([
                'date' => ''
            ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('date');
    }

    /** @test */
    public function date_must_be_valid_date()
    {
        $admin = Admin::factory()->create();
        $transaction = Transaction::factory()->create($this->oldTransaction(['admin_id' => $admin->id]));

        $response = $this->actingAs($admin, 'admin')
            ->json('patch', "admin/accounting/transactions/{$transaction->id}", $this->newTransaction([
                'date' => 'abc'
            ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('date');
    }

    /** @test */
    public function notes_are_optional()
    {
        $admin = Admin::factory()->create();
        $transaction = Transaction::factory()->create($this->oldTransaction(['admin_id' => $admin->id]));

        $response = $this->actingAs($admin, 'admin')
            ->json('patch', "admin/accounting/transactions/{$transaction->id}", $this->newTransaction([
                'notes' => ''
            ]));

        $response->assertStatus(204);
    }
}
