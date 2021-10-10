<?php

namespace App\Providers;

use App\InvitationCodeGenerator;
use App\RandomCodeGenerator;
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
        $this->app->bind(InvitationCodeGenerator::class, RandomCodeGenerator::class);
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
