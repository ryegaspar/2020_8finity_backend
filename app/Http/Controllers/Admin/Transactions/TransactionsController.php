<?php

namespace App\Http\Controllers\Admin\Transactions;

use App\Http\Controllers\Controller;
use App\Http\Resources\TransactionCollection;
use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionsController extends Controller
{
    public function show()
    {
        $startDate = request('start_date') ?: null;
        $endDate = request('end_date') ?: null;

        $transactions = Transaction::transactionsBetween($startDate, $endDate);

        return response()->json(new TransactionCollection($transactions));
    }
}
