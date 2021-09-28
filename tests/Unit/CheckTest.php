<?php

namespace Tests\Unit;

use App\Models\Account;
use App\Models\Admin;
use App\Models\Category;
use App\Models\Check;
use App\Models\Log;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_check_belongs_to_a_category()
    {
        $check = Check::factory()->create();

        $this->assertInstanceOf(Category::class, $check->category);
    }

    /** @test */
    public function a_check_belongs_to_admin()
    {
        $check = Check::factory()->create();

        $this->assertInstanceOf(Admin::class, $check->admin);
    }

    /** @test */
    public function a_check_belongs_to_an_account()
    {
        $check = Check::factory()->create();

        $this->assertInstanceOf(Account::class, $check->account);
    }

    /** @test */
    public function adding_a_check_with_an_income_category_has_positive_amount()
    {
        $check = Check::factory()->create([
            'category_id' => Category::factory()->income()->create(),
            'amount'      => 100
        ]);

        $this->assertGreaterThan(0, $check->amount);
        $this->assertEquals(100, $check->amount);
    }

    /** @test */
    public function adding_a_check_with_an_expense_category_has_negative_amount()
    {
        $check = Check::factory()->create([
            'category_id' => Category::factory()->expense()->create(),
            'amount'      => 100
        ]);

        $this->assertLessThan(0, $check->amount);
        $this->assertEquals(-100, $check->amount);
    }

    /** @test */
    public function adding_a_check_with_income_category_adds_to_its_account_check_balance()
    {
        $account = Account::factory()->create();
        $category = Category::factory()->income()->create();

        Check::factory()->create([
            'account_id'  => $account->id,
            'category_id' => $category->id,
            'amount'      => 100
        ]);

        $this->assertEquals(100, $account->fresh()->check_balance);

        Check::factory()->create([
            'account_id'  => $account->id,
            'category_id' => $category->id,
            'amount'      => 75
        ]);

        $this->assertEquals(175, $account->fresh()->check_balance);
    }

    /** @test */
    public function adding_a_check_with_expense_category_lessens_to_its_account_check_balance()
    {
        $account = Account::factory()->create();
        $category = Category::factory()->expense()->create();

        Check::factory()->create([
            'account_id'  => $account->id,
            'category_id' => $category->id,
            'amount'      => 100
        ]);

        $this->assertEquals(-100, $account->fresh()->check_balance);
    }

    /** @test */
    public function deleting_a_check_updates_account_balance()
    {
        $account = Account::factory()->create();
        $category = Category::factory()->income()->create();

        $check = Check::factory()->create([
            'account_id'  => $account->id,
            'category_id' => $category->id,
            'amount'      => 100
        ]);

        $this->assertEquals(100, $account->fresh()->check_balance);

        $check->delete();

        $this->assertEquals(0, $account->fresh()->check_balance);
    }

    /** @test */
    public function updating_a_check_with_an_income_category_has_positive_amount()
    {
        $categoryExpense = Category::factory()->expense()->create();
        $categoryIncome = Category::factory()->income()->create();

        $check = Check::factory()->create([
            'category_id' => $categoryExpense->id,
            'amount'      => 100
        ]);

        $this->assertEquals(-100, $check->amount);

        $check->update([
            'category_id' => $categoryIncome->id,
            'amount'      => 75
        ]);

        $this->assertEquals(75, $check->fresh()->amount);
    }

    /** @test */
    public function updating_a_check_with_an_expense_category_has_negative_amount()
    {
        $categoryExpense = Category::factory()->expense()->create();
        $categoryIncome = Category::factory()->income()->create();

        $check = Check::factory()->create([
            'category_id' => $categoryIncome->id,
            'amount'      => 100
        ]);

        $this->assertEquals(100, $check->amount);

        $check->update([
            'category_id' => $categoryExpense->id,
            'amount'      => 75
        ]);

        $this->assertEquals(-75, $check->fresh()->amount);
    }

    /** @test */
    public function updating_a_check_amount_with_the_same_income_category_updates_its_account_balance()
    {
        $account = Account::factory()->create();
        $category = Category::factory()->income()->create();

        $check = Check::factory()->create([
            'account_id'  => $account->id,
            'category_id' => $category->id,
            'amount'      => 100
        ]);

        $this->assertEquals(100, $account->fresh()->check_balance);

        $check->update([
            'amount' => 50
        ]);

        $this->assertEquals(50, $account->fresh()->check_balance);
    }

    /** @test */
    public function updating_a_check_amount_with_the_same_expense_category_updates_its_account_balance()
    {
        $account = Account::factory()->create();
        $category = Category::factory()->expense()->create();
        $check = Check::factory()->create([
            'account_id'  => $account->id,
            'category_id' => $category->id,
            'amount'      => 100
        ]);

        $this->assertEquals(-100, $account->fresh()->check_balance);

        $check->update([
            'amount' => 50
        ]);

        $this->assertEquals(-50, $account->fresh()->check_balance);
    }

    /** @test */
    public function updating_a_checks_amount_with_different_category_types_updates_its_account_balance()
    {
        $account = Account::factory()->create();
        $categoryExpense = Category::factory()->expense()->create();
        $categoryIncome = Category::factory()->income()->create();

        $check = Check::factory()->create([
            'account_id'  => $account->id,
            'category_id' => $categoryIncome->id,
            'amount'      => 100
        ]);

        $this->assertEquals(100, $account->fresh()->check_balance);

        $check->update([
            'category_id' => $categoryExpense->id,
            'amount'      => 25
        ]);

        $this->assertEquals(-25, $account->fresh()->check_balance);
    }

    /** @test */
    public function updating_a_checks_with_different_account_updates_both_accounts()
    {
        $account1 = Account::factory()->create();
        $account2 = Account::factory()->create();
        $category = Category::factory()->income()->create();

        $check = Check::factory()->create([
            'account_id'  => $account1->id,
            'category_id' => $category->id,
            'amount'      => 100
        ]);

        $this->assertEquals(100, $account1->fresh()->check_balance);
        $this->assertEquals(0, $account2->fresh()->check_balance);

        $check->update([
            'account_id'  => $account2->id,
            'amount'      => 100
        ]);

        $this->assertEquals(0, $account1->fresh()->check_balance);
        $this->assertEquals(100, $account2->fresh()->check_balance);
    }

    /** @test */
    public function log_is_created_when_a_check_gets_added()
    {
        // check factory creates - category and account
        $check = Check::factory()->create();

        $this->assertDatabaseCount('logs', 3);

        tap(Log::find(3), function ($log) use ($check) {
            $this->assertEquals('created', $log->action);
            $this->assertEquals('Check', $log->loggable_type);

            $changes = json_decode($log->changes, true);

            $this->assertEquals([
                'category_id' => $check->category_id,
                'account_id'  => $check->account_id,
                'amount'      => $check->amount,
                'due_date'    => $check->formattedDueDate,
            ], $changes);
        });
    }

    /** @test */
    public function log_is_created_when_a_check_gets_updated()
    {
        $category = Category::factory()->create();
        $account = Account::factory()->create();

        $check = Check::factory()->create([
            'category_id' => $category->id,
            'account_id'  => $account->id,
            'amount'      => 50,
            'description' => 'old description',
            'status'      => 'pending',
            'notes'       => 'old notes',
        ]);

        $check->update([
            'amount'      => 100,
            'description' => 'new description',
            'status'      => 'cancelled',
            'notes'       => 'new notes',
        ]);

        $this->assertDatabaseCount('logs', 4);

        tap(Log::find(4),
            function ($log) {
                $this->assertEquals('updated', $log->action);
                $this->assertEquals('Check', $log->loggable_type);

                $changes = json_decode($log->changes, true);

                $this->assertEquals([
                    'before' => [
                        'amount'      => 50,
                        'description' => 'old description',
                        'status'      => 'pending',
                        'notes'       => 'old notes',
                    ],
                    'after'  => [
                        'amount'      => 100,
                        'description' => 'new description',
                        'status'      => 'cancelled',
                        'notes'       => 'new notes',
                    ]
                ], $changes);
            });
    }

    /** @test */
    public function log_is_created_when_a_check_gets_deleted()
    {
        $category = Category::factory()->create();
        $account = Account::factory()->create();

        $check = Check::factory()->create([
            'category_id' => $category->id,
            'account_id'  => $account->id,
            'amount'      => 50,
            'description' => 'old description',
            'status'      => 'pending',
            'notes'       => 'old notes',
        ]);

        $date = $check->formattedDueDate;

        $check->delete();

        $this->assertDatabaseCount('logs', 4);

        tap(Log::find(4), function ($log) use ($category, $account, $date) {
            $this->assertEquals('deleted', $log->action);
            $this->assertEquals('Check', $log->loggable_type);

            $changes = json_decode($log->changes, true);

            $this->assertEquals([
                'category_id' => $category->id,
                'account_id'  => $account->id,
                'amount'      => 50,
                'description' => 'old description',
                'status'      => 'pending',
                'notes'       => 'old notes',
                'due_date'    => $date
            ], $changes);
        });
    }
}
