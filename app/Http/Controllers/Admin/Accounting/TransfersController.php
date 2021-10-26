<?php

namespace App\Http\Controllers\Admin\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Resources\TransferResource;
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
            ->tableFilter($request)
            ->paginate($request->per_page);

        return TransferResource::collection($transfers);
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

    public function update(Transfer $transfer)
    {
        $this->authorize('update', $transfer);

        request()->validate([
            'description'  => 'required',
            'from_account' => ['required', 'exists:accounts,id', new ActiveAccount()],
            'to_account'   => ['required', 'exists:accounts,id', 'different:from_account', new ActiveAccount()],
            'amount'       => [
                'required',
                'regex:/^\d+(\.\d{1,2})?$/',
            ],
            'date'         => 'required|date',
            'notes'        => 'nullable'
        ]);

        $transfer->update([
            'description'  => request('description'),
            'from_account' => request('from_account'),
            'to_account'   => request('to_account'),
            'amount'       => (int)(request('amount') * 100),
            'date'         => request('date'),
            'notes'        => request('notes')
        ]);

        return response()->json('', 204);
    }

    public function destroy(Transfer $transfer)
    {
        $this->authorize('delete', $transfer);

        $transfer->delete();

        return response()->json('', 204);
    }
}
