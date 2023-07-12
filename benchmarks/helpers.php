<?php

namespace CacheWerk\Relay\Benchmarks;

use Exception;

function printUsage(string $script): void
{
    $usage = <<<EOT
        Usage: php $script [options] [--] [<file>...]

        Options:
            -h, --host         The Redis host to connect to. Defaults to '127.0.0.1'.
            -p, --port         The Redis port to connect to. Defaults to 6379.
            -a, --auth         The Redis password for legacy authentication.  Defaults to none.
                --user         The Redis username for ACL authentication.  Defaults to none.
                --pass         The redis password for ACL authentication.  Defaults to none.
                --workers      Specifies the number of worker threads. Defaults to 1.
                --duration     Specifies the duration of the test in seconds. Defaults to 1.0.
                --runs         Specifies the number of test runs. Defaults to 5 for a single worker, 1 for multiple workers.
                --filter       Specifies a filter to apply to the test.  For example "--filter '^Relay$'" to only run Relay benchmarks.
                --json         Output results in JSON instead of a table.
            -v, --verbose      Enables verbose output.
                --warmup       Specifies how many warm up runs to execute.  Defaults to 1.
                --key-type     A comma separated list of key types (string, set, hash, list, zset, hyperloglog).  Defaults to all.
                --command-type A comma separated list of command types (read, write).  Defaults to read and write.
                --help         Prints this help message.

        Arguments:
            <file>             One or more filenames to process.
    EOT;

    fprintf(STDERR, $usage);

    fprintf(STDERR, "\n\nAvailable files:\n\n");

    $files = glob(__DIR__ . '/Benchmark*.php');

    if (! is_array($files)) {
        throw new Exception('Could not read benchmark files!');
    }

    foreach ($files as $file) {
        fprintf(STDERR, "  %s\n", basename($file));
    }
}

/**
 * @param  array<int|string, string>  $opt
 * @param  array<int, string>  $default
 * @return array<int|string, string>
 */
function getCsvOption(array $opt, string $key, array $default): array
{
    if (! isset($opt[$key])) {
        return $default;
    }

    return array_map(
        fn ($v) => trim($v),
        explode(',', $opt[$key])
    );
}

/**
 * @param  array<int|string, string>  $opt
 * @param  array<int, string>  $default
 */
function getCommandTypes(array $opt, string $key, array $default): int
{
    $result = 0;

    foreach (getCsvOption($opt, $key, $default) as $type) {
        if (! strcasecmp($type, 'read')) {
            $result |= Support\Benchmark::READ;
        } elseif (! strcasecmp($type, 'write')) {
            $result |= Support\Benchmark::WRITE;
        } elseif (! strcasecmp($type, 'default')) {
            $result |= Support\Benchmark::DEFAULT;
        } else {
            fprintf(STDERR, "Error: Command type is only `read`, `write`, or `default`\n");
            exit(1);
        }
    }

    return $result;
}

/**
 * @param  array<int|string, string>  $opt
 * @param  array<int, string>  $default
 */
function getKeyTypes(array $opt, string $key, array $default): int
{
    $result = 0;

    $lookup = [
        'all' => Support\Benchmark::ALL,
        'utility' => Support\Benchmark::UTILITY,
        'string' => Support\Benchmark::STRING,
        'hash' => Support\Benchmark::HASH,
        'set' => Support\Benchmark::SET,
        'list' => Support\Benchmark::LIST,
        'zset' => Support\Benchmark::ZSET,
        'stream' => Support\Benchmark::STREAM,
        'hyperloglog' => Support\Benchmark::HYPERLOGLOG,
        'hll' => Support\Benchmark::HYPERLOGLOG,
    ];

    foreach (getCsvOption($opt, $key, $default) as $type) {
        $type = strtolower($type);

        if (! isset($lookup[$type])) {
            fprintf(STDERR, "Unknown key type `%s`\n", $type);
            exit(1);
        }

        $result |= $lookup[$type];
    }

    return $result;
}
