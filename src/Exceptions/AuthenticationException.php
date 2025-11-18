<?php

declare(strict_types=1);

namespace PhpLiteCore\Exceptions;

/**
 * Authentication Exception
 *
 * Thrown when authentication operations fail
 */
class AuthenticationException extends PhpLiteCoreException
{
    /**
     * Create exception for invalid credentials
     *
     * @param string $message
     * @return static
     */
    public static function invalidCredentials(string $message = 'Invalid credentials'): static
    {
        return new static($message, 2001);
    }

    /**
     * Create exception for unauthenticated access
     *
     * @param string $message
     * @return static
     */
    public static function unauthenticated(string $message = 'Unauthenticated'): static
    {
        return new static($message, 2002);
    }

    /**
     * Create exception for unauthorized access
     *
     * @param string $message
     * @return static
     */
    public static function unauthorized(string $message = 'Unauthorized'): static
    {
        return new static($message, 2003);
    }
}
