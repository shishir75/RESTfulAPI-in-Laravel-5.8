<?php

namespace App\Providers;

use App\Mail\UserCreated;
use App\Mail\UserMailChange;
use App\Product;
use App\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
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
        Schema::defaultStringLength(191);

        Product::updated(function($product){
            if ($product->quantity == 0 && $product->isAvailable())
            {
                $product->status = Product::UNAVAILABLE_PRODUCT;
                $product->save();
            }
        });

        // send mail when a user is created using event, this does not work anymore
        User::created(function($user){
            Mail::to($user)->send(new UserCreated($user));
        });

        User::updated(function($user){
            if ($user->isDirty('email'))
            {
                Mail::to($user)->send(new UserMailChange($user));
            }

        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
