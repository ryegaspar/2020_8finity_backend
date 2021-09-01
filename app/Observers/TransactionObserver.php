<?php

namespace App\Observers;

use App\Models\Category;
use App\Models\Transaction;

class TransactionObserver
{
//    public $afterCommit = true;
    /**
     * Handle the Transaction "creating" event.
     *
     * @param  \App\Models\Transaction  $transaction
     * @return void
     */
    public function creating(Transaction $transaction)
    {
        $transaction->amount = $transaction->amount * ($transaction->category->type === 'in' ? 1 : -1);
    }

    /**
     * Handle the Transaction "updating" event.
     *
     * @param  \App\Models\Transaction  $transaction
     * @return void
     */
    public function updating(Transaction $transaction)
    {
        $transaction->amount = $transaction->amount * ($transaction->category->type === 'in' ? 1 : -1);
    }

    /**
     * Handle the Transaction "deleted" event.
     *
     * @param  \App\Models\Transaction  $transaction
     * @return void
     */
    public function deleted(Transaction $transaction)
    {
        //
    }

    /**
     * Handle the Transaction "restored" event.
     *
     * @param  \App\Models\Transaction  $transaction
     * @return void
     */
    public function restored(Transaction $transaction)
    {
        //
    }

    /**
     * Handle the Transaction "force deleted" event.
     *
     * @param  \App\Models\Transaction  $transaction
     * @return void
     */
    public function forceDeleted(Transaction $transaction)
    {
        //
    }
}
