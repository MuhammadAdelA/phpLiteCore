<?php

declare(strict_types=1);

namespace PhpLiteCore\Utils;

use InvalidArgumentException;

class FormatUtils
{
    /**
     * Format a byte count into a human-readable string using 1024-based units.
     *
     * @param int $bytes     The size in bytes.
     * @param int $precision Number of decimal digits to include.
     * @return string        Formatted size string (e.g., '1.23 MB').
     * @throws InvalidArgumentException If $bytes is negative or $precision is invalid.
     */
    public static function formatBytes(int $bytes, int $precision = 2): string
    {
        if ($bytes < 0) {
            throw new InvalidArgumentException("Byte value cannot be negative: {$bytes}");
        }

        if ($precision < 0) {
            throw new InvalidArgumentException("Precision must be a non-negative integer: {$precision}");
        }

        if ($bytes === 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB'];
        $base = (int) floor(log($bytes, 1024));
        // Prevent overflow beyond defined units
        $base = min($base, count($units) - 1);

        $value = $bytes / (1024 ** $base);

        return round($value, $precision) . ' ' . $units[$base];
    }
}
