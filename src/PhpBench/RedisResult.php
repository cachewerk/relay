<?php

declare(strict_types=1);

namespace CacheWerk\Relay\PhpBench;

use PhpBench\Model\ResultInterface;

/**
 * Represents the memory reported at the end of the benchmark script.
 */
class RedisResult implements ResultInterface
{
    /**
     * Information and statistics about the Redis server.
     *
     * @var array<string, mixed>
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
     * @param  array<string, mixed>  $info
     * @return void
     */
    public function __construct(array $info)
    {
        $this->info = $info;
    }

    /**
     * Returns the Redis metrics for the result.
     * 
     * @return array<string, mixed>
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
