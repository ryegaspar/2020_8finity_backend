<?php

namespace App\Http\Controllers\Admin\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Check;
use App\Models\Transaction;
use Carbon\Carbon;

class CheckActionsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function clear(Check $check)
    {
        $this->authorize('update', $check);

        $transaction = Transaction::create([
            'description' => $check->description,
            'category_id' => $check->category_id,
            'account_id'  => $check->account_id,
            'admin_id'    => auth()->id(),
            'amount'      => $check->amount,
            'date'        => Carbon::now(),
            'notes'       => $check->notes
        ]);

        $check->update([
            'transaction_id' => $transaction->id,
            'status'         => 'cleared'
        ]);

        return response()->json('', 204);
    }
}
