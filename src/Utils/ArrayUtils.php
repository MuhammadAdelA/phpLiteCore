<?php

declare(strict_types=1);

namespace PhpLiteCore\Utils;

class ArrayUtils
{
    /**
     * Determine if an array contains duplicate values.
     *
     * @param array $array The array to check for duplicates.
     * @return bool          True if duplicates are found, false otherwise.
     */
    public static function hasDuplicates(array $array): bool
    {
        // Count values and check for any count greater than 1
        $counts = array_count_values($array);
        foreach ($counts as $count) {
            if ($count > 1) {
                return true;
            }
        }

        return false;
    }
}
