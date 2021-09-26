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

        $transaction = $check->createTransaction();

        $check->update([
            'transaction_id' => $transaction->id,
            'status'         => Check::CLEARED
        ]);

        return response()->json('', 204);
    }

    public function cancel(Check $check)
    {
        $this->authorize('delete', $check);

        $check->update(['status' => Check::CANCELLED]);

        return response()->json('', 204);
    }
}
