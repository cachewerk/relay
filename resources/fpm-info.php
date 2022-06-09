<?php

$repeat = intval($_GET['repeat'] ?? 10);

$arr_info = \Relay\Relay::stats();

$mem = $arr_info['memory'];
$endpoints = $arr_info['endpoints'];
$stats = $arr_info['stats'];
$ids = $arr_info['hashes'];

list($total, $limit, $active, $used) = [
    $mem['total'],
    $mem['limit'],
    $mem['active'],
    $mem['used'],
];

$arr_usage = $arr_info['usage'];

list($tot_reqs, $act_reqs, $max_act_reqs, $free_epoch_recs) = [
    $arr_usage['total_requests'],
    $arr_usage['active_requests'],
    $arr_usage['max_active_requests'],
    $arr_usage['free_epoch_records'],
];

list($requests, $misses, $hits, $errors, $oom, $empty) = [
    $stats['requests'],
    $stats['misses'],
    $stats['hits'],
    $stats['errors'],
    $stats['oom'],
    $stats['empty'],
];

function pct($a, $b) {
    return $b != 0
        ? round(100.00 * $a / $b) . '%'
        : '0%';
}

?><!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Relay v<?php echo phpversion('relay'); ?></title>
    <meta http-equiv="refresh" content="<?php echo $repeat; ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://unpkg.com/tailwindcss@^2/dist/tailwind.min.css">
</head>
<body>
    <div class="container max-w-6xl mx-auto p-8">

        <div class="flex flex-wrap gap-16">

            <div class="flex-grow w-full lg:w-2/5">

                <h2 class="text-2xl font-extrabold text-gray-900 tracking-tight mb-4">
                    Memory
                </h2>

                <table class="w-full text-left border-collapse mb-12 mb-12 whitespace-nowrap">
                    <thead class="text-sm font-semibold text-gray-600 pb-2 pr-2 border-b-2 border-gray-200">
                        <td class="py-1 pr-2 border-b border-gray-200">Stat</td>
                        <td class="py-1 pr-2 border-b border-gray-200 text-right">Used</td>
                        <td class="py-1 pr-2 border-b border-gray-200 text-right">Total</td>
                        <td class="py-1 pr-2 border-b border-gray-200 text-right">%</td>
                        <td class="py-1 pr-2 border-b border-gray-200 text-right">Meter</td>
                    </thead>
                    <tbody class="text-sm">
                        <tr>
                            <td class="py-1 pr-2 border-b border-gray-200">Shared allocation</td>
                            <td class="py-1 pr-2 border-b border-gray-200 text-right">
                                <code class="text-purple-600"><?php echo number_format($used); ?></code>
                            </td>
                            <td class="py-1 pr-2 border-b border-gray-200 text-right">
                                <code class="text-purple-600"><?php echo number_format($total); ?></code>
                            </td>
                            <td class="py-1 pr-2 border-b border-gray-200 text-right">
                                <code class="text-purple-600"><?php echo pct($used, $total) ?></code>
                            </td>
                            <td class="py-1 pr-2 border-b border-gray-200 text-right">
                                <meter id="mmap" value="<?php echo $used?>" min="0" max="<?php echo $total; ?>">
                            </td>
                        </tr>
                        <tr>
                            <td class="py-1 pr-2 border-b border-gray-200">Limit</td>
                            <td class="py-1 pr-2 border-b border-gray-200 text-right">
                                <code class="text-purple-600"><?php echo number_format($used); ?> </code>
                            </td>
                            <td class="py-1 pr-2 border-b border-gray-200 text-right">
                                <code class="text-purple-600"><?php echo number_format($limit); ?></code>
                            </td>
                            <td class="py-1 pr-2 border-b border-gray-200 text-right">
                                <code class="text-purple-600"><?php echo pct($used, $limit); ?></code>
                            </td>
                            <td class="py-1 pr-2 border-b border-gray-200 text-right">
                                <meter id="used" value="<?php echo $used; ?>" min="0" max="<?php echo $limit; ?>">
                            </td>
                        </tr>
                    </tbody>
                </table>

            </div>

            <div class="flex-grow w-full lg:w-2/5">

                <h2 class="text-2xl font-extrabold text-gray-900 tracking-tight mb-4">
                    FPM
                </h2>

                <table class="w-full text-left border-collapse mb-12 whitespace-nowrap">
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

            </div>

            <div class="flex-grow w-full lg:w-2/5">

                <h2 class="text-2xl font-extrabold text-gray-900 tracking-tight mb-4">
                    Requests
                </h2>

                <table class="w-full text-left border-collapse mb-12 whitespace-nowrap">
                    <thead class="text-sm font-semibold text-gray-600 pb-2 pr-2 border-b-2 border-gray-200">
                        <td class="py-1 pr-2 border-b border-gray-200">Stat</td>
                        <td class="py-1 pr-2 border-b border-gray-200 text-right">Value</td>
                    </thead>
                    <tbody class="text-sm">
                        <tr>
                            <td class="py-1 pr-2 border-b border-gray-200">Total requests</td>
                            <td class="py-1 pr-2 border-b border-gray-200 text-right">
                                <code class="text-purple-600">
                                    <?php echo number_format($tot_reqs); ?>
                                </code>
                            </td>
                        </tr>
                        <tr>
                            <td class="py-1 pr-2 border-b border-gray-200">Active requests</td>
                            <td class="py-1 pr-2 border-b border-gray-200 text-right">
                                <code class="text-purple-600">
                                    <?php echo number_format($act_reqs); ?>
                                </code>
                            </td>
                        </tr>
                        <tr>
                            <td class="py-1 pr-2 border-b border-gray-200">Maximum active requests</td>
                            <td class="py-1 pr-2 border-b border-gray-200 text-right">
                                <code class="text-purple-600">
                                    <?php echo number_format($max_act_reqs); ?>
                                </code>
                            </td>
                        </tr>
                        <tr>
                            <td class="py-1 pr-2 border-b border-gray-200">Free epoch records</td>
                            <td class="py-1 pr-2 border-b border-gray-200 text-right">
                                <code class="text-purple-600">
                                    <?php echo number_format($free_epoch_recs); ?>
                                </code>
                            </td>
                        </tr>
                    </tbody>
                </table>

            </div>

            <div class="flex-grow w-full lg:w-2/5">

                <h2 class="text-2xl font-extrabold text-gray-900 tracking-tight mb-4">
                    Cache Stats
                </h2>

                <table class="w-full text-left border-collapse mb-12 whitespace-nowrap">
                    <thead class="text-sm font-semibold text-gray-600 pb-2 pr-2 border-b-2 border-gray-200">
                        <td class="py-1 pr-2 border-b border-gray-200">Stat</td>
                        <td class="py-1 pr-2 border-b border-gray-200 text-right">Value</td>
                    </thead>
                    <tbody class="text-sm">
                        <tr>
                            <td class="py-1 pr-2 border-b border-gray-200">Total</td>
                            <td class="py-1 pr-2 border-b border-gray-200 text-right">
                                <code class="text-purple-600">
                                    <?php echo number_format($requests); ?>
                                </code>
                            </td>
                        </tr>
                        <tr>
                            <td class="py-1 pr-2 border-b border-gray-200">Hits</td>
                            <td class="py-1 pr-2 border-b border-gray-200 text-right">
                                <code class="text-purple-600">
                                    <?php echo number_format($hits); ?>
                                </code>
                            </td>
                        </tr>
                        <tr>
                            <td class="py-1 pr-2 border-b border-gray-200">Misses</td>
                            <td class="py-1 pr-2 border-b border-gray-200 text-right">
                                <code class="text-purple-600">
                                    <?php echo number_format($misses); ?>
                                </code>
                            </td>
                        </tr>
                    </tbody>
                </table>

            </div>

            <div class="flex-grow w-full lg:w-2/5">

                <h2 class="text-2xl font-extrabold text-gray-900 tracking-tight mb-4">
                    Errors
                </h2>

                <table class="w-full text-left border-collapse mb-12 whitespace-nowrap">
                    <thead class="text-sm font-semibold text-gray-600 pb-2 pr-2 border-b-2 border-gray-200">
                        <td class="py-1 pr-2 border-b border-gray-200">Stat</td>
                        <td class="py-1 pr-2 border-b border-gray-200 text-right">Value</td>
                    </thead>
                    <tbody class="text-sm">
                        <tr>
                            <td class="py-1 pr-2 border-b border-gray-200">Errors</td>
                            <td class="py-1 pr-2 border-b border-gray-200 text-right">
                                <code class="text-purple-600"><?php echo number_format($errors); ?></code>
                            </td>
                        </tr>
                        <tr>
                            <td class="py-1 pr-2 border-b border-gray-200">OOM</td>
                            <td class="py-1 pr-2 border-b border-gray-200 text-right">
                                <code class="text-purple-600"><?php echo $oom; ?></code>
                            </td>
                        </tr>
                        <tr>
                            <td class="py-1 pr-2 border-b border-gray-200">Empty</td>
                            <td class="py-1 pr-2 border-b border-gray-200 text-right">
                                <code class="text-purple-600"><?php echo $empty; ?></code>
                            </td>
                        </tr>
                    </tbody>
                </table>

            </div>

            <div class="flex-grow w-full lg:w-2/5">

                <h2 class="text-2xl font-extrabold text-gray-900 tracking-tight mb-4">
                    Instances
                </h2>

                <table class="w-full text-left border-collapse mb-12 whitespace-nowrap">
                    <thead class="text-sm font-semibold text-gray-600 pb-2 pr-2 border-b-2 border-gray-200">
                        <td class="py-1 pr-2 border-b border-gray-200">ID</td>
                        <td class="py-1 pr-2 border-b border-gray-200 text-right">Connections</td>
                        <td class="py-1 pr-2 border-b border-gray-200 text-right">Redis Version</td>
                        <td class="py-1 pr-2 border-b border-gray-200 text-right">Keys</td>
                    </thead>
                    <tbody class="text-sm">
                        <?php foreach ($endpoints as $endpoint => $info) : ?>
                            <tr>
                                <td class="py-1 pr-2 border-b border-gray-200">
                                    <code class="text-purple-600"><?php echo $endpoint; ?></code>
                                </td>
                                <td class="py-1 pr-2 border-b border-gray-200 text-right">
                                    <code class="text-purple-600"><?php echo count($info['connections']); ?></code>
                                </td>
                                <td class="py-1 pr-2 border-b border-gray-200 text-right">
                                    <code class="text-purple-600"><?php echo $info['redis']['redis_version']; ?></code>
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
