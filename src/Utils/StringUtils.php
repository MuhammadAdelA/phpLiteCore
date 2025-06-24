<?php
// File: src/Utils/StringUtils.php
declare(strict_types=1);

namespace PhpLiteCore\Utils;

use Random\RandomException;
use RuntimeException;

class StringUtils
{
    /**
     * Separate a string into chunks with a given separator. Default chunk length is floor(sqrt(string length)).
     *
     * @param string $str       The input string to break into chunks.
     * @param string $separator The string to insert between chunks.
     * @return string           The resulting separated string.
     */
    public static function separateString(string $str, string $separator = '-'): string
    {
        // Calculate chunk length based on string length
        $length = mb_strlen($str);
        $chunkLength = (int) floor(sqrt($length));

        // If chunk length is less than 1 or greater than string length, return original
        if ($chunkLength < 1 || $chunkLength >= $length) {
            return $str;
        }

        // Use chunk_split to add separator after each chunk
        $result = chunk_split($str, $chunkLength, $separator);

        // Remove possible trailing separator
        return rtrim($result, $separator);
    }

    /**
     * Generate a strong password containing a customizable mix of character sets.
     * Strength percent adjusts the number of character sets included:
     * 100% uses all selected sets, lower percentages include fewer sets in order 'l', 'u', 'd', 's'.
     *
     * @param int $length Desired total length of the password.
     * @param int $strengthPercent Percentage of strength (0-100) to determine how many sets to include.
     * @param string $separator Optional separator to insert between chunks of the password.
     * @param string $availableSets A string of characters indicating which sets are available: 'l' (lowercase), 'u' (uppercase), 'd' (digits), 's' (special).
     * @return string                 The generated password, optionally separated by the given separator.
     * @throws RandomException
     */
    public static function generateStrongPassword(
        int $length = 12,
        int $strengthPercent = 100,
        string $separator = '',
        string $availableSets = 'luds'
    ): string {
        // Validate strength percentage
        $strengthPercent = max(0, min(100, $strengthPercent));

        // Determine how many sets to include based on strength percentage
        $allSets = str_split($availableSets);
        $totalSets = count($allSets);
        $setsToInclude = max(1, (int) ceil($totalSets * $strengthPercent / 100));
        $chosenSets = array_slice($allSets, 0, $setsToInclude);

        // Build actual sets map
        $setsMap = [];
        foreach ($chosenSets as $setKey) {
            switch ($setKey) {
                case 'l':
                    $setsMap[] = 'abcdefghjkmnpqrstuvwxyz';
                    break;
                case 'u':
                    $setsMap[] = 'ABCDEFGHJKMNPQRSTUVWXYZ';
                    break;
                case 'd':
                    $setsMap[] = '123456789';
                    break;
                case 's':
                    $setsMap[] = '!@#$%&*?';
                    break;
            }
        }

        // Ensure at least one character from each chosen set
        $passwordChars = [];
        $allChars = '';
        foreach ($setsMap as $set) {
            $charList = mb_str_split($set);
            $passwordChars[] = $charList[random_int(0, count($charList) - 1)];
            $allChars .= $set;
        }

        // Fill the rest
        $remaining = $length - count($passwordChars);
        $allList = mb_str_split($allChars);
        for ($i = 0; $i < $remaining; $i++) {
            $passwordChars[] = $allList[random_int(0, count($allList) - 1)];
        }

        // Shuffle to avoid predictable sequences
        shuffle($passwordChars);
        $password = implode('', $passwordChars);

        // Insert separators if requested
        return $separator !== ''
            ? self::separateString($password, $separator)
            : $password;
    }

    /**
     * Generate a real unique string of specified length using cryptographically secure methods.
     * Optionally, insert a separator between chunks of the ID.
     *
     * @param int    $length    Desired total length of the unique ID.
     * @param string $separator Separator to insert between chunks (use empty for none).
     * @return string           The generated unique ID string.
     * @throws RuntimeException If a secure random generator is unavailable or fails.
     */
    public static function generateUniqueId(int $length = 13, string $separator = ''): string
    {
        // Determine the number of bytes needed (two hex chars per byte)
        $bytesNeeded = (int) ceil($length / 2);
        // Generate secure random bytes
        if (function_exists('random_bytes')) {
            try {
                $bytes = random_bytes($bytesNeeded);
            } catch (\Exception $e) {
                throw new RuntimeException('Failed to generate secure random bytes: ' . $e->getMessage(), 0, $e);
            }
        } elseif (function_exists('openssl_random_pseudo_bytes')) {
            $bytes = openssl_random_pseudo_bytes($bytesNeeded);
            if (!$bytes) {
                throw new RuntimeException('Failed to generate secure random bytes via OpenSSL.');
            }
        } else {
            throw new RuntimeException('No cryptographically secure random function available.');
        }

        // Convert to hex and trim to length
        $uniqueId = substr(bin2hex($bytes), 0, $length);

        // Insert separators if requested
        if ($separator !== '') {
            // Use StringUtils for chunk splitting
            $uniqueId = StringUtils::separateString($uniqueId, $separator);
        }

        return $uniqueId;
    }
}
