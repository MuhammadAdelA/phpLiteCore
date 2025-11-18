<?php

// ملف: src/Utils/AssetUtils.php
declare(strict_types=1);

namespace PhpLiteCore\Utils;

class AssetUtils
{
    /**
     * Append version query param to an asset URL based on its filesystem modification time.
     *
     * @param string $path        Relative URL path (e.g. '/css/style.css').
     * @param string $urlBase     Base URL or constant name for assets (e.g., ASSET_BASE_URL).
     * @param string $pathBase    Filesystem base path or constant name (e.g., ASSET_BASE_PATH).
     * @return string             URL with appended version query string.
     */
    public static function withVersion(string $path, string $urlBase = 'ASSET_BASE_URL', string $pathBase = 'ASSET_BASE_PATH'): string
    {
        // Resolve a base URL and filesystem path from constants or as given
        $baseUrl = defined($urlBase) ? constant($urlBase) : $urlBase;
        $basePath = defined($pathBase) ? constant($pathBase) : $pathBase;

        // Normalize separators
        $fileSystemPath = rtrim($basePath, DIRECTORY_SEPARATOR)
            . DIRECTORY_SEPARATOR
            . ltrim($path, '/\\');

        $webPath = rtrim($baseUrl, '/') . '/' . ltrim($path, '/');

        // If the file does not exist or filemtime fails, return the web path unchanged
        if (! file_exists($fileSystemPath) || ($mtime = filemtime($fileSystemPath)) === false) {
            return $webPath;
        }

        // Append the version as a 'v' query parameter
        return $webPath . '?v=' . $mtime;
    }

    /**
     * Get the current script file name.
     *
     * @param bool $withExtension Include file extension if true.
     * @return string The script file name without or with its extension.
     */
    public static function getScriptName(bool $withExtension = false): string
    {
        // Use SCRIPT_NAME or fallback to PHP_SELF
        $filePath = $_SERVER['SCRIPT_NAME'] ?? ($_SERVER['PHP_SELF'] ?? '');
        $fileName = basename($filePath);

        return $withExtension
            ? $fileName
            : pathinfo($fileName, PATHINFO_FILENAME);
    }

}
