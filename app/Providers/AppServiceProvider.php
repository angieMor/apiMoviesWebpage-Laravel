<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        Validator::extend('movie_type', function ($attribute, $value, $parameters, $validator) {
            return in_array($value, ['movie', 'series']);
        });

        Validator::extend('order_type', function ($attribute, $value, $parameters, $validator) {
            return in_array($value, ['asc', 'desc']);
        });

        Validator::extend('orderby_type', function ($attribute, $value, $parameters, $validator) {
            return in_array($value, ['title', 'rate', 'Movie', 'Series']);
        });
    }
}
