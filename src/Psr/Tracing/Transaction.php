<?php

declare(strict_types=1);

namespace CacheWerk\Relay\Psr\Tracing;

use LogicException;

use Relay\Relay;

class Transaction
{
    /**
     * The transaction type.
     *
     * @var int
     */
    public int $type;

    /**
     * The buffered commands.
     *
     * @var array<int, array{string, mixed}>
     */
    public array $commands = [];

    /**
     * The underlying Relay client.
     *
     * @var object
     */
    private object $client;

    /**
     * Creates a new buffered transaction.
     *
     * @param  object  $client
     * @param  int  $type
     * @return void
     */
    public function __construct($client, int $type)
    {
        $this->type = $type;
        $this->client = $client;
    }

    /**
     * Buffers called commands.
     *
     * @param  string  $method
     * @param  array<mixed>  $arguments
     * @return $this
     */
    public function __call($method, $arguments)
    {
        $this->commands[] = [$method, $arguments];

        return $this;
    }

    /**
     * Executes the transaction on the client.
     *
     * @return array<mixed>|bool
     */
    public function exec()
    {
        return $this->client->executeBufferedTransaction($this);
    }

    /**
     * Blocks nested transactions.
     *
     * @return void
     */
    public function pipeline()
    {
        throw new LogicException('Nested transactions are not supported.');
    }

    /**
     * Blocks nested transactions.
     *
     * @param  int  $mode
     * @return void
     */
    public function multi(int $mode = Relay::MULTI)
    {
        throw new LogicException('Nested transactions are not supported.');
    }
}
