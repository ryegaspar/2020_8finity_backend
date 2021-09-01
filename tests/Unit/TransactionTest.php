<?php

namespace Tests\Unit;

use App\Models\Admin;
use App\Models\Category;
use App\Models\Transaction;
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
            'amount' => "20000",
            'date'   => $thisMonth
        ];

        $expectedTransaction2 = [
            'amount' => "10000",
            'date'   => $thisMonth
        ];

        $oldTransaction = [
            'amount' => '5000',
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
            'amount' => "5000",
            'date'   => $tenthOfMonth
        ];

        $expectedTransaction2 = [
            'amount' => "20000",
            'date'   => $fifthOfMonth
        ];

        $expectedTransaction3 = [
            'amount' => '10000',
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
            'amount' => "20000",
            'date'   => $threeMonthsAgo
        ];

        $expectedTransaction2 = [
            'amount' => "10000",
            'date'   => $fiveMonthsAgo
        ];

        $oldTransaction = [
            'amount' => '5000',
            'date'   => $today
        ];

        Assert::assertArraySubset($expectedTransaction1, $transactions[0], true);
        Assert::assertArraySubset($expectedTransaction2, $transactions[1], true);
        $this->assertEquals(2, count($transactions));
    }
}
