<?php

namespace Tests\Unit;

use App\Transaction;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_get_formatted_date()
    {
        $transaction = factory(Transaction::class)->create([
            'date' => Carbon::parse("2020-01-01")
        ]);

        $date = $transaction->formatted_date;

        $this->assertEquals('Jan 1, 2020', $date);
    }

    /** @test */
    public function can_get_formatted_amount()
    {
        $transaction = factory(Transaction::class)->create([
            'amount' => 1000
        ]);

        $this->assertEquals("₱10.00", $transaction->amount);
    }
}
