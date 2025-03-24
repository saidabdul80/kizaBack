<?php

namespace App\Providers;

use App\Services\Contracts\TransactionServiceInterface;
use App\Services\ExchangeRateResolver;
use App\Services\TransactionService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(TransactionServiceInterface::class, TransactionService::class);
        $this->app->singleton('exchange', function ($app) {
            return new ExchangeRateResolver();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);
    }
}
