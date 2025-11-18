<?php

// File: src/Utils/FileUtils.php
declare(strict_types=1);

namespace PhpLiteCore\Utils;

use RuntimeException;

class FileUtils
{
    /**
     * Check if a file exists locally (on the file system) or remotely (via URL).
     *
     * @param string $path   Filesystem path or remote URL (include protocol).
     * @param bool   $remote Whether to perform a remote existence check.
     * @return bool          True if the file exists, false otherwise.
     */
    public static function exists(string $path, bool $remote = false): bool
    {
        if ($remote) {
            $headers = @get_headers($path);
            if ($headers === false) {
                return false;
            }

            return (bool) preg_match('#^HTTP/\d+\.\d+\s+200#', $headers[0]);
        }

        $normalized = str_replace(PHPLITECORE_ROOT, '', $path);
        $filePath = rtrim(PHPLITECORE_ROOT, DIRECTORY_SEPARATOR)
            . DIRECTORY_SEPARATOR
            . ltrim($normalized, DIRECTORY_SEPARATOR);

        return file_exists($filePath);
    }

    /**
     * Ensure that one or more directories exist and are writable.
     *
     * @param string|string[] $paths Directory path or array of paths to create.
     * @return string[] List of paths that failed to be created or are not writable.
     */
    public static function ensureDirectories(string|array $paths): array
    {
        $paths = is_array($paths) ? $paths : [$paths];
        $errors = [];

        foreach ($paths as $path) {
            $fullPath = self::resolveDirectoryPath($path, true);

            if (! is_dir($fullPath) && ! @mkdir($fullPath, 0755, true)) {
                $errors[] = $path;
                error_log("[FileUtils] Failed to create directory: {$fullPath}");

                continue;
            }

            if (! is_writable($fullPath)) {
                $errors[] = $path;
                error_log("[FileUtils] Directory not writable: {$fullPath}");
            }
        }

        return $errors;
    }

    /**
     * Copy a file, or recursively copy a folder and its contents.
     *
     * @param string $source Source file or directory path.
     * @param string $dest   Destination file or directory path.
     * @return void
     * @throws RuntimeException If the copy fails at any point.
     */
    public static function copy(string $source, string $dest): void
    {
        if (! is_readable($source)) {
            $message = "Source not readable: {$source}";
            error_log("[FileUtils] {$message}");

            throw new RuntimeException($message);
        }

        // Handle symlinks
        if (is_link($source)) {
            $linkTarget = readlink($source);
            if (@symlink($linkTarget, $dest) === false) {
                $message = "Failed to create symlink from {$source} to {$dest}";
                error_log("[FileUtils] {$message}");

                throw new RuntimeException($message);
            }

            return;
        }

        // Copy single file
        if (is_file($source)) {
            if (! @copy($source, $dest)) {
                $message = "Failed to copy file from {$source} to {$dest}";
                error_log("[FileUtils] {$message}");

                throw new RuntimeException($message);
            }

            return;
        }

        // Recursively copy directory
        if (is_dir($source)) {
            if (! is_dir($dest) && ! @mkdir($dest, 0755, true)) {
                $message = "Failed to create directory: {$dest}";
                error_log("[FileUtils] {$message}");

                throw new RuntimeException($message);
            }
            $items = scandir($source);
            foreach ($items as $item) {
                if ($item === '.' || $item === '..') {
                    continue;
                }
                self::copy(
                    rtrim($source, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $item,
                    rtrim($dest, DIRECTORY_SEPARATOR)   . DIRECTORY_SEPARATOR . $item
                );
            }

            return;
        }

        $message = "Source not a file or directory: {$source}";
        error_log("[FileUtils] {$message}");

        throw new RuntimeException($message);
    }

    /**
     * Remove all files within a directory, logging errors and throwing exceptions on failure.
     *
     * @param string $dirPath Path to the directory to clean.
     * @return void
     * @throws RuntimeException If the directory does not exist or a file cannot be deleted.
     */
    public static function cleanDirectory(string $dirPath): void
    {
        $fullPath = self::resolveDirectoryPath($dirPath);
        $items = glob($fullPath . '*');

        foreach ($items as $item) {
            if (is_file($item)) {
                if (! @unlink($item)) {
                    $message = "Failed to remove file: {$item}";
                    error_log("[FileUtils] {$message}");

                    throw new RuntimeException($message);
                }
                error_log("[FileUtils] Successfully removed file: {$item}");
            }
        }
    }

    /**
     * Recursively delete a directory and its contents, logging errors and throwing exceptions on failure.
     *
     * @param string $dirPath Path to the directory to delete.
     * @return void
     * @throws RuntimeException If deletion of any file or directory fails.
     */
    public static function deleteDirectory(string $dirPath): void
    {
        $fullPath = self::resolveDirectoryPath($dirPath);
        $items = glob($fullPath . '*', GLOB_MARK);

        foreach ($items as $item) {
            if (is_dir($item)) {
                self::deleteDirectory($item);
            } else {
                if (! @unlink($item)) {
                    $message = "Failed to delete file: {$item}";
                    error_log("[FileUtils] {$message}");

                    throw new RuntimeException($message);
                }
            }
        }

        if (! @rmdir($fullPath)) {
            $message = "Failed to remove directory: {$fullPath}";
            error_log("[FileUtils] {$message}");

            throw new RuntimeException($message);
        }

        error_log("[FileUtils] Successfully deleted directory: {$fullPath}");
    }

    /**
     * Resolve and validate a directory path against PHPLITECORE_ROOT, ensuring it exists.
     *
     * @param string $dirPath Directory path to resolve.
     * @return string Full filesystem path with trailing separator.
     * @throws RuntimeException If directory does not exist.
     */
    public static function resolveDirectoryPath(string $dirPath, bool $allowCreate = false): string
    {
        $normalized = rtrim(str_replace(PHPLITECORE_ROOT, '', $dirPath), DIRECTORY_SEPARATOR);
        $fullPath = rtrim(PHPLITECORE_ROOT, DIRECTORY_SEPARATOR)
            . DIRECTORY_SEPARATOR
            . ltrim($normalized, DIRECTORY_SEPARATOR)
            . DIRECTORY_SEPARATOR;

        if ($allowCreate) {
            return $fullPath;
        }

        if (! is_dir($fullPath)) {
            $message = "Directory does not exist: {$fullPath}";
            error_log("[FileUtils] {$message}");

            throw new RuntimeException($message);
        }

        return $fullPath;
    }

    /**
     * Canonicalize a given path by resolving "." and ".." segments, keeping it relative if input is relative.
     *
     * @param string $path The input relative or absolute path.
     * @return string      The canonicalized path.
     */
    public static function canonicalizePath(string $path): string
    {
        // Normalize separators to forward slash for resolution
        $uniform = str_replace(['/', '\\'], '/', $path);
        $parts = explode('/', $uniform);
        $resolved = [];

        foreach ($parts as $part) {
            if ($part === '' || $part === '.') {
                continue;
            }
            if ($part === '..') {
                if (! empty($resolved) && end($resolved) !== '..') {
                    array_pop($resolved);
                } else {
                    $resolved[] = '..';
                }
            } else {
                $resolved[] = $part;
            }
        }

        return implode(DIRECTORY_SEPARATOR, $resolved);
    }
}
