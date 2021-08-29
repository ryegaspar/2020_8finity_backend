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
    }

    public function store()
    {
        request()->validate([
            'name' => 'required',
        ]);

        Account::create([
            'name' => request('name'),
        ]);

        return response()->json([], 201);
    }
}
