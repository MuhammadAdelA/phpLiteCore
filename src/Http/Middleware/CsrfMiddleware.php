<?php

declare(strict_types=1);

namespace PhpLiteCore\Http\Middleware;

use PhpLiteCore\Http\Response;
use PhpLiteCore\Session\Session;

/**
 * CSRF Protection Middleware
 *
 * Provides Cross-Site Request Forgery (CSRF) protection by:
 * - Lazily generating a session-backed token
 * - Validating incoming tokens from form fields or headers
 * - Rejecting non-GET requests with missing/invalid tokens (HTTP 403)
 */
class CsrfMiddleware
{
    /**
     * The session key used to store the CSRF token.
     */
    private const TOKEN_KEY = '_csrf_token';

    /**
     * The form field name expected to contain the CSRF token.
     */
    private const FORM_FIELD = '_token';

    /**
     * The HTTP header name expected to contain the CSRF token.
     */
    private const HEADER_NAME = 'HTTP_X_CSRF_TOKEN';

    /**
     * The Session instance for storing and retrieving the token.
     */
    private Session $session;

    /**
     * Constructor
     *
     * @param Session $session The session instance
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    /**
     * Handle the incoming request.
     *
     * For GET requests: no-op (allows request to proceed)
     * For non-GET requests: validates CSRF token, sends 403 if invalid
     *
     * @param string $method The HTTP request method
     * @return void
     */
    public function handle(string $method): void
    {
        // Skip CSRF validation for GET requests
        if (strtoupper($method) === 'GET') {
            return;
        }

        // For non-GET requests, validate the token
        if (! $this->validateToken()) {
            Response::forbidden('CSRF token mismatch');
        }
    }

    /**
     * Get or generate the CSRF token.
     *
     * Lazily generates a token if one doesn't exist in the session.
     *
     * @return string The CSRF token
     */
    public static function token(): string
    {
        // We need a session instance, but this is a static method for convenience.
        // We'll use the global session from the Application instance if available,
        // or create a temporary one. In practice, the session should already be started
        // by the Application bootstrap.
        $session = self::getSession();

        if (! $session->has(self::TOKEN_KEY)) {
            $token = bin2hex(random_bytes(32));
            $session->set(self::TOKEN_KEY, $token);
        }

        return $session->get(self::TOKEN_KEY);
    }

    /**
     * Validate the CSRF token from the request.
     *
     * Checks for the token in:
     * 1. POST data (form field: _token)
     * 2. HTTP header (X-CSRF-TOKEN)
     *
     * @return bool True if the token is valid, false otherwise
     */
    private function validateToken(): bool
    {
        $expectedToken = $this->session->get(self::TOKEN_KEY);

        // If no token exists in session yet, reject the request
        if (! $expectedToken) {
            return false;
        }

        // Check for token in POST data
        $submittedToken = $_POST[self::FORM_FIELD] ?? null;

        // If not in POST, check the HTTP header
        if (! $submittedToken) {
            $submittedToken = $_SERVER[self::HEADER_NAME] ?? null;
        }

        // Token must be present and match
        if (! $submittedToken) {
            return false;
        }

        // Use hash_equals to prevent timing attacks
        return hash_equals($expectedToken, $submittedToken);
    }

    /**
     * Get the session instance.
     *
     * This helper method retrieves the session from the Application singleton.
     * If the Application is not available, it creates a new Session instance.
     *
     * @return Session
     */
    private static function getSession(): Session
    {
        // Try to get the session from the Application singleton
        if (class_exists('\PhpLiteCore\Bootstrap\Application')) {
            try {
                $app = \PhpLiteCore\Bootstrap\Application::getInstance();
                // Check if session property is initialized
                if (isset($app->session)) {
                    return $app->session;
                }
            } catch (\Throwable $e) {
                // If Application is not initialized properly, fall back to creating a new Session
            }
        }

        // Fallback: create a new Session instance
        return new Session();
    }
}
