<?php

namespace CacheWerk\Relay\Benchmarks\Support;

class System
{
    public static function cpu()
    {
        switch (PHP_OS) {
            case 'Darwin':
                return self::macCPU();
            case 'Linux':
                return self::linuxCPU();
            default:
                return 'Unknown (' . PHP_OS . ')';
        };
    }

    public static function macCPU()
    {
        $result = [];

        $info = shell_exec('sysctl -a | grep machdep.cpu');

        if (empty($info)) {
            return 'macOS';
        }

        foreach (explode("\n", trim($info)) as $line) {
            [$key, $value] = explode(':', $line);

            $result[$key] = trim($value);
        }

        return (object) [
            'type' => $result['machdep.cpu.brand_string'],
            'cores' => $result['machdep.cpu.core_count'],
            'arch' => trim((string) shell_exec('uname -m')),
        ];
    }

    public static function linuxCPU()
    {
        $result = [];

        $info = shell_exec('cat /proc/cpuinfo');

        if (empty($info)) {
            return 'Linux';
        }

        foreach (explode("\n", trim($info)) as $line) {
            if (! trim($line)) {
                continue;
            }

            [$key, $value] = explode(':', $line);

            $result[strtolower(trim($key))] = trim($value);
        }

        return (object) [
            'type' => $result['model name'],
            'cores' => $result['cpu cores'],
            'arch' => trim((string) shell_exec('uname -m')),
        ];
    }
}
