<?php

declare(strict_types=1);

namespace CacheWerk\Relay\Laravel;

use Illuminate\Support\ServiceProvider;

class RelayServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->resolving('redis', function ($redis) {
            $redis->extend('relay', function () {
                return new RelayConnector;
            });
        });
    }
}
