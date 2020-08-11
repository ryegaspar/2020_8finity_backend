<?php

namespace Tests\Unit;

use App\Transaction;
use Carbon\Carbon;
use DMS\PHPUnitExtensions\ArraySubset\Assert;
use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;
    use ArraySubsetAsserts;

    /** @test */
    public function can_get_formatted_date()
    {
        $transaction = factory(Transaction::class)->make([
            'date' => Carbon::parse("2020-01-01")
        ]);

        $this->assertEquals('2020-01-01', $transaction->formatted_date);
    }

    /** @test */
    public function can_get_formatted_amount()
    {
        $transaction = factory(Transaction::class)->make([
            'amount' => 1000
        ]);

        $this->assertEquals(1000, $transaction->amount);
        $this->assertEquals("₱10.00", $transaction->amountFormatted);
    }

    /** @test */
    public function transactions_with_an_in_state_are_incomes()
    {
        $incomeA = factory(Transaction::class)->create(['transaction_type' => 'in']);
        $incomeB = factory(Transaction::class)->create(['transaction_type' => 'in']);
        $notIncomeC = factory(Transaction::class)->create(['transaction_type' => 'out']);

        $income = Transaction::income()->get();

        $this->assertTrue($income->contains($incomeA));
        $this->assertTrue($income->contains($incomeB));
        $this->assertFalse($income->contains($notIncomeC));
    }

    /** @test */
    public function transactions_with_an_out_state_are_expenses()
    {
        $expenseA = factory(Transaction::class)->create(['transaction_type' => 'out']);
        $expenseB = factory(Transaction::class)->create(['transaction_type' => 'out']);
        $notExpenseC = factory(Transaction::class)->create(['transaction_type' => 'in']);

        $income = Transaction::expenses()->get();

        $this->assertTrue($income->contains($expenseA));
        $this->assertTrue($income->contains($expenseB));
        $this->assertFalse($income->contains($notExpenseC));
    }

    /** @test */
    public function default_start_date_is_2_months_ago_and_end_date_is_today()
    {
        $twoMonthsAgo = Carbon::now()->startOfMonth()->subMonths(2)->format('Y-m-d');
        $threeMonthsAgo = Carbon::now()->startOfMonth()->subMonths(3)->format('Y-m-d');

        factory(Transaction::class)->create(['date' => $twoMonthsAgo, 'amount' => 20000]);
        factory(Transaction::class)->create(['date' => $twoMonthsAgo, 'amount' => 10000]);
        factory(Transaction::class)->create(['date' => $threeMonthsAgo, 'amount' => 5000]);

        $transactions = Transaction::transactionsBetween()->toArray();

        $expectedTransaction1 = [
            'amount' => "20000",
            'date'   => $twoMonthsAgo
        ];

        $expectedTransaction2 = [
            'amount' => "10000",
            'date'   => $twoMonthsAgo
        ];

        $oldTransaction = [
            'amount' => '5000',
            'date'   => $threeMonthsAgo
        ];

        Assert::assertArraySubset($expectedTransaction1, $transactions[0], true);
        Assert::assertArraySubset($expectedTransaction2, $transactions[1], true);
        $this->assertEquals(2, count($transactions));
    }

    /** @test */
    public function are_ordered_by_date_in_descending_order()
    {
        $oneMonthAgo = Carbon::now()->startOfMonth()->subMonths(1)->format('Y-m-d');
        $fiveDaysAgo = Carbon::now()->subDays(5)->format('Y-m-d');
        $today = Carbon::now()->format('Y-m-d');

        factory(Transaction::class)->create(['date' => $oneMonthAgo, 'amount' => 10000]);
        factory(Transaction::class)->create(['date' => $fiveDaysAgo, 'amount' => 20000]);
        factory(Transaction::class)->create(['date' => $today, 'amount' => 5000]);

        $transactions = Transaction::transactionsBetween()->toArray();

        $expectedTransaction1 = [
            'amount' => "5000",
            'date'   => $today
        ];

        $expectedTransaction2 = [
            'amount' => "20000",
            'date'   => $fiveDaysAgo
        ];

        $expectedTransaction3 = [
            'amount' => '10000',
            'date'   => $oneMonthAgo
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

        factory(Transaction::class)->create(['date' => $threeMonthsAgo, 'amount' => 20000]);
        factory(Transaction::class)->create(['date' => $fiveMonthsAgo, 'amount' => 10000]);
        factory(Transaction::class)->create(['date' => $today, 'amount' => 5000]);

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
