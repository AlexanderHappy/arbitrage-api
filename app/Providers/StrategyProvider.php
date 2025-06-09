<?php

namespace App\Providers;

use App\Interfaces\InterfaceExchangeStrategy;
use App\Services\Exchange\KucoinStrategy;
use Illuminate\Support\ServiceProvider;

class StrategyProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            InterfaceExchangeStrategy::class,
            KucoinStrategy::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
