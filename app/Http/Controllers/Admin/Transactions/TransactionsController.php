<?php

namespace App\Http\Controllers\Admin\Transactions;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaginatedTransactionCollection;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use JamesDordoy\LaravelVueDatatable\Http\Resources\DataTableCollectionResource;

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
//        $length = $request->input('length');
//        $sortBy = $request->input('column');
//        $orderBy = $request->input('dir');
//        $searchValue = $request->input('search');
//        $isIncome = $request->input('income');
//
//        $sortBy = str_replace('category', 'categories', $sortBy);
//
//        $query = Transaction::eloquentQuery($sortBy, $orderBy, $searchValue, ['category']);
//
//        if (isset($isIncome)) {
//            $query->where('categories.type', $isIncome);
//        }
//
//        $data = $query->paginate($length);
//
//        return new DataTableCollectionResource($data);
    }
}
