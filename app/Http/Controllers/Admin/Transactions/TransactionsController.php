<?php

namespace App\Http\Controllers\Admin\Transactions;

use App\Http\Controllers\Controller;
use App\Http\Resources\TransactionCollection;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use Illuminate\Http\Request;
use JamesDordoy\LaravelVueDatatable\Http\Resources\DataTableCollectionResource;

class TransactionsController extends Controller
{
    public function index(Request $request)
    {

        $length = $request->input('length');
        $sortBy = $request->input('column');
        $orderBy = $request->input('dir');
        $searchValue = $request->input('search');
        $isIncome = $request->input('income');

        $sortBy = str_replace('category', 'categories', $sortBy);

        $query = Transaction::eloquentQuery($sortBy, $orderBy, $searchValue, ['category']);

        if (isset($isIncome)) {
            $query->where('categories.type', $isIncome);
        }

        $data = $query->paginate($length);

//        return new TransactionResource($data);
        return new DataTableCollectionResource($data);

//        $startDate = request('start_date') ?: null;
//        $endDate = request('end_date') ?: null;
//
//        $transactions = Transaction::transactionsBetween($startDate, $endDate);
//
//        return response()->json(new TransactionCollection($transactions));
    }
}
