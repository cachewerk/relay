<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RelayClusterTestProvider extends ServiceProvider
{
    public function register(): void
    {
        $port = (int) env('REDIS_CLUSTER_PORT', 7000);

        config()->set('database.redis.clusters.relaycluster', [
            ['host' => '127.0.0.1', 'port' => $port],
            ['host' => '127.0.0.1', 'port' => $port + 1],
            ['host' => '127.0.0.1', 'port' => $port + 2],
        ]);
    }
}
