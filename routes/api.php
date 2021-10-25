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
                Route::resource('accounts', 'AccountsController')
                    ->only(['index', 'store', 'update', 'destroy']);

                Route::resource('transactions', 'TransactionsController')
                    ->only(['index', 'store', 'update', 'destroy']);

                Route::resource('transfers', 'TransfersController')
                    ->only(['index', 'store', 'update', 'destroy']);

                Route::resource('checks', 'ChecksController')
                    ->only(['index', 'store', 'update', 'destroy']);

                Route::resource('categories', 'CategoriesController')
                    ->only(['index', 'store', 'update', 'destroy']);

                Route::patch('checks/process/{check}', 'CheckActionsController@clear');
                Route::delete('checks/process/{check}', 'CheckActionsController@cancel');

                Route::get('logs', 'LogsController@index');
            });
    });
