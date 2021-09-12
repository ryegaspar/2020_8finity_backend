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
Route::namespace('Auth')->group(function () {
    Route::post('admin/login', 'AdminLoginController@login');
    Route::post('admin/logout', 'AdminLoginController@logout');
});

Route::middleware('auth:admin')
    ->get('/admin', function (Request $request) {
        return auth()->guard('admin')->user();
    });

Route::prefix('admin')
    ->namespace('Admin')
    ->group(function () {

        Route::prefix('dashboard')
            ->namespace('Dashboard')
            ->group(function () {
                Route::get('transactions-summary', 'TransactionSummaryController@show');
                Route::get('transactions', 'TransactionsController@show');
            });

        Route::prefix('accounting')
            ->namespace('Accounting')
            ->group(function () {
                Route::get('accounts', 'AccountsController@index');
                Route::post('accounts', 'AccountsController@store');
                Route::patch('accounts/{account}', 'AccountsController@update');
                Route::delete('accounts/{account}', 'AccountsController@destroy');

                Route::get('transactions', 'TransactionsController@index');
                Route::post('transactions', 'TransactionsController@store');
                Route::patch('transactions/{transaction}', 'TransactionsController@update');
                Route::delete('transactions/{transaction}', 'TransactionsController@destroy');

                Route::get('transfers', 'TransfersController@index');
                Route::post('transfers', 'TransfersController@store');
                Route::patch('transfers/{transfer}', 'TransfersController@update');
                Route::delete('transfers/{transfer}', 'TransfersController@destroy');

                Route::get('categories', 'CategoriesController@index');
                Route::post('categories', 'CategoriesController@store');
                Route::patch('categories/{category}', 'CategoriesController@update');
                Route::delete('categories/{category}', 'CategoriesController@destroy');
            });
    });
