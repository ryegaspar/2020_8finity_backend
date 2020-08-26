<?php

namespace App\Http\Controllers;

use App\Category;
use App\ExpenseTracker\Money;
use App\Transaction;

class TransactionSummaryController extends Controller
{
    public function show()
    {
        $expenses = Transaction::sumByCategoryType(Category::EXPENSE);
        $income = Transaction::sumByCategoryType(Category::INCOME);
        $total = $income - $expenses;

        return response()->json([
            'data' => [
                'income'  => [
                    'amount'           => $income,
                    'amount_formatted' => (new Money($income))->formatted()
                ],
                'expense' => [
                    'amount'           => $expenses,
                    'amount_formatted' => (new Money($expenses))->formatted()
                ],
                'total'   => [
                    'amount'           => strval($total),
                    'amount_formatted' => (new Money($total))->formatted()
                ]
            ]
        ]);
    }
}
