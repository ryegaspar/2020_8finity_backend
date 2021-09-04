<?php

namespace App\Http\Controllers\Admin\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Rules\EnoughAccountBalance;
use Illuminate\Http\Request;

class TransfersController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index(Request $request)
    {
//        $transactions = Transaction::with('admin', 'category', 'account')
//            ->tableFilter($request)
//            ->paginate($request->per_page);

        return response()->json([], 201);
    }

    public function store()
    {
        request()->validate([
            'description'  => 'required',
            'from_account' => 'required|exists:accounts,id',
            'to_account'   => 'required|exists:accounts,id',
            'amount'       => ['required','regex:/^\d+(\.\d{1,2})?$/', new EnoughAccountBalance(request('from_account'))],
            'date'         => 'required|date',
            'notes'        => 'nullable'
        ]);

        request()->user('admin')
            ->transfers()
            ->create([
                'description'  => request('description'),
                'from_account' => request('from_account'),
                'to_account'   => request('to_account'),
                'amount'       => (int)(request('amount') * 100),
                'date'         => request('date'),
                'notes'        => request('notes')
            ]);

        return response()->json([], 201);
    }

}
