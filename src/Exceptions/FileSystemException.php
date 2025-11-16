<?php

declare(strict_types=1);

namespace PhpLiteCore\Exceptions;

/**
 * File System Exception
 * 
 * Thrown when file system operations fail
 */
class FileSystemException extends PhpLiteCoreException
{
    /**
     * Create exception for file not found
     * 
     * @param string $path
     * @return static
     */
    public static function notFound(string $path): static
    {
        return new static("File not found: {$path}", 4001);
    }

    /**
     * Create exception for permission denied
     * 
     * @param string $path
     * @param string $operation
     * @return static
     */
    public static function permissionDenied(string $path, string $operation = 'access'): static
    {
        return new static("Permission denied to {$operation} file: {$path}", 4002);
    }

    /**
     * Create exception for read failure
     * 
     * @param string $path
     * @return static
     */
    public static function readFailed(string $path): static
    {
        return new static("Failed to read file: {$path}", 4003);
    }

    /**
     * Create exception for write failure
     * 
     * @param string $path
     * @return static
     */
    public static function writeFailed(string $path): static
    {
        return new static("Failed to write file: {$path}", 4004);
    }
}
