<?php

declare(strict_types=1);

namespace CacheWerk\Relay\PhpBench;

use Redis;

use PhpBench\Registry\Config;
use PhpBench\Executor\ExecutionContext;
use PhpBench\Executor\ExecutionResults;
use PhpBench\Executor\Benchmark\RemoteExecutor;
use PhpBench\Executor\BenchmarkExecutorInterface;

use Symfony\Component\OptionsResolver\OptionsResolver;

class RedisExecutor implements BenchmarkExecutorInterface
{
    /**
     * The decorated executor instance.
     *
     * @var \PhpBench\Executor\Benchmark\RemoteExecutor
     */
    protected $executor;

    /**
     * The `runner.php_env` environment variables.
     *
     * @var array
     */
    protected $env;

    /**
     * Creates a new Redis executor instance.
     *
     * @param  \PhpBench\Executor\Benchmark\RemoteExecutor  $executor
     * @param  array  $env
     * @return void
     */
    public function __construct(RemoteExecutor $executor, array $env)
    {
        $this->env = $env;
        $this->executor = $executor;
    }

    /**
     * {@inheritDoc}
     */
    public function configure(OptionsResolver $options): void
    {
        //
    }

    /**
     * {@inheritDoc}
     */
    public function execute(ExecutionContext $context, Config $config): ExecutionResults
    {
        $results = $this->executor->execute($context, $config);

        $redis = new Redis;
        $redis->connect($this->env['REDIS_HOST'], $this->env['REDIS_PORT']);

        $results->add(
            new RedisResult($redis->info())
        );

        return $results;
    }
}
