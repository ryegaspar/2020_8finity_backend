<?php

namespace App\Http\Controllers\Admin\Accounting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ChecksController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function store()
    {
        request()->validate([
            'description' => 'required',
            'category_id' => 'required|exists:categories,id',
            'account_id'  => 'required|exists:accounts,id',
            'amount'      => 'required|regex:/^\d+(\.\d{1,2})?$/',
            'post_date'   => 'required|date',
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
                'post_date'   => request('post_date'),
            ]);

        return response()->json([], 201);
    }
}
