<?php

namespace App\Observers;

use App\Models\Account;
use App\Models\Transaction;

class TransactionObserver
{
    private function updateAmountSign(Transaction $transaction)
    {
        $transaction->amount =  $transaction->amount * ($transaction->category()->first()->type === 'in' ? 1 : -1);
    }

    public function creating(Transaction $transaction)
    {
        $this->updateAmountSign($transaction);
    }

    public function updating(Transaction $transaction)
    {
        $this->updateAmountSign($transaction);
    }

    public function created(Transaction $transaction)
    {
        Account::find($transaction->account_id)->recalculateBalance();
    }

    public function updated(Transaction $transaction)
    {
        Account::find($transaction->account_id)->recalculateBalance();

        if ($transaction->wasChanged('account_id')) {
            Account::find($transaction->getOriginal('account_id'))->recalculateBalance();
        }
    }

    public function deleted(Transaction $transaction)
    {
        Account::find($transaction->account_id)->recalculateBalance();
    }

    public function restored(Transaction $transaction)
    {
        //
    }

    public function forceDeleted(Transaction $transaction)
    {
        //
    }
}
