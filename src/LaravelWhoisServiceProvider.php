<?php

namespace OTGH\LaravelWhois;

use Illuminate\Support\ServiceProvider;

class LaravelWhoisServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/whois.php', 'whois');

    }

    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/whois.php' => config_path('whois.php'),
        ], 'laravel-whois');
    }
}
