<?php

namespace CacheWerk\Relay\Benchmarks\Support;

class System
{
    public static function cpu(): object
    {
        switch (PHP_OS) {
            case 'Darwin':
                return self::macCPU();
            case 'Linux':
                return self::linuxCPU();
            default:
                return (object) [
                    'type' => 'Unknown (' . PHP_OS . ')',
                    'cores' => 0,
                    'arch' => trim((string) shell_exec('uname -m')),
                ];
        }
    }

    public static function macCPU(): object
    {
        $result = [];

        $info = shell_exec('sysctl -a | grep machdep.cpu');

        if (empty($info)) {
            return (object) [
                'type' => 'macOS',
                'cores' => 0,
                'arch' => trim((string) shell_exec('uname -m')),
            ];
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

    public static function linuxCPU(): object
    {
        $result = [];

        $info = shell_exec('cat /proc/cpuinfo');

        if (empty($info)) {
            return (object) [
                'type' => 'Linux',
                'cores' => 0,
                'arch' => trim((string) shell_exec('uname -m')),
            ];
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
