<?php

namespace Tests\Feature;

use App\Transaction;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ViewTransactionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function users_can_view_transactions()
    {
        $transaction1 = Transaction::create([
            'transaction_type' => 'in',
            'amount'           => 12000,
            'date'             => Carbon::parse("2020-01-01")
        ]);

        $transaction2 = Transaction::create([
            'transaction_type' => 'out',
            'amount'           => 8000,
            'date'             => Carbon::parse("2020-01-01")
        ]);

        $this->getJson('transactions')
            ->assertExactJson([
                [
                    'id'               => $transaction1->id,
                    'transaction_type' => 'income',
                    'amount'           => "₱120.00",
                    'date'             => 'Jan 1, 2020'
                ],
                [
                    'id'               => $transaction2->id,
                    'transaction_type' => 'expense',
                    'amount'           => "₱80.00",
                    'date'             => 'Jan 1, 2020'
                ]
            ]);
    }
}
