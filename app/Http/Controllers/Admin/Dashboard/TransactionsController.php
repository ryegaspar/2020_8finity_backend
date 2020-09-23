<?php

namespace App\Http\Controllers\Admin\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Resources\TransactionCollection;
use App\Models\Transaction;

class TransactionsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function show()
    {
        $startDate = request('start_date') ?: null;
        $endDate = request('end_date') ?: null;

        $transactions = Transaction::transactionsBetween($startDate, $endDate);

        return response()->json(new TransactionCollection($transactions));
    }
}
