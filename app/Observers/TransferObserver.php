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
    }

    /**
     * Handle the Transfer "updated" event.
     *
     * @param \App\Models\Transfer $transfer
     * @return void
     */
    public function updated(Transfer $transfer)
    {
        Account::find($transfer->from_account)->recalculateBalance();
        Account::find($transfer->to_account)->recalculateBalance();

        if ($transfer->wasChanged('to_account')) {
            Account::find($transfer->getOriginal('to_account'))->recalculateBalance();
        }

        if ($transfer->wasChanged('from_account')) {
            Account::find($transfer->getOriginal('from_account'))->recalculateBalance();
        }
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
