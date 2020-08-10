<?php

namespace App\Http\Controllers;

use App\Http\Resources\TransactionCollection;
use App\Transaction;
use Illuminate\Http\Request;

class TransactionsController extends Controller
{
    public function show()
    {
//        return response()->json(TransactionResource::collection(Transaction::all()));
        return response()->json(new TransactionCollection(Transaction::transactionsBetween()));
    }
}
