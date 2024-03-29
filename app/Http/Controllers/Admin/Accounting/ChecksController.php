<?php

namespace App\Http\Controllers\Admin\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Resources\CheckResource;
use App\Models\Check;
use App\Rules\ActiveAccount;
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

        return CheckResource::collection($checks);
    }

    public function store()
    {
        request()->validate([
            'description' => 'required',
            'category_id' => 'required|exists:categories,id',
            'account_id'  => ['required', 'exists:accounts,id', new ActiveAccount()],
            'amount'      => 'required|regex:/^\d+(\.\d{1,2})?$/',
            'due_date'    => 'required|date',
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
                'due_date'    => request('due_date'),
            ]);

        return response()->json([], 201);
    }

    public function update(Check $check)
    {
        $this->authorize('update', $check);

        request()->validate([
            'description' => 'required',
            'category_id' => 'required|exists:categories,id',
            'account_id'  => ['required', 'exists:accounts,id', new ActiveAccount()],
            'amount'      => 'required|regex:/^\d+(\.\d{1,2})?$/',
            'due_date'    => 'required|date',
            'notes'       => 'nullable'
        ]);

        $check->update([
            'description' => request('description'),
            'category_id' => request('category_id'),
            'account_id'  => request('account_id'),
            'amount'      => (int)(request('amount') * 100),
            'due_date'    => request('due_date'),
            'notes'       => request('notes'),
        ]);

        return response()->json('', 204);
    }

    public function destroy(Check $check)
    {
        $this->authorize('delete', $check);

        $check->delete();

        return response()->json('', 204);
    }
}
