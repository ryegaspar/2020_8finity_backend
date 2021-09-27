<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Relation::morphMap([
            'Account'     => 'App\Models\Account',
            'Category'    => 'App\Models\Category',
            'Check'       => 'App\Models\Check',
            'Transaction' => 'App\Models\Transaction',
            'Transfer'    => 'App\Models\Transfer'
        ]);
    }
}
