<?php

namespace App\Console\Commands;

use App\Jobs\NoopJob;

use CacheWerk\Relay\Laravel\Relay;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Redis;

class VerifyRelay extends Command
{
    protected $signature = 'app:verify-relay {--connection=default}';

    protected $description = 'Verify the Relay integration (scan, queue, bulk).';

    protected bool $failed = false;

    public function handle(): int
    {
        $connection = Redis::connection($this->option('connection'));

        $isCluster = method_exists($connection, 'isCluster') && $connection->isCluster();

        $this->line(sprintf(
            'Connection: %s (cluster: %s)',
            $connection::class,
            $isCluster ? 'true' : 'false',
        ));

        $connection->flushdb();

        $this->verifyScan($connection, $isCluster);
        $this->verifyQueue();

        $this->assert('Relay::stats() returns array', is_array(Relay::stats()));

        $connection->flushdb();

        return $this->failed ? self::FAILURE : self::SUCCESS;
    }

    protected function verifyScan($connection, bool $isCluster): void
    {
        for ($i = 1; $i <= 50; $i++) {
            $connection->set("scan:{$i}", $i);
        }

        $found = [];
        $cursor = null;

        do {
            // Leading wildcard so the match is tolerant of the connection's key prefix.
            $result = $connection->scan($cursor, ['match' => '*scan:*', 'count' => 10]);

            if ($result === false) {
                break;
            }

            [$cursor, $keys] = $result;
            $found = array_merge($found, $keys);
        } while ((int) $cursor !== 0);

        $found = array_unique($found);

        // A cluster SCAN only walks a single node, so we just assert it returned keys.
        $isCluster
            ? $this->assert('scan returns keys', count($found) > 0, count($found).' keys')
            : $this->assert('scan returns all 50 keys', count($found) === 50, count($found).' keys');
    }

    protected function verifyQueue(): void
    {
        Queue::push(new NoopJob);
        $this->assert('queue push increments size', Queue::size() === 1, 'size='.Queue::size());

        Queue::bulk([new NoopJob, new NoopJob, new NoopJob]);
        $this->assert('queue bulk pushes all jobs', Queue::size() === 4, 'size='.Queue::size());

        $this->assert('queue pop returns a job', Queue::pop() !== null);
    }

    protected function assert(string $label, bool $passed, string $detail = ''): void
    {
        $this->line(sprintf('  [%s] %s%s', $passed ? 'PASS' : 'FAIL', $label, $detail !== '' ? " ({$detail})" : ''));

        if (! $passed) {
            $this->failed = true;
        }
    }
}
