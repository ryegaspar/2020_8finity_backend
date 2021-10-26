<?php

namespace App\Http\Controllers\Admin\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use App\Rules\ActiveAccount;
use Illuminate\Http\Request;

class TransactionsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index(Request $request)
    {
        $transactions = Transaction::with('admin', 'category', 'account')
            ->tableFilter($request)
            ->paginate($request->per_page);

        return TransactionResource::collection($transactions);
    }

    public function store()
    {
        request()->validate([
            'description'   => 'required',
            'category_id'   => 'required|exists:categories,id',
            'account_id'    => ['required','exists:accounts,id', new ActiveAccount()],
            'amount'        => 'required|regex:/^\d+(\.\d{1,2})?$/',
            'date'          => 'required|date',
            'notes'         => 'nullable'
        ]);

        request()->user('admin')
            ->transactions()
            ->create([
                'description' => request('description'),
                'category_id' => request('category_id'),
                'account_id'  => request('account_id'),
                'amount'      => (int)(request('amount') * 100),
                'date'        => request('date'),
                'notes'       => request('notes')
            ]);

        return response()->json([], 201);
    }

    public function update(Transaction $transaction)
    {
        $this->authorize('update', $transaction);

        request()->validate([
            'description'   => 'required',
            'category_id'   => 'required|exists:categories,id',
            'account_id'    => ['required','exists:accounts,id', new ActiveAccount()],
            'amount'        => 'required|regex:/^\d+(\.\d{1,2})?$/',
            'date'          => 'required|date',
            'notes'         => 'nullable'
        ]);

        $transaction->update([
            'description' => request('description'),
            'category_id' => request('category_id'),
            'account_id'  => request('account_id'),
            'amount'      => (int)(request('amount') * 100),
            'date'        => request('date'),
            'notes'       => request('notes'),
        ]);

        return response()->json('', 204);
    }

    public function destroy(Transaction $transaction)
    {
        $this->authorize('delete', $transaction);

        $transaction->delete();

        return response()->json('', 204);
    }
}
