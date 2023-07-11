<?php

namespace CacheWerk\Relay\Benchmarks\Support;

class System
{
    public static function cpu(): CpuInfo
    {
        switch (PHP_OS) {
            case 'Darwin':
                return self::macCPU();
            case 'Linux':
                return self::linuxCPU();
            default:
                return new CpuInfo(
                    'Unknown (' . PHP_OS . ')', 0, 0, trim((string) shell_exec('uname -m')),
                );
        }
    }

    public static function macCPU(): CpuInfo
    {
        $result = [];

        $info = shell_exec('sysctl -a | grep machdep.cpu');

        if (empty($info)) {
            return new CpuInfo('macOS', 0, 0, trim((string) shell_exec('uname -m')));
        }

        foreach (explode("\n", trim($info)) as $line) {
            [$key, $value] = explode(':', $line);

            $result[$key] = trim($value);
        }

        return new CpuInfo(
            $result['machdep.cpu.brand_string'],
            (int) $result['machdep.cpu.core_count'],
            (int) $result['machdep.cpu.thread_count'],
            trim((string) shell_exec('uname -m')),
        );
    }

    public static function linuxCPU(): CpuInfo
    {
        $result = ['threads' => 0];

        $info = shell_exec('cat /proc/cpuinfo');

        if (empty($info)) {
            return new CpuInfo('Linux', 0, 0, trim((string) shell_exec('uname -m')));
        }

        foreach (explode("\n", trim($info)) as $line) {
            if (! trim($line)) {
                continue;
            }

            [$key, $value] = explode(':', $line);

            $result['threads'] += trim($key) == 'processor';

            $result[strtolower(trim($key))] = trim($value);
        }

        return new CpuInfo(
            $result['model name'],
            $result['cpu cores'],
            $result['threads'],
            trim((string) shell_exec('uname -m')),
        );
    }
}
