<?php

namespace App\Providers;

use App\Buyer;
use App\Policies\BuyerPolicy;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Buyer::class => BuyerPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Passport::routes();

        Passport::tokensExpireIn(Carbon::now()->addMinutes(30));
        Passport::refreshTokensExpireIn(Carbon::now()->addDays(30));
        Passport::personalAccessTokensExpireIn(Carbon::now()->addMonths(6));

        Passport::enableImplicitGrant();

        // Scope
        Passport::tokensCan([
            'purchase-product' => 'Create a new transaction for a specific product',
            'manage-products' => 'Create, Read, Update, Delete a products - (CRUD)',
            'manage-account' => 'Read your account data, id, name, email, if verified and if admin (can not read password). Modify your account data (email and password). Can not delete your account',
            'read-general' => 'Read general information like purchasing categories, purchased products, selling products, selling categories, your transactions (purchases and sales)',
        ]);
    }
}












