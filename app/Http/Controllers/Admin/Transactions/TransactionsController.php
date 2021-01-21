<?php

namespace App\Http\Controllers\Admin\Transactions;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaginatedTransactionCollection;
use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionsController extends Controller
{
    public function index(Request $request)
    {
        $transactions = new Transaction;

        if ($request->sort) {
            list ($sortCol, $sortDir) = explode('|', $request->sort);
            $transactions = $transactions->orderBy($sortCol, $sortDir);
        }

        if ($request->search) {
            $transactions = $transactions->where('description', 'LIKE', "%{$request->search}%");
        }

        if ($request->filter && $request->filter !== 'all') {
            $filter = $request->filter === 'income' ? 'in' : 'out';
            $transactions = $transactions->whereHas('category', function ($q) use ($filter) {
                $q->where('type', $filter);
            });
        }

        $transactions = $transactions->paginate($request->per_page);

        return response()->json(new PaginatedTransactionCollection($transactions));
    }
}
