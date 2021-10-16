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
Route::prefix('admin')
    ->namespace('Auth\\Admin')
    ->group(function () {
        Route::post('login', 'LoginController@login');
        Route::post('logout', 'LoginController@logout');

        Route::post('register', 'RegisterController@register');
        Route::post('forgot-password', 'ForgotPasswordController@sendResetLinkEmail');
        Route::post('reset-password', 'ResetPasswordController@reset');
    });

Route::middleware('auth:admin')
    ->get('/admin', function (Request $request) {
        return auth()->guard('admin')->user();
    });

Route::prefix('admin')
    ->namespace('Admin')
    ->group(function () {
        Route::get('notifications', 'NotificationsController@index');
        Route::delete('notifications', 'NotificationsController@destroy');

        Route::get('lists', 'ListsAdminController');
        Route::get('invitations/{code}', 'InvitationsController@show');

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

                Route::get('checks', 'ChecksController@index');
                Route::post('checks', 'ChecksController@store');
                Route::patch('checks/{check}', 'ChecksController@update');
                Route::delete('checks/{check}', 'ChecksController@destroy');

                Route::patch('checks/process/{check}', 'CheckActionsController@clear');
                Route::delete('checks/process/{check}', 'CheckActionsController@cancel');

                Route::get('categories', 'CategoriesController@index');
                Route::post('categories', 'CategoriesController@store');
                Route::patch('categories/{category}', 'CategoriesController@update');
                Route::delete('categories/{category}', 'CategoriesController@destroy');

                Route::get('logs', 'LogsController@index');
            });
    });
