<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ViewTransactionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_view_transactions()
    {
        $transaction = Transaction::create([
            'transaction_type' => 'in',
            'amount'           => 120000,
            'date'             => '01/01/2020'
        ]);

        $this->json('get', 'transactions')
            ->assertExactJson([
                'data' => [
                    'id'               => $transaction->id,
                    'transaction_type' => 'in',
                    'amount'           => 12000,
                    'date'             => '01/01/2020'
                ]
            ]);
    }
}
