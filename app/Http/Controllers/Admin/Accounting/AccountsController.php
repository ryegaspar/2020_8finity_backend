<?php

namespace App\Http\Controllers\Admin\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Resources\AccountCollection;
use App\Http\Resources\PaginatedAccountCollection;
use App\Models\Account;
use App\Rules\ActiveAccountHasBalance;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AccountsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index(Request $request)
    {
        if ($request->exists('active')) {
            return response()->json(new AccountCollection(Account::active()->get()));
        }

        $accounts = Account::tableFilter()->get();

        return response()->json(new PaginatedAccountCollection($accounts));
    }

    public function store()
    {
        request()->validate([
            'name' => 'required|unique:accounts,name',
        ]);

        Account::create([
            'name' => request('name'),
        ]);

        return response()->json([], 201);
    }

    public function update(Account $account)
    {
        request()->validate([
            'name'      => ['required', Rule::unique('accounts', 'name')->ignore($account->id)],
            'is_active' => ['boolean', new ActiveAccountHasBalance($account->getOriginal('is_active'), $account->balance)]
        ]);

        $account->update([
            'name'      => request('name'),
            'is_active' => request('is_active')
        ]);

        return response()->json([], 204);
    }

    public function destroy(Account $account)
    {
        if ($account->id === 1) {
            return response()->json([], 422);
        }

        if ($account->transactions()->count() ||
            $account->checks()->count() ||
            $account->toTransfers()->count() ||
            $account->fromTransfers()->count())
        {
            return response()->json([], 409);
        }

        $account->delete();

        return response()->json([], 204);
    }
}
