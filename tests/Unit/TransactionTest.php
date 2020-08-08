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
    public function transactions_can_be_group_by_date_and_ordered_descending()
    {
        factory(Transaction::class)->create(['date' => '2020-01-01', 'amount' => 20000]);
        factory(Transaction::class)->create(['date' => '2020-01-01', 'amount' => 10000]);
        factory(Transaction::class)->create(['date' => '2020-01-02', 'amount' => 5000]);

        $transactions = Transaction::transactionsByDate()->toArray();

        $expectedTransaction1 = [
            'amount' => "₱200.00",
            'date'   => '2020-01-01'
        ];

        $expectedTransaction2 = [
            'amount' => "₱100.00",
            'date'   => '2020-01-01'
        ];

        $expectedTransaction3 = [
            'amount' => '₱50.00',
            'date'   => '2020-01-02'
        ];

        Assert::assertArraySubset($expectedTransaction1, $transactions['01'][0], true);
        Assert::assertArraySubset($expectedTransaction2, $transactions['01'][1], true);
        Assert::assertArraySubset($expectedTransaction3, $transactions['02'][0], true);
    }
}
