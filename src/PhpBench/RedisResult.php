<?php

namespace Relay\PhpBench;

use PhpBench\Model\ResultInterface;

/**
 * Represents the memory reported at the end of the benchmark script.
 */
class RedisResult implements ResultInterface
{
    /**
     * Information and statistics about the Redis server.
     *
     * @var array
     */
    private $info;

    /**
     * {@inheritdoc}
     */
    public static function fromArray(array $values): ResultInterface
    {
        return new self($values['info']);
    }

    /**
     * Create a new Redis result instance.
     *
     * @param  array  $info
     * @return void
     */
    public function __construct(array $info)
    {
        $this->info = $info;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetrics(): array
    {
        return [
            'used_memory_dataset' => $this->info['used_memory_dataset'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getKey(): string
    {
        return 'redis';
    }
}
