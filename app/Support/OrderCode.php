<?php

namespace App\Support;

final class OrderCode
{
    public static function forIndex(int $index): string
    {
        $group = intdiv($index, 27);
        $position = $index % 27;
        $prefix = chr(65 + $group);

        return $position === 0 ? $prefix : $prefix.chr(64 + $position);
    }

    public static function next(?string $current): string
    {
        if (! $current || ! preg_match('/^[A-Z]{1,3}$/', $current)) {
            return 'A';
        }

        $length = strlen($current);
        if ($length === 1) {
            return $current.'A';
        }

        $last = ord($current[$length - 1]);
        if ($last < 90) {
            return substr($current, 0, -1).chr($last + 1);
        }

        return chr(ord($current[0]) + 1);
    }
}
