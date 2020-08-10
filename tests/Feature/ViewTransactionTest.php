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
        $transactionDay = Carbon::parse("first day of this month");

        $transaction1 = Transaction::create([
            'transaction_type' => 'in',
            'amount'           => 12000,
            'date'             => $transactionDay->format("Y-m-d")
        ]);

        $transaction2 = Transaction::create([
            'transaction_type' => 'out',
            'amount'           => 8000,
            'date'             => $transactionDay->format("Y-m-d")
        ]);

        $this->getJson('transactions')
            ->assertExactJson([
                'data' => [
                    [
                        'id'               => $transaction1->id,
                        'transaction_type' => 'income',
                        'amount'           => "12000",
                        'amount_formatted' => "₱120.00",
                        'date'             => $transactionDay->format("M j, Y")
                    ],
                    [
                        'id'               => $transaction2->id,
                        'transaction_type' => 'expense',
                        'amount'           => "8000",
                        'amount_formatted' => "₱80.00",
                        'date'             => $transactionDay->format("M j, Y")
                    ]
                ]
            ]);
    }
}
