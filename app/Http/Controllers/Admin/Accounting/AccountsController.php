<?php

namespace App\Http\Controllers\Admin\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Resources\DatatableAccountCollection;
use App\Models\Account;
use Illuminate\Http\Request;

class AccountsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index(Request $request)
    {
        $accounts = Account::tableView()->get();

        return response()->json(new DatatableAccountCollection($accounts));
//        $transactions = Transaction::with('admin', 'category')
//            ->filter($request)
//            ->paginate($request->per_page);
//
//        return response()->json(new PaginatedTransactionCollection($transactions));
    }
}
