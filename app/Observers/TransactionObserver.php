<?php

namespace App\Observers;

use App\Models\Account;
use App\Models\Transaction;

class TransactionObserver
{
    private function updateAmountSign(Transaction $transaction)
    {
        $transaction->amount =  $transaction->amount * ($transaction->category->type === 'in' ? 1 : -1);
    }

    private function updateAccountBalance($accountId)
    {
        $transactionTotal = Transaction::sumByAccount($accountId);
        Account::find($accountId)->update(['balance' => $transactionTotal]);
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
        $this->updateAccountBalance($transaction->account_id);
    }

    public function updated(Transaction $transaction)
    {
        $this->updateAccountBalance($transaction->account_id);

        if ($transaction->wasChanged('account_id')) {
            $this->updateAccountBalance($transaction->getOriginal('account_id'));
        }
    }

    public function deleted(Transaction $transaction)
    {
        //
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
