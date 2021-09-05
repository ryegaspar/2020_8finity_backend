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

class EditTransactionTest extends TestCase
{
    use RefreshDatabase;

    private $oldCategory;
    private $newCategory;

    private function oldTransaction($overrides = [])
    {
        return array_merge([
            'description' => 'old transaction',
            'category_id' => ($this->oldCategory = Category::factory()->income()->create())->id,
            'account_id'  => 1,
            'amount'      => "100.25",
            'date'        => '2020-01-01',
            'notes'       => 'old note',
        ], $overrides);
    }

    private function newTransaction($overrides = [])
    {
        return array_merge([
            'description' => 'new transaction',
            'category_id' => ($this->newCategory = Category::factory()->expense()->create())->id,
            'account_id'  => 1,
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
        $account = Account::factory()->create();

        $this->actingAs($admin, 'admin')
            ->json('patch', "admin/accounting/transactions/{$transaction->id}",
                $this->newTransaction([
                    'account_id' => $account->id,
                ])
            )
            ->assertStatus(204);

        tap(Transaction::first(), function ($transaction) use ($admin, $account) {
            $this->assertEquals('new transaction', $transaction->description);
            $this->assertEquals($this->newCategory->id, $transaction->category_id);
            $this->assertEquals($admin->id, $transaction->admin_id);
            $this->assertEquals($account->id, $transaction->account_id);
            $this->assertEquals(-20025, $transaction->amount);
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
    public function account_is_required()
    {
        $admin = Admin::factory()->create();
        $transaction = Transaction::factory()->create($this->oldTransaction(['admin_id' => $admin->id]));

        $response = $this->actingAs($admin, 'admin')
            ->json('patch', "admin/accounting/transactions/{$transaction->id}", $this->newTransaction([
                'account_id' => ''
            ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('account_id');
    }

    /** @test */
    public function must_be_a_valid_account()
    {
        $admin = Admin::factory()->create();
        $transaction = Transaction::factory()->create($this->oldTransaction(['admin_id' => $admin->id]));

        $response = $this->actingAs($admin, 'admin')
            ->json('patch', "admin/accounting/transactions/{$transaction->id}", $this->newTransaction([
                'account_id' => 999
            ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('account_id');
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

    /** @test */
    public function updating_a_transaction_with_an_income_category_has_positive_amount()
    {
        $admin = Admin::factory()->create();
        $transaction = Transaction::factory()->create($this->oldTransaction(['admin_id' => $admin->id]));

        $this->actingAs($admin, 'admin')
            ->json('patch', "admin/accounting/transactions/{$transaction->id}",
                $this->newTransaction([
                    'category_id' => Category::factory()->income()->create(['name' => 'income'])->id,
                    'amount'      => 100
                ])
            )
            ->assertStatus(204);

        tap(Transaction::first(), function ($transaction) use ($admin) {
            $this->assertGreaterThan(0, $transaction->amount);
            $this->assertEquals(10000, $transaction->amount);
        });
    }

    /** @test */
    public function updating_a_transaction_with_an_expense_category_has_negative_amount()
    {
        $admin = Admin::factory()->create();
        $transaction = Transaction::factory()->create($this->oldTransaction(['admin_id' => $admin->id]));

        $this->actingAs($admin, 'admin')
            ->json('patch', "admin/accounting/transactions/{$transaction->id}",
                $this->newTransaction([
                    'category_id'   => Category::factory()->expense()->create(['name' => 'expense'])->id,
                    'category_type' => 'expense',
                    'amount'        => 100
                ])
            )
            ->assertStatus(204);

        tap(Transaction::first(), function ($transaction) use ($admin) {
            $this->assertLessThan(0, $transaction->amount);
            $this->assertEquals(-10000, $transaction->amount);
        });
    }

    /** @test */
    public function updating_a_transaction_amount_with_the_same_income_category_updates_its_account_balance()
    {
        $admin = Admin::factory()->create();
        $account = Account::factory()->create();
        $category = Category::factory()->income()->create();
        $transaction = Transaction::factory()->create([
            'admin_id'    => $admin->id,
            'account_id'  => $account->id,
            'category_id' => $category->id,
            'amount'      => 10000
        ]);

        $this->assertEquals(10000, $account->fresh()->balance);

        $response = $this->actingAs($admin, 'admin')
            ->json('patch', "admin/accounting/transactions/{$transaction->id}",
                $this->newTransaction([
                    'admin_id'    => $admin->id,
                    'account_id'  => $account->id,
                    'category_id' => $category->id,
                    'amount'      => 50
                ])
            );

        $this->assertEquals(5000, $account->fresh()->balance);
    }

    /** @test */
    public function updating_a_transaction_amount_with_the_same_expense_category_updates_its_account_balance()
    {
        $admin = Admin::factory()->create();
        $account = Account::factory()->create();
        $category = Category::factory()->expense()->create();
        $transaction = Transaction::factory()->create([
            'admin_id'    => $admin->id,
            'account_id'  => $account->id,
            'category_id' => $category->id,
            'amount'      => 10000
        ]);

        $this->assertEquals(-10000, $account->fresh()->balance);

        $this->actingAs($admin, 'admin')
            ->json('patch', "admin/accounting/transactions/{$transaction->id}",
                $this->newTransaction([
                    'admin_id'    => $admin->id,
                    'account_id'  => $account->id,
                    'category_id' => $category->id,
                    'amount'      => 50
                ])
            );

        $this->assertEquals(-5000, $account->fresh()->balance);
    }

    /** @test */
    public function updating_a_transaction_amount_with_different_category_types_updates_its_account_balance()
    {
        $admin = Admin::factory()->create();
        $account = Account::factory()->create();
        $category_expense = Category::factory()->expense()->create();
        $category_income = Category::factory()->income()->create();
        $transaction = Transaction::factory()->create([
            'admin_id'    => $admin->id,
            'account_id'  => $account->id,
            'category_id' => $category_income->id,
            'amount'      => 10000
        ]);

        $this->assertEquals(10000, $account->fresh()->balance);

        $this->actingAs($admin, 'admin')
            ->json('patch', "admin/accounting/transactions/{$transaction->id}",
                $this->newTransaction([
                    'admin_id'    => $admin->id,
                    'account_id'  => $account->id,
                    'category_id' => $category_expense->id,
                    'amount'      => 25
                ])
            );

        $this->assertEquals(-2500, $account->fresh()->balance);
    }

    /** @test */
    public function updating_a_transaction_with_different_account_updates_both_accounts()
    {
        $admin = Admin::factory()->create();
        $account1 = Account::factory()->create();
        $account2 = Account::factory()->create();
        $category = Category::factory()->income()->create();
        $transaction = Transaction::factory()->create([
            'admin_id'    => $admin->id,
            'account_id'  => $account1->id,
            'category_id' => $category->id,
            'amount'      => 10000
        ]);

        $this->assertEquals(10000, $account1->fresh()->balance);
        $this->assertEquals(0, $account2->fresh()->balance);

        $this->actingAs($admin, 'admin')
            ->json('patch', "admin/accounting/transactions/{$transaction->id}",
                $this->newTransaction([
                    'admin_id'    => $admin->id,
                    'account_id'  => $account2->id,
                    'category_id' => $category->id,
                    'amount'      => 100
                ])
            );

        $this->assertEquals(0, $account1->fresh()->balance);
        $this->assertEquals(10000, $account2->fresh()->balance);
    }

    /** @test */
    public function updating_a_transaction_to_an_account_that_has_transfers_have_correct_account_balance()
    {
        $admin = Admin::factory()->create();
        $account1 = Account::factory()->create();
        $account2 = Account::factory()->create();
        $category = Category::factory()->income()->create();

        $transaction = Transaction::factory()->create([
            'admin_id'    => $admin->id,
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
            ->json('patch', "admin/accounting/transactions/{$transaction->id}",
                $this->newTransaction([
                    'account_id'  => $account1->id,
                    'category_id' => $category->id,
                    'amount'      => 120
                ])
            );

        $this->assertEquals(11000, $account1->fresh()->balance);
        $this->assertEquals(1000, $account2->fresh()->balance);
    }
}
