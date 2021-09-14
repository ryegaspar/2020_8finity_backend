<?php

namespace Tests\Feature\Admin\Accounting\Transactions;

use App\Models\Account;
use App\Models\Admin;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\Transfer;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AddTransactionTest extends TestCase
{
    use RefreshDatabase;

    private function validParams($overrides = [])
    {
        return array_merge([
            'description' => 'new transaction',
            'category_id' => 1,
            'account_id'  => 1,
            'amount'      => "100.25",
            'date'        => '2021-01-01',
            'notes'       => 'note'
        ], $overrides);
    }

    /** @test */
    public function only_authenticated_users_can_add_transactions()
    {
        $admin = Admin::factory()->create();

        $this->actingAs($admin, 'admin')
            ->json('post', 'admin/accounting/transactions', $this->validParams())
            ->assertStatus(201);
    }

    /** @test */
    public function guests_cannot_add_transactions()
    {
        $this->json('post', 'admin/accounting/transactions', $this->validParams())
            ->assertStatus(401);
    }

    /** @test */
    public function adding_a_transaction()
    {
        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->json('post', 'admin/accounting/transactions', $this->validParams())
            ->assertStatus(201);

        tap(Transaction::first(), function ($transaction) use ($admin) {
            $this->assertEquals('new transaction', $transaction->description);
            $this->assertEquals(1, $transaction->category_id);
            $this->assertEquals($admin->id, $transaction->admin_id);
            $this->assertEquals(1, $transaction->account_id);
            $this->assertEquals(10025, $transaction->amount);
            $this->assertEquals(Carbon::parse('2021-01-01'), $transaction->date);
            $this->assertEquals('note', $transaction->notes);
        });
    }

    /** @test */
    public function description_is_required()
    {
        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->json('post', 'admin/accounting/transactions', $this->validParams([
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
            ->json('post', 'admin/accounting/transactions', $this->validParams([
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
            ->json('post', 'admin/accounting/transactions', $this->validParams([
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
            ->json('post', 'admin/accounting/transactions', $this->validParams([
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
            ->json('post', 'admin/accounting/transactions', $this->validParams([
                'account_id' => 999
            ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('account_id');
    }

    /** @test */
    public function cannot_add_transaction_with_a_disabled_account()
    {
        $admin = Admin::factory()->create();
        $account = Account::factory()->create(['balance' => 10000, 'is_active' => false]);

        $response = $this->actingAs($admin, 'admin')
            ->json('post', 'admin/accounting/transactions', $this->validParams([
                'account_id' => $account->id,
                'amount'       => 25
            ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('account_id');
    }

    /** @test */
    public function amount_is_required()
    {
        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->json('post', 'admin/accounting/transactions', $this->validParams([
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
            ->json('post', 'admin/accounting/transactions', $this->validParams([
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
            ->json('post', 'admin/accounting/transactions', $this->validParams([
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
            ->json('post', 'admin/accounting/transactions', $this->validParams([
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
            ->json('post', 'admin/accounting/transactions', $this->validParams([
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
            ->json('post', 'admin/accounting/transactions', $this->validParams([
                'notes' => ''
            ]));

        $response->assertStatus(201);
    }

    /** @test */
    public function adding_a_transaction_with_an_income_category_has_positive_amount()
    {
        $admin = Admin::factory()->create();

        $this->actingAs($admin, 'admin')
            ->json('post', 'admin/accounting/transactions',
                $this->validParams([
                    'category_id' => Category::factory()->income()->create()->id,
                    'amount'      => 100
                ])
            )
            ->assertStatus(201);

        tap(Transaction::first(), function ($transaction) use ($admin) {
            $this->assertGreaterThan(0, $transaction->amount);
            $this->assertEquals(10000, $transaction->amount);
        });
    }

    /** @test */
    public function adding_a_transaction_with_an_expense_category_has_negative_amount()
    {
        $admin = Admin::factory()->create();

        $this->actingAs($admin, 'admin')
            ->json('post', 'admin/accounting/transactions',
                $this->validParams([
                    'category_id' => Category::factory()->expense()->create()->id,
                    'amount'      => 100
                ])
            )
        ->assertStatus(201);

        tap(Transaction::first(), function ($transaction) use ($admin) {
            $this->assertLessThan(0, $transaction->amount);
            $this->assertEquals(-10000, $transaction->amount);
        });
    }

    /** @test */
    public function adding_a_transaction_with_income_category_adds_to_its_account_balance()
    {
        $admin = Admin::factory()->create();
        $account = Account::factory()->create();
        $category = Category::factory()->income()->create();

        $this->actingAs($admin, 'admin')
            ->json('post', 'admin/accounting/transactions',
                $this->validParams([
                    'account_id'  => $account->id,
                    'category_id' => $category->id,
                    'amount'      => 100
                ])
            );

        $this->assertEquals(10000, $account->fresh()->balance);

        $this->actingAs($admin, 'admin')
            ->json('post', 'admin/accounting/transactions',
                $this->validParams([
                    'account_id'  => $account->id,
                    'category_id' => $category->id,
                    'amount'      => 50
                ])
            );

        $this->assertEquals(15000, $account->fresh()->balance);
    }

    /** @test */
    public function adding_a_transaction_with_expense_category_lessens_to_its_account_balance()
    {
        $admin = Admin::factory()->create();
        $account = Account::factory()->create();
        $category = Category::factory()->expense()->create();

        $this->actingAs($admin, 'admin')
            ->json('post', 'admin/accounting/transactions',
                $this->validParams([
                    'account_id'  => $account->id,
                    'category_id' => $category->id,
                    'amount'      => 100
                ])
            );

        $this->assertEquals(-10000, $account->fresh()->balance);
    }

    /** @test */
    public function adding_a_transaction_to_an_account_that_has_transfers_have_correct_account_balance()
    {
        $admin = Admin::factory()->create();
        $account1 = Account::factory()->create();
        $account2 = Account::factory()->create();
        $category = Category::factory()->income()->create();

        Transaction::factory()->create([
            'account_id'  => $account1->id,
            'category_id' => $category->id,
            'amount'      => 10000
        ]);

        $this->assertEquals(10000, $account1->fresh()->balance);

        Transfer::factory()->create([
            'from_account' => $account1->id,
            'to_account'   => $account2->id,
            'admin_id'     => $admin->id,
            'amount'       => 1000,
        ]);

        $this->assertEquals(9000, $account1->fresh()->balance);
        $this->assertEquals(1000, $account2->fresh()->balance);

        $this->actingAs($admin, 'admin')
            ->json('post', 'admin/accounting/transactions',
                $this->validParams([
                    'account_id'  => $account1->id,
                    'category_id' => $category->id,
                    'amount'      => 10
                ])
            );

        $this->assertEquals(10000, $account1->fresh()->balance);
    }
}
