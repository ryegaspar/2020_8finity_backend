<?php

namespace App\Observers;

use App\Models\Account;
use App\Models\Transaction;
use App\Models\Transfer;

class TransferObserver
{
    /**
     * Handle the Transfer "created" event.
     *
     * @param \App\Models\Transfer $transfer
     * @return void
     */
    public function created(Transfer $transfer)
    {
        Account::find($transfer->from_account)->recalculateBalance();
        Account::find($transfer->to_account)->recalculateBalance();
//        Account::recalculateBalance_temp($transfer->from_account);
//        Account::recalculateBalance_temp($transfer->to_account);
//        $transactionsFromSum = Transaction::sumByAccount($transfer->from_account);
//        $transactionsToSum = Transaction::sumByAccount($transfer->to_account);
//
//        $accountFromSum = Transfer::sumTo($transfer->from_account) - Transfer::sumFrom($transfer->from_account) + $transactionsFromSum;
//        $accountToSum = Transfer::sumTo($transfer->to_account) - Transfer::sumFrom($transfer->to_account) + $transactionsToSum;
//
//        Account::find($transfer->from_account)->update(['balance' => $accountFromSum]);
//        Account::find($transfer->to_account)->update(['balance' => $accountToSum]);
    }

    /**
     * Handle the Transfer "updated" event.
     *
     * @param \App\Models\Transfer $transfer
     * @return void
     */
    public function updated(Transfer $transfer)
    {
        //
    }

    /**
     * Handle the Transfer "deleted" event.
     *
     * @param \App\Models\Transfer $transfer
     * @return void
     */
    public function deleted(Transfer $transfer)
    {
        //
    }

    /**
     * Handle the Transfer "restored" event.
     *
     * @param \App\Models\Transfer $transfer
     * @return void
     */
    public function restored(Transfer $transfer)
    {
        //
    }

    /**
     * Handle the Transfer "force deleted" event.
     *
     * @param \App\Models\Transfer $transfer
     * @return void
     */
    public function forceDeleted(Transfer $transfer)
    {
        //
    }
}
