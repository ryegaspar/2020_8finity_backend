<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:admin')
    ->get('/admin', function (Request $request) {
        return auth()->guard('admin')->user();
    });

Route::prefix('admin')
    ->namespace('Admin')
    ->group(function () {
        Route::get('dashboard/transactions-summary', 'Dashboard\TransactionSummaryController@show');
        Route::get('dashboard/transactions', 'Dashboard\TransactionsController@show');
        Route::get('transactions', 'Transactions\TransactionsController@show');
        Route::get('/categories', 'CategoriesController@show');
    });
