<?php

namespace App\Providers;

use App\Models\Check;
use App\Models\Transaction;
use App\Models\Transfer;
use App\Policies\CheckPolicy;
use App\Policies\TransactionPolicy;
use App\Policies\TransferPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Transaction::class => TransactionPolicy::class,
        Transfer::class    => TransferPolicy::class,
        Check::class       => CheckPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        //
    }
}
