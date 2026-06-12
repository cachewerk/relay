<?php

namespace CacheWerk\Relay\Benchmarks\Support\Clients;

use Swoole\Table;

/**
 * Swoole tables only hold fixed-size scalar columns, so values are
 * serialized into a single string column — comparable to APCu and
 * Relay\Table, which serialize internally.
 */
class SwooleTableClient implements InMemoryClient
{
    protected Table $table;

    protected int $rows;

    protected int $valueSize;

    public function __construct(int $rows = 2048, int $valueSize = 1024)
    {
        $this->rows = $rows;
        $this->valueSize = $valueSize;

        $this->createTable();
    }

    /**
     * Swoole tables cannot be flushed, drop the shared
     * memory block and allocate a fresh one instead.
     */
    public function clear(): bool
    {
        $this->table->destroy();

        return $this->createTable();
    }

    public function get(string $key): mixed
    {
        $value = $this->table->get($key, 'value');

        return is_string($value) ? unserialize($value) : false;
    }

    public function set(string $key, mixed $value): bool
    {
        return $this->table->set($key, ['value' => serialize($value)]);
    }

    protected function createTable(): bool
    {
        $this->table = new Table($this->rows);
        $this->table->column('value', Table::TYPE_STRING, $this->valueSize);

        return $this->table->create();
    }
}
