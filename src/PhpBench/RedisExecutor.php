<?php

namespace Relay\PhpBench;

use Redis;

use Relay\Benchmarks\BenchCase;

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
     * Creates a new Redis executor instance.
     *
     * @param  \PhpBench\Executor\Benchmark\RemoteExecutor  $executor
     * @return void
     */
    public function __construct(RemoteExecutor $executor)
    {
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
        $redis->connect(BenchCase::Host, BenchCase::Port);

        $results->add(
            new RedisResult($redis->info())
        );

        return $results;
    }
}
