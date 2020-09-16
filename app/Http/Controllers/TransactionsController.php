<?php

namespace App\Http\Controllers;

use App\Category;
use App\Http\Resources\TransactionCollection;
use App\Transaction;
use Illuminate\Http\Request;

class TransactionsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function show()
    {
        $startDate = request('start_date') ?: null;
        $endDate = request('end_date') ?: null;

        $transactions = Transaction::transactionsBetween($startDate, $endDate);

        return response()->json(new TransactionCollection($transactions));
    }
}
