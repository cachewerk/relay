<?php

namespace CacheWerk\Relay\PhpBench;

use PhpBench\Extension\RunnerExtension;
use PhpBench\DependencyInjection\Container;
use PhpBench\Executor\Benchmark\RemoteExecutor;
use PhpBench\DependencyInjection\ExtensionInterface;

use Symfony\Component\OptionsResolver\OptionsResolver;

class RedisExtension implements ExtensionInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(Container $container): void
    {
        $container->register(RedisExecutor::class, function (Container $container) {
            return new RedisExecutor(
                $container->get(RemoteExecutor::class),
                $container->getParameter(RunnerExtension::PARAM_PHP_ENV)
            );
        }, [
            RunnerExtension::TAG_EXECUTOR => [
                'name' => 'redis',
            ],
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function configure(OptionsResolver $resolver): void
    {
        //
    }
}
