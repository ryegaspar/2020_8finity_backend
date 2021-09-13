<?php

namespace App\Http\Controllers\Admin\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaginatedCheckCollection;
use App\Models\Check;
use Illuminate\Http\Request;

class ChecksController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index(Request $request)
    {
        $checks = Check::with('admin', 'category', 'account')
            ->tableFilter($request)
            ->paginate($request->per_page);
//        $transactions = Transaction::with('admin', 'category', 'account')
//            ->tableFilter($request)
//            ->paginate($request->per_page);

//        return response()->json(new PaginatedTransactionCollection($transactions));
        return response()->json(new PaginatedCheckCollection($checks));
    }

    public function store()
    {
        request()->validate([
            'description' => 'required',
            'category_id' => 'required|exists:categories,id',
            'account_id'  => 'required|exists:accounts,id',
            'amount'      => 'required|regex:/^\d+(\.\d{1,2})?$/',
            'post_date'   => 'required|date',
            'notes'       => 'nullable'
        ]);

        request()->user('admin')
            ->checks()
            ->create([
                'description' => request('description'),
                'category_id' => request('category_id'),
                'account_id'  => request('account_id'),
                'amount'      => (int)(request('amount') * 100),
                'notes'       => request('notes'),
                'post_date'   => request('post_date'),
            ]);

        return response()->json([], 201);
    }
}
