<?php

namespace App\Http\Controllers\Admin\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Resources\AccountCollection;
use App\Http\Resources\PaginatedAccountCollection;
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
        if ($request->exists('active')) {
            return response()->json(new AccountCollection(Account::active()->get()));
        }

        $accounts = Account::tableFilter()->get();

        return response()->json(new PaginatedAccountCollection($accounts));
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

    public function update(Account $account)
    {
        request()->validate([
            'name'      => ['required'],
            'is_active' => ['boolean']
        ]);

        if (request('is_active') === false && $account->balance > 0) {
            return response()
                ->json([
                    'errors' =>
                        ['is_active' => ['cannot deactivate account with non-zero balance']]
                ], 422);
        }

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

//        if (Account::where('category_id', $id)->count()) {
//            return response()->json([], 409);
//        }

        $account->delete();

        return response()->json([], 204);
    }
}
