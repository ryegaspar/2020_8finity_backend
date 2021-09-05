<?php

namespace App\Http\Controllers\Admin\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaginatedTransferCollection;
use App\Models\Transfer;
use App\Rules\AccountEnoughBalance;
use App\Rules\ActiveAccount;
use Illuminate\Http\Request;

class TransfersController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index(Request $request)
    {
        $transfers = Transfer::with('fromAccount', 'toAccount', 'admin')
//            ->tableFilter($request)
            ->paginate($request->per_page);

        return response()->json(new PaginatedTransferCollection($transfers));
    }

    public function store()
    {
        request()->validate([
            'description'  => 'required',
            'from_account' => ['required', 'exists:accounts,id', new ActiveAccount()],
            'to_account'   => ['required', 'exists:accounts,id', 'different:from_account', new ActiveAccount()],
            'amount'       => [
                'required',
                'regex:/^\d+(\.\d{1,2})?$/',
                new AccountEnoughBalance(request('from_account'))
            ],
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
