<?php

namespace App\Http\Controllers\Admin\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\ExpenseTracker\Money;
use App\Models\Transaction;

class TransactionSummaryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function show()
    {
        $startDate = request('start_date');
        $endDate = request('end_date');

        $expenses = Transaction::sumByCategoryTypeBetween(Category::EXPENSE, $startDate, $endDate);
        $income = Transaction::sumByCategoryTypeBetween(Category::INCOME, $startDate, $endDate);

        $total = $income + $expenses;

        return response()->json([
            'data' => [
                'income'  => [
                    'amount'           => $income,
//                    'amount_formatted' => (new Money($income))->formatted()
                ],
                'expense' => [
                    'amount'           => $expenses,
//                    'amount_formatted' => (new Money($expenses))->formatted()
                ],
                'total'   => [
                    'amount'           => strval($total),
//                    'amount_formatted' => (new Money($total))->formatted()
                ]
            ]
        ]);
    }
}
