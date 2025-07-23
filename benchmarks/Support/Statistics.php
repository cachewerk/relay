<?php

namespace CacheWerk\Relay\Benchmarks\Support;

class Statistics
{
    /**
     * @param  array<int, int|float>  $values
     */
    public static function stdev(array $values, bool $sample = false): float
    {
        $variance = self::variance($values, $sample);

        return \sqrt($variance);
    }

    /**
     * @param  array<int, int|float>  $values
     * @return int|float
     */
    public static function variance(array $values, bool $sample = false)
    {
        $average = self::mean($values);
        $sum = 0;

        foreach ($values as $value) {
            $diff = pow($value - $average, 2);
            $sum += $diff;
        }

        if (count($values) === 0) {
            return 0;
        }

        $variance = $sum / (count($values) - ($sample ? 1 : 0));

        return $variance;
    }

    /**
     * @param  array<int, int|float>  $values
     * @return int|float
     */
    public static function mean(array $values)
    {
        if (empty($values)) {
            return 0;
        }

        $sum = array_sum($values);

        if ($sum === 0) {
            return 0;
        }

        $count = count($values);

        return $sum / $count;
    }

    /**
     * @param  array<int, int|float>  $values
     * @return int|float
     */
    public static function median(array $values)
    {
        $count = count($values);
        sort($values);

        $mid = (int) floor(($count - 1) / 2);

        return ($values[$mid] + $values[$mid + 1 - $count % 2]) / 2;
    }

    /**
     * @param  array<int, int|float>  $values
     */
    public static function rstdev(array $values, bool $sample = false): float
    {
        $mean = self::mean($values);

        return $mean ? self::stdev($values, $sample) / $mean * 100 : 0;
    }
}
