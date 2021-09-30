<?php

namespace Tests\Unit;

use App\Models\Account;
use App\Models\Admin;
use App\Models\Category;
use App\Models\Log;
use App\Models\Transaction;
use App\Models\Transfer;
use Carbon\Carbon;
use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;
use DMS\PHPUnitExtensions\ArraySubset\Assert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;
    use ArraySubsetAsserts;

    /** @test */
    public function can_get_formatted_date()
    {
        $transaction = Transaction::factory()->make([
            'date' => Carbon::parse("2020-01-01")
        ]);

        $this->assertEquals('2020-01-01', $transaction->formatted_date);
    }

    /** @test */
    public function can_get_formatted_amount()
    {
        $transaction = Transaction::factory()->make([
            'amount' => 1000
        ]);

        $this->assertEquals(1000, $transaction->amount);
    }

    /** @test */
    public function a_transaction_belongs_to_a_category()
    {
        $transaction = Transaction::factory()->create();

        $this->assertInstanceOf(Category::class, $transaction->category);
    }

    /** @test */
    public function a_transaction_belongs_to_admin()
    {
        $transaction = Transaction::factory()->create();

        $this->assertInstanceOf(Admin::class, $transaction->admin);
    }

    /** @test */
    public function a_check_belongs_to_an_account()
    {
        $transaction = Transaction::factory()->create();

        $this->assertInstanceOf(Account::class, $transaction->account);
    }

    /** @test */
    public function eager_loads_categories_when_getting_transactions_between()
    {
        Transaction::factory()->create();

        $transactions = Transaction::transactionsBetween();

        $this->assertTrue($transactions[0]->relationLoaded('category'));
    }

    /** @test */
    public function eager_loads_admins_when_getting_transactions_between()
    {
        Transaction::factory()->create();

        $transactions = Transaction::transactionsBetween();

        $this->assertTrue($transactions[0]->relationLoaded('admin'));
    }

    /** @test */
    public function default_start_date_is_start_of_the_month_until_current_date()
    {
        $thisMonth = Carbon::now()->startOfMonth()->addDays(3)->format('Y-m-d');
        $twoMonthsAgo = Carbon::now()->startOfMonth()->subMonths(2)->format('Y-m-d');

        Transaction::factory()->create(['date' => $thisMonth, 'amount' => 20000]);
        Transaction::factory()->create(['date' => $thisMonth, 'amount' => 10000]);
        Transaction::factory()->create(['date' => $twoMonthsAgo, 'amount' => 5000]);

        $transactions = Transaction::transactionsBetween()->toArray();

        $expectedTransaction1 = [
            'amount' => 20000,
            'date'   => $thisMonth
        ];

        $expectedTransaction2 = [
            'amount' => 10000,
            'date'   => $thisMonth
        ];

        $oldTransaction = [
            'amount' => 5000,
            'date'   => $twoMonthsAgo
        ];

        Assert::assertArraySubset($expectedTransaction1, $transactions[0], true);
        Assert::assertArraySubset($expectedTransaction2, $transactions[1], true);
        $this->assertEquals(2, count($transactions));
    }

    /** @test */
    public function are_ordered_by_date_in_descending_order()
    {
        $startOfMonth = Carbon::now()->startOfMonth()->format('Y-m-d');
        $fifthOfMonth = Carbon::now()->startOfMonth()->addDays(5)->format('Y-m-d');
        $tenthOfMonth = Carbon::now()->startOfMonth()->addDays(10)->format('Y-m-d');

        Transaction::factory()->create(['date' => $startOfMonth, 'amount' => 10000]);
        Transaction::factory()->create(['date' => $fifthOfMonth, 'amount' => 20000]);
        Transaction::factory()->create(['date' => $tenthOfMonth, 'amount' => 5000]);

        $transactions = Transaction::transactionsBetween()->toArray();

        $expectedTransaction1 = [
            'amount' => 5000,
            'date'   => $tenthOfMonth
        ];

        $expectedTransaction2 = [
            'amount' => 20000,
            'date'   => $fifthOfMonth
        ];

        $expectedTransaction3 = [
            'amount' => 10000,
            'date'   => $startOfMonth
        ];

        $this->assertEquals(3, count($transactions));
        Assert::assertArraySubset($expectedTransaction1, $transactions[0], true);
        Assert::assertArraySubset($expectedTransaction2, $transactions[1], true);
        Assert::assertArraySubset($expectedTransaction3, $transactions[2], true);
    }

    /** @test */
    public function can_fetch_transaction_with_defined_dates()
    {
        $fiveMonthsAgo = Carbon::now()->startOfMonth()->subMonths(5)->format('Y-m-d');
        $threeMonthsAgo = Carbon::now()->startOfMonth()->subMonths(3)->format('Y-m-d');
        $today = Carbon::now()->format('Y-m-d');

        Transaction::factory()->create(['date' => $threeMonthsAgo, 'amount' => 20000]);
        Transaction::factory()->create(['date' => $fiveMonthsAgo, 'amount' => 10000]);
        Transaction::factory()->create(['date' => $today, 'amount' => 5000]);

        $transactions = Transaction::transactionsBetween($fiveMonthsAgo, $threeMonthsAgo)->toArray();

        $expectedTransaction1 = [
            'amount' => 20000,
            'date'   => $threeMonthsAgo
        ];

        $expectedTransaction2 = [
            'amount' => 10000,
            'date'   => $fiveMonthsAgo
        ];

        $oldTransaction = [
            'amount' => 5000,
            'date'   => $today
        ];

        Assert::assertArraySubset($expectedTransaction1, $transactions[0], true);
        Assert::assertArraySubset($expectedTransaction2, $transactions[1], true);
        $this->assertEquals(2, count($transactions));
    }

    /** @test */
    public function adding_a_transaction_with_an_income_category_has_positive_amount()
    {
        Transaction::factory()->create([
            'category_id' => Category::factory()->income()->create()->id,
            'amount'      => 100
        ]);

        tap(Transaction::first(), function ($transaction) {
            $this->assertGreaterThan(0, $transaction->amount);
            $this->assertEquals(100, $transaction->amount);
        });
    }

    /** @test */
    public function adding_a_transaction_with_an_expense_category_has_negative_amount()
    {
        Transaction::factory()->create([
            'category_id' => Category::factory()->expense()->create()->id,
            'amount'      => 100
        ]);

        tap(Transaction::first(), function ($transaction) {
            $this->assertLessThan(0, $transaction->amount);
            $this->assertEquals(-100, $transaction->amount);
        });
    }

    /** @test */
    public function adding_a_transaction_with_income_category_adds_to_its_account_balance()
    {
        $account = Account::factory()->create();
        $category = Category::factory()->income()->create();

        Transaction::factory()->create([
            'account_id'  => $account->id,
            'category_id' => $category->id,
            'amount'      => 100
        ]);

        $this->assertEquals(100, $account->fresh()->balance);

        Transaction::factory()->create([
            'account_id'  => $account->id,
            'category_id' => $category->id,
            'amount'      => 50
        ]);

        $this->assertEquals(150, $account->fresh()->balance);
    }

    /** @test */
    public function adding_a_transaction_with_expense_category_lessens_to_its_account_balance()
    {
        $account = Account::factory()->create();
        $category = Category::factory()->expense()->create();

        Transaction::factory()->create([
            'account_id'  => $account->id,
            'category_id' => $category->id,
            'amount'      => 100
        ]);

        $this->assertEquals(-100, $account->fresh()->balance);
    }

    /** @test */
    public function adding_a_transaction_to_an_account_that_has_transfers_have_correct_account_balance()
    {
        $account1 = Account::factory()->create();
        $account2 = Account::factory()->create();
        $category = Category::factory()->income()->create();

        Transaction::factory()->create([
            'account_id'  => $account1->id,
            'category_id' => $category->id,
            'amount'      => 100
        ]);

        $this->assertEquals(100, $account1->fresh()->balance);

        Transfer::factory()->create([
            'from_account' => $account1->id,
            'to_account'   => $account2->id,
            'amount'       => 10,
        ]);

        $this->assertEquals(90, $account1->fresh()->balance);
        $this->assertEquals(10, $account2->fresh()->balance);

        Transaction::factory()->create([
            'account_id'  => $account1->id,
            'category_id' => $category->id,
            'amount'      => 10
        ]);

        $this->assertEquals(100, $account1->fresh()->balance);
    }

    /** @test */
    public function deleting_a_transaction_updates_account_balance()
    {
        $account = Account::factory()->create();
        $category = Category::factory()->income()->create();

        $transaction = Transaction::factory()->create([
            'account_id'  => $account->id,
            'category_id' => $category->id,
            'amount'      => 100
        ]);

        $this->assertEquals(100, $account->fresh()->balance);

        $transaction->delete();

        $this->assertEquals(0, $account->fresh()->balance);
    }

    /** @test */
    public function deleting_a_transaction_to_an_account_that_has_transfers_have_correct_account_balance()
    {
        $account1 = Account::factory()->create();
        $account2 = Account::factory()->create();
        $category = Category::factory()->income()->create();

        Transaction::factory()->create([
            'account_id'  => $account1->id,
            'category_id' => $category->id,
            'amount'      => 100
        ]);

        $transaction = Transaction::factory()->create([
            'account_id'  => $account1->id,
            'category_id' => $category->id,
            'amount'      => 100
        ]);

        $this->assertEquals(200, $account1->fresh()->balance);

        Transfer::factory()->create([
            'from_account' => $account1->id,
            'to_account'   => $account2->id,
            'amount'       => 10,
        ]);

        $this->assertEquals(190, $account1->fresh()->balance);
        $this->assertEquals(10, $account2->fresh()->balance);

        $transaction->delete();

        $this->assertEquals(90, $account1->fresh()->balance);
        $this->assertEquals(10, $account2->fresh()->balance);
    }

    /** @test */
    public function updating_a_transaction_with_an_income_category_has_positive_amount()
    {
        $expenseCategory = Category::factory()->expense()->create();
        $incomeCategory = Category::factory()->income()->create();

        $transaction = Transaction::factory()->create([
            'category_id' => $expenseCategory->id,
            'amount'      => 100
        ]);

        $this->assertEquals(-100, $transaction->amount);

        $transaction->update([
            'category_id' => $incomeCategory->id,
            'amount'      => 100
        ]);

        tap(Transaction::first(), function ($transaction) {
            $this->assertGreaterThan(0, $transaction->amount);
            $this->assertEquals(100, $transaction->amount);
        });
    }

    /** @test */
    public function updating_a_transaction_with_an_expense_category_has_negative_amount()
    {
        $expenseCategory = Category::factory()->expense()->create();
        $incomeCategory = Category::factory()->income()->create();

        $transaction = Transaction::factory()->create([
            'category_id' => $incomeCategory->id,
            'amount'      => 100
        ]);

        $this->assertEquals(100, $transaction->amount);

        $transaction->update([
            'category_id' => $expenseCategory->id,
            'amount'      => 100
        ]);

        tap(Transaction::first(), function ($transaction) {
            $this->assertLessThan(0, $transaction->amount);
            $this->assertEquals(-100, $transaction->amount);
        });
    }

    /** @test */
    public function updating_a_transaction_amount_with_the_same_income_category_updates_its_account_balance()
    {
        $account = Account::factory()->create();
        $category = Category::factory()->income()->create();
        $transaction = Transaction::factory()->create([
            'account_id'  => $account->id,
            'category_id' => $category->id,
            'amount'      => 100
        ]);

        $this->assertEquals(100, $account->fresh()->balance);

        $transaction->update([
            'account_id'  => $account->id,
            'category_id' => $category->id,
            'amount'      => 50
        ]);

        $this->assertEquals(50, $account->fresh()->balance);
    }

    /** @test */
    public function updating_a_transaction_amount_with_the_same_expense_category_updates_its_account_balance()
    {
        $account = Account::factory()->create();
        $category = Category::factory()->expense()->create();
        $transaction = Transaction::factory()->create([
            'account_id'  => $account->id,
            'category_id' => $category->id,
            'amount'      => 100
        ]);

        $this->assertEquals(-100, $account->fresh()->balance);

        $transaction->update([
            'amount' => 50
        ]);

        $this->assertEquals(-50, $account->fresh()->balance);
    }

    /** @test */
    public function updating_a_transaction_amount_with_different_category_types_updates_its_account_balance()
    {
        $account = Account::factory()->create();
        $expenseCategory = Category::factory()->expense()->create();
        $incomeCategory = Category::factory()->income()->create();
        $transaction = Transaction::factory()->create([
            'account_id'  => $account->id,
            'category_id' => $incomeCategory->id,
            'amount'      => 100
        ]);

        $this->assertEquals(100, $account->fresh()->balance);

        $transaction->update([
            'category_id' => $expenseCategory->id,
            'amount'      => 25
        ]);

        $this->assertEquals(-25, $account->fresh()->balance);
    }

    /** @test */
    public function updating_a_transaction_with_different_account_updates_both_accounts()
    {
        $account1 = Account::factory()->create();
        $account2 = Account::factory()->create();
        $category = Category::factory()->income()->create();
        $transaction = Transaction::factory()->create([
            'account_id'  => $account1->id,
            'category_id' => $category->id,
            'amount'      => 100
        ]);

        $this->assertEquals(100, $account1->fresh()->balance);
        $this->assertEquals(0, $account2->fresh()->balance);

        $transaction->update([
            'account_id' => $account2->id,
            'amount'     => 100
        ]);

        $this->assertEquals(0, $account1->fresh()->balance);
        $this->assertEquals(100, $account2->fresh()->balance);
    }

    /** @test */
    public function updating_a_transaction_to_an_account_that_has_transfers_have_correct_account_balance()
    {
        $account1 = Account::factory()->create();
        $account2 = Account::factory()->create();
        $category = Category::factory()->income()->create();

        $transaction = Transaction::factory()->create([
            'account_id'  => $account1->id,
            'category_id' => $category->id,
            'amount'      => 100
        ]);

        $this->assertEquals(100, $account1->fresh()->balance);

        Transfer::factory()->create([
            'from_account' => $account1->id,
            'to_account'   => $account2->id,
            'amount'       => 10,
        ]);

        $this->assertEquals(90, $account1->fresh()->balance);
        $this->assertEquals(10, $account2->fresh()->balance);

        $transaction->update([
            'amount'     => 120
        ]);

        $this->assertEquals(110, $account1->fresh()->balance);
        $this->assertEquals(10, $account2->fresh()->balance);
    }

    /** @test */
    public function log_is_created_when_a_transaction_gets_added()
    {
        // check factory creates - category and account
        $transaction = Transaction::factory()->create();

        $this->assertDatabaseCount('logs', 3);

        tap(Log::find(3), function ($log) use ($transaction) {
            $this->assertEquals('created', $log->action);
            $this->assertEquals('Transaction', $log->loggable_type);

            $changes = json_decode($log->changes, true);

            $this->assertEquals([
                'category_id' => $transaction->category_id,
                'account_id'  => $transaction->account_id,
                'amount'      => $transaction->amount,
                'date'        => $transaction->formattedDate,
            ], $changes);
        });
    }

    /** @test */
    public function log_is_created_when_a_transaction_gets_updated()
    {
        $category = Category::factory()->create();
        $account = Account::factory()->create();

        $transaction = Transaction::factory()->create([
            'category_id' => $category->id,
            'account_id'  => $account->id,
            'amount'      => 50,
            'description' => 'old description',
            'notes'       => 'old notes',
        ]);

        $transaction->update([
            'amount'      => 100,
            'description' => 'new description',
            'notes'       => 'new notes',
        ]);

        $this->assertDatabaseCount('logs', 4);

        tap(Log::find(4),
            function ($log) {
                $this->assertEquals('updated', $log->action);
                $this->assertEquals('Transaction', $log->loggable_type);

                $changes = json_decode($log->changes, true);

                $this->assertEquals([
                    'before' => [
                        'amount'      => 50,
                        'description' => 'old description',
                        'notes'       => 'old notes',
                    ],
                    'after'  => [
                        'amount'      => 100,
                        'description' => 'new description',
                        'notes'       => 'new notes',
                    ]
                ], $changes);
            });
    }

    /** @test */
    public function log_is_created_when_a_transaction_gets_deleted()
    {
        $category = Category::factory()->create();
        $account = Account::factory()->create();

        $transaction = Transaction::factory()->create([
            'category_id' => $category->id,
            'account_id'  => $account->id,
            'amount'      => 50,
            'description' => 'old description',
            'notes'       => 'old notes',
        ]);

        $date = $transaction->formattedDate;

        $transaction->delete();

        $this->assertDatabaseCount('logs', 4);

        tap(Log::find(4), function ($log) use ($category, $account, $date) {
            $this->assertEquals('deleted', $log->action);
            $this->assertEquals('Transaction', $log->loggable_type);

            $changes = json_decode($log->changes, true);

            $this->assertEquals([
                'category_id' => $category->id,
                'account_id'  => $account->id,
                'amount'      => 50,
                'description' => 'old description',
                'notes'       => 'old notes',
                'date'        => $date
            ], $changes);
        });
    }
}
