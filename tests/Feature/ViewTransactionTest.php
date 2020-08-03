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
        $transaction = Transaction::create([
//            'transaction_type' => 'in',
            'amount' => 12000,
            'date'   => Carbon::parse("2020-01-01")
        ]);

        $this->getJson('transactions')
            ->assertExactJson([
                [
                    'id'     => $transaction->id,
//                    'transaction_type' => 'in',
                    'amount' => "₱120.00",
                    'date'   => 'Jan 1, 2020'
                ]
            ]);
    }
}
