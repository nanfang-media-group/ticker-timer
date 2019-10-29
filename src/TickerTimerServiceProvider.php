<?php

namespace NanfangMediaGroup\TickerTimer;

use NanfangMediaGroup\TickerTimer\Console\ReportCommand;
use Illuminate\Support\ServiceProvider;

class TickerTimerServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        register_shutdown_function(function () {
            TickerTimer::save();
        });
    }

    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                ReportCommand::class,
            ]);
        }
    }
}
