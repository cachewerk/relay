<?php

$refresh = intval($_GET['refresh'] ?? 60);

$info = \Relay\Relay::stats();
$license = \Relay\Relay::license();

$mem = $info['memory'];
$endpoints = $info['endpoints'];
$stats = $info['stats'];
$ids = $info['hashes'];
$usage = $info['usage'];

function pct(float $a, float $b): string
{
    return $b != 0
        ? round(100.00 * $a / $b) . '%'
        : '0%';
}

function humansize(int $size, int $precision = 2): string
{
    for ($i = 0; ($size / 1024) > 0.9; $i++, $size /= 1024) {
    }

    return round($size, $precision) . ' ' . ['B', 'KB', 'MB', 'GB', 'TB'][$i];
}

?><!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Relay v<?php echo phpversion('relay'); ?></title>
    <meta http-equiv="refresh" content="<?php echo $refresh; ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
    <div class="container max-w-6xl mx-auto p-8">

        <div class="flex flex-wrap gap-16">

            <div class="flex-grow w-full lg:w-2/5">

                <h2 class="text-2xl font-extrabold text-gray-900 tracking-tight mb-4">
                    Memory
                </h2>

                <table class="w-full text-left border-collapse mb-12 mb-12">
                    <thead class="text-sm font-semibold text-gray-600 pb-2 pr-2 border-b-2 border-gray-200">
                        <td class="py-1 pr-2 border-b border-gray-200">Metric</td>
                        <td class="py-1 pr-2 border-b border-gray-200 text-right">Value</td>
                        <td class="py-1 pr-2 border-b border-gray-200 text-right">Percentage</td>
                    </thead>

                    <tbody class="text-sm">
                        <tr>
                            <td class="py-1 pr-2 border-b border-gray-200">limit</td>
                            <td class="py-1 pr-2 border-b border-gray-200 text-right">
                                <code class="text-purple-600"><?php echo $mem['limit'] < 0 ? -1 : humansize((int) $mem['limit']); ?></code>
                            </td>
                            <td class="py-1 pr-2 border-b border-gray-200 text-right"></td>
                        </tr>
                        <tr>
                            <td class="py-1 pr-2 border-b border-gray-200">total</td>
                            <td class="py-1 pr-2 border-b border-gray-200 text-right">
                                <code class="text-purple-600"><?php echo humansize((int) $mem['total']); ?></code>
                            </td>
                            <td class="py-1 pr-2 border-b border-gray-200 text-right"></td>
                        </tr>
                        <tr>
                            <td class="py-1 pr-2 border-b border-gray-200">used</td>
                            <td class="py-1 pr-2 border-b border-gray-200 text-right">
                                <code class="text-purple-600"><?php echo humansize((int) $mem['used']); ?></code>
                            </td>
                            <td class="py-1 pr-2 border-b border-gray-200 text-right">
                                <code class="text-purple-600"><?php echo pct($mem['used'], $mem['total']) ?></code>
                                <meter value="<?php echo $mem['used']; ?>" min="0" max="<?php echo $mem['total']; ?>">
                            </td>
                        </tr>
                        <tr>
                            <td class="py-1 pr-2 border-b border-gray-200">active</td>
                            <td class="py-1 pr-2 border-b border-gray-200 text-right">
                                <code class="text-purple-600"><?php echo humansize((int) $mem['active']); ?> </code>
                            </td>
                            <td class="py-1 pr-2 border-b border-gray-200 text-right">
                                <code class="text-purple-600"><?php echo pct($mem['active'], $mem['total']); ?></code>
                                <meter value="<?php echo $mem['active']; ?>" min="0" max="<?php echo $mem['total']; ?>">
                            </td>
                        </tr>
                    </tbody>
                </table>

            </div>

            <div class="flex-grow w-full lg:w-2/5">

                <h2 class="text-2xl font-extrabold text-gray-900 tracking-tight mb-4">
                    Requests
                </h2>

                <table class="w-full text-left border-collapse mb-12">
                    <thead class="text-sm font-semibold text-gray-600 pb-2 pr-2 border-b-2 border-gray-200">
                        <td class="py-1 pr-2 border-b border-gray-200">Metric</td>
                        <td class="py-1 pr-2 border-b border-gray-200 text-right">Value</td>
                    </thead>
                    <tbody class="text-sm">

                        <?php foreach ([
                            'total_requests',
                            'active_requests',
                            'max_active_requests',
                            'free_epoch_records',
                        ] as $name) : ?>
                            <tr>
                                <td class="py-1 pr-2 border-b border-gray-200"><?php echo $name; ?></td>
                                <td class="py-1 pr-2 border-b border-gray-200 text-right">
                                    <code class="text-purple-600">
                                        <?php echo number_format($usage[$name]); ?>
                                    </code>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                    </tbody>
                </table>

            </div>

            <div class="flex-grow w-full lg:w-2/5">

                <h2 class="text-2xl font-extrabold text-gray-900 tracking-tight mb-4">
                    Cache
                </h2>

                <table class="w-full text-left border-collapse mb-12">
                    <thead class="text-sm font-semibold text-gray-600 pb-2 pr-2 border-b-2 border-gray-200">
                        <td class="py-1 pr-2 border-b border-gray-200">Metric</td>
                        <td class="py-1 pr-2 border-b border-gray-200 text-right">Value</td>
                    </thead>
                    <tbody class="text-sm">

                        <?php foreach ([
                            'requests',
                            'hits',
                            'misses',
                            'ops_per_sec',
                            'bytes_sent',
                            'bytes_received',
                            'command_usec',
                            'rinit_usec',
                            'rshutdown_usec',
                            'sigio_usec',
                        ] as $name) : ?>
                            <tr>
                                <td class="py-1 pr-2 border-b border-gray-200">
                                    <?php echo $name; ?>
                                </td>
                                <td class="py-1 pr-2 border-b border-gray-200 text-right">
                                    <code class="text-purple-600">
                                        <?php if (strpos($name, 'bytes_') === 0) : ?>
                                            <?php echo humansize((int) $stats[$name]); ?>
                                        <?php elseif (strpos($name, '_usec')) : ?>
                                            <?php echo number_format($stats[$name] / 1000, 2); ?> ms
                                        <?php else : ?>
                                            <?php echo number_format($stats[$name]); ?>
                                        <?php endif; ?>
                                    </code>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                    </tbody>
                </table>

            </div>

            <div class="flex-grow w-full lg:w-2/5">

                <h2 class="text-2xl font-extrabold text-gray-900 tracking-tight mb-4">
                    Process
                </h2>

                <table class="w-full text-left border-collapse mb-12">
                    <thead class="text-sm font-semibold text-gray-600 pb-2 pr-2 border-b-2 border-gray-200">
                        <td class="py-1 pr-2 border-b border-gray-200">ID</td>
                        <td class="py-1 pr-2 border-b border-gray-200 text-right">Value</td>
                    </thead>
                    <tbody class="text-sm">
                        <?php foreach ($ids as $key => $val) : ?>
                            <tr>
                                <td class="py-1 pr-2 border-b border-gray-200"><?php echo $key; ?></td>
                                <td class="py-1 pr-2 border-b border-gray-200 text-right">
                                    <code class="text-purple-600"><?php echo $val; ?></code>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <h2 class="text-2xl font-extrabold text-gray-900 tracking-tight mb-4">
                    Errors
                </h2>

                <table class="w-full text-left border-collapse mb-12">
                    <thead class="text-sm font-semibold text-gray-600 pb-2 pr-2 border-b-2 border-gray-200">
                        <td class="py-1 pr-2 border-b border-gray-200">Type</td>
                        <td class="py-1 pr-2 border-b border-gray-200 text-right">Value</td>
                    </thead>
                    <tbody class="text-sm">

                        <?php foreach ([
                            'errors',
                            'oom',
                            'empty',
                            'dirty_skips',
                        ] as $name) : ?>
                            <tr>
                                <td class="py-1 pr-2 border-b border-gray-200"><?php echo $name; ?></td>
                                <td class="py-1 pr-2 border-b border-gray-200 text-right">
                                    <code class="text-purple-600">
                                        <?php echo number_format($stats[$name]); ?>
                                    </code>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                    </tbody>
                </table>

            </div>

            <div class="flex-grow w-full lg:w-2/5">

                <h2 class="text-2xl font-extrabold text-gray-900 tracking-tight mb-4">
                    License
                </h2>

                <table class="w-full text-left border-collapse mb-12">
                    <thead class="text-sm font-semibold text-gray-600 pb-2 pr-2 border-b-2 border-gray-200">
                        <td class="py-1 pr-2 border-b border-gray-200">Key</td>
                        <td class="py-1 pr-2 border-b border-gray-200 text-right">Value</td>
                    </thead>
                    <tbody class="text-sm">
                        <?php foreach ([
                            'state',
                            'reason',
                            'memory-limit',
                            'response-code',
                            'response-id',
                            'timestamp',
                            'attempts',
                            'errors',
                        ] as $name) : ?>
                            <tr>
                                <td class="py-1 pr-2 border-b border-gray-200"><?php echo $name; ?></td>
                                <td class="py-1 pr-2 border-b border-gray-200 text-right">
                                    <code class="text-purple-600">
                                        <?php if ($name === 'timestamp') : ?>
                                            <time datetime="<?php echo date('c', $license[$name]); ?>" title="<?php echo date('r', $license[$name]); ?>">
                                                <?php echo $license[$name]; ?>
                                            </time>
                                        <?php else : ?>
                                            <?php echo $license[$name]; ?>
                                        <?php endif; ?>
                                    </code>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

            </div>

            <div class="flex-grow w-full lg:w-2/5">

                <h2 class="text-2xl font-extrabold text-gray-900 tracking-tight mb-4">
                    Instances
                </h2>

                <table class="w-full text-left border-collapse mb-12">
                    <thead class="text-sm font-semibold text-gray-600 pb-2 pr-2 border-b-2 border-gray-200">
                        <td class="py-1 pr-2 border-b border-gray-200">ID</td>
                        <td class="py-1 pr-2 border-b border-gray-200 text-right">Redis</td>
                        <td class="py-1 pr-2 border-b border-gray-200 text-right">Connections</td>
                        <td class="py-1 pr-2 border-b border-gray-200 text-right">Keys</td>
                    </thead>
                    <tbody class="text-sm">
                        <?php foreach ($endpoints as $endpoint => $info) : ?>
                            <tr>
                                <td class="py-1 pr-2 border-b border-gray-200">
                                    <code class="text-purple-600 break-all"><?php echo $endpoint; ?></code>
                                </td>
                                <td class="py-1 pr-2 border-b border-gray-200 text-right">
                                    <code class="text-purple-600"><?php echo $info['redis']['redis_version']; ?></code>
                                </td>
                                <td class="py-1 pr-2 border-b border-gray-200 text-right">
                                    <code class="text-purple-600"><?php echo count($info['connections']); ?></code>
                                </td>
                                <td class="py-1 pr-2 border-b border-gray-200 text-right">
                                    <code class="text-purple-600"><?php echo number_format(
                                        array_sum(array_map(fn ($conn) => array_sum($conn['keys']), $info['connections']))
                                    ); ?></code>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

            </div>

        </div>

    </div>
</body>
</html>
