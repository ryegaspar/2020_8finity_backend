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

        $date = $transaction->formatted_date;

        $this->assertEquals('Jan 1, 2020', $date);
    }

    /** @test */
    public function can_get_formatted_amount()
    {
        $transaction = factory(Transaction::class)->make([
            'amount' => 1000
        ]);

        $this->assertEquals("₱10.00", $transaction->amount);
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
    public function transactions_are_group_by_date_and_sorted()
    {
        $transactionA = factory(Transaction::class)->create(['date' => '01-01-2020']);
        $transactionB = factory(Transaction::class)->create(['date' => '01-02-2020']);

        $transactions = Transaction::transactionsByDate()->get()->toArray();

        $transactions1 = [
            'amount' => "₱100.00",
            'date' => '01-02-2020'
        ];

        $transactions2 = [
            'amount' => "₱100.00",
            'date' => '01-01-2020'
        ];

        Assert::assertArraySubset($transactions1, $transactions[0], true);
        Assert::assertArraySubset($transactions2, $transactions[1], true);
//        $this->assertSame($transactions1, $transactions[0]);
    }
}
