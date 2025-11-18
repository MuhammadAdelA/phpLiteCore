<?php

declare(strict_types=1);

namespace PhpLiteCore\Exceptions;

/**
 * Database Exception
 *
 * Thrown when database operations fail
 */
class DatabaseException extends PhpLiteCoreException
{
    /**
     * Create exception for connection failure
     *
     * @param string $message
     * @param array $context
     * @return static
     */
    public static function connectionFailed(string $message = 'Database connection failed', array $context = []): static
    {
        return new static($message, 1001, $context);
    }

    /**
     * Create exception for query failure
     *
     * @param string $message
     * @param array $context
     * @return static
     */
    public static function queryFailed(string $message = 'Database query failed', array $context = []): static
    {
        return new static($message, 1002, $context);
    }

    /**
     * Create exception for transaction failure
     *
     * @param string $message
     * @param array $context
     * @return static
     */
    public static function transactionFailed(string $message = 'Database transaction failed', array $context = []): static
    {
        return new static($message, 1003, $context);
    }
}
