<?php

declare(strict_types=1);

namespace PhpLiteCore\Http\Middleware;

use PhpLiteCore\Http\Response;
use PhpLiteCore\Session\Session;

/**
 * Rate Limiting Middleware
 * 
 * Provides basic rate limiting to prevent abuse by:
 * - Tracking request counts per IP address
 * - Using session storage for simplicity
 * - Blocking requests that exceed the configured limit
 */
class RateLimitMiddleware
{
    /**
     * The session key prefix for storing rate limit data
     */
    private const SESSION_PREFIX = '_rate_limit_';

    /**
     * The Session instance for storing rate limit data
     */
    private Session $session;

    /**
     * Maximum number of requests allowed in the time window
     */
    private int $maxAttempts;

    /**
     * Time window in seconds
     */
    private int $decaySeconds;

    /**
     * Constructor
     * 
     * @param Session $session The session instance
     * @param int $maxAttempts Maximum number of requests allowed (default: 60)
     * @param int $decaySeconds Time window in seconds (default: 60)
     */
    public function __construct(Session $session, int $maxAttempts = 60, int $decaySeconds = 60)
    {
        $this->session = $session;
        $this->maxAttempts = $maxAttempts;
        $this->decaySeconds = $decaySeconds;
    }

    /**
     * Handle the incoming request
     * 
     * @param string $key Unique key for rate limiting (e.g., IP address, user ID)
     * @return void
     */
    public function handle(string $key): void
    {
        $sessionKey = self::SESSION_PREFIX . $this->sanitizeKey($key);
        $data = $this->session->get($sessionKey, []);

        $now = time();
        
        // Initialize or reset if expired
        if (!isset($data['reset_at']) || $data['reset_at'] <= $now) {
            $data = [
                'attempts' => 0,
                'reset_at' => $now + $this->decaySeconds,
            ];
        }

        // Increment attempts
        $data['attempts']++;
        
        // Store updated data
        $this->session->set($sessionKey, $data);

        // Check if limit exceeded
        if ($data['attempts'] > $this->maxAttempts) {
            $retryAfter = $data['reset_at'] - $now;
            Response::tooManyRequests(
                "Rate limit exceeded. Try again in {$retryAfter} seconds.",
                $retryAfter
            );
        }
    }

    /**
     * Get the current number of attempts for a key
     * 
     * @param string $key The rate limit key
     * @return int Number of attempts
     */
    public function getAttempts(string $key): int
    {
        $sessionKey = self::SESSION_PREFIX . $this->sanitizeKey($key);
        $data = $this->session->get($sessionKey, []);

        $now = time();
        
        // Return 0 if expired or not set
        if (!isset($data['reset_at']) || $data['reset_at'] <= $now) {
            return 0;
        }

        return $data['attempts'] ?? 0;
    }

    /**
     * Get the number of remaining attempts for a key
     * 
     * @param string $key The rate limit key
     * @return int Number of remaining attempts
     */
    public function getRemainingAttempts(string $key): int
    {
        $attempts = $this->getAttempts($key);
        
        return max(0, $this->maxAttempts - $attempts);
    }

    /**
     * Clear rate limit data for a key
     * 
     * @param string $key The rate limit key
     * @return void
     */
    public function clear(string $key): void
    {
        $sessionKey = self::SESSION_PREFIX . $this->sanitizeKey($key);
        $this->session->remove($sessionKey);
    }

    /**
     * Sanitize the key to prevent session key injection
     * 
     * @param string $key The key to sanitize
     * @return string Sanitized key
     */
    private function sanitizeKey(string $key): string
    {
        return preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $key);
    }

    /**
     * Get the client's IP address for rate limiting
     * 
     * @return string The client's IP address
     */
    public static function getClientIp(): string
    {
        // Check various headers for the real IP
        $headers = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR',
        ];

        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = $_SERVER[$header];
                
                // Handle multiple IPs in X-Forwarded-For
                if (str_contains($ip, ',')) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                
                // Validate IP
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return '0.0.0.0';
    }
}
