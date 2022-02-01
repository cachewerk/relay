<?php

declare(strict_types=1);

namespace CacheWerk\Relay\Laravel;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin CacheWerk\Relay\Laravel\RelayConnection
 */
class Relay extends Facade
{
    /**
     * {@inheritDoc}
     */
    protected static function getFacadeAccessor()
    {
        return 'redis';
    }
}
