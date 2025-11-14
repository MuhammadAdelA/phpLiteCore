<?php

declare(strict_types=1);

namespace PhpLiteCore\Exceptions;

/**
 * Configuration Exception
 * 
 * Thrown when configuration is missing or invalid
 */
class ConfigurationException extends PhpLiteCoreException
{
    /**
     * Create exception for missing configuration
     * 
     * @param string $key
     * @return static
     */
    public static function missing(string $key): static
    {
        return new static("Configuration key '{$key}' is missing", 3001);
    }

    /**
     * Create exception for invalid configuration
     * 
     * @param string $key
     * @param string $reason
     * @return static
     */
    public static function invalid(string $key, string $reason = ''): static
    {
        $message = "Configuration key '{$key}' is invalid";
        
        if ($reason) {
            $message .= ": {$reason}";
        }

        return new static($message, 3002);
    }
}
