<?php

namespace App\Http\Controllers\Admin\Accounting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AccountsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index(Request $request)
    {
//        $transactions = Transaction::with('admin', 'category')
//            ->filter($request)
//            ->paginate($request->per_page);
//
//        return response()->json(new PaginatedTransactionCollection($transactions));
    }
}
