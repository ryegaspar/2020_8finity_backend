<?php

namespace App\Observers;

use App\Models\Account;
use App\Models\Check;

class CheckObserver
{
    private function updateAmountSign(Check $check)
    {
        $check->amount =  $check->amount * ($check->category->type === 'in' ? 1 : -1);
    }

    public function creating(Check $check)
    {
        $this->updateAmountSign($check);
    }

    public function updating(Check $check)
    {
        $this->updateAmountSign($check);
    }

    /**
     * Handle the Check "created" event.
     *
     * @param  \App\Models\Check  $check
     * @return void
     */
    public function created(Check $check)
    {
        Account::find($check->account_id)->recalculateCheckBalance();
    }

    /**
     * Handle the Check "updated" event.
     *
     * @param  \App\Models\Check  $check
     * @return void
     */
    public function updated(Check $check)
    {
        //
    }

    /**
     * Handle the Check "deleted" event.
     *
     * @param  \App\Models\Check  $check
     * @return void
     */
    public function deleted(Check $check)
    {
        //
    }

    /**
     * Handle the Check "restored" event.
     *
     * @param  \App\Models\Check  $check
     * @return void
     */
    public function restored(Check $check)
    {
        //
    }

    /**
     * Handle the Check "force deleted" event.
     *
     * @param  \App\Models\Check  $check
     * @return void
     */
    public function forceDeleted(Check $check)
    {
        //
    }
}
