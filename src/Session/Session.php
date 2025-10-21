<?php
declare(strict_types=1);

namespace PhpLiteCore\Session;

/**
 * Manages PHP sessions with basic operations and flash messaging.
 * Wraps PHP's native $_SESSION superglobal.
 */
class Session
{
    /**
     * Session constructor.
     * Starts the session if it hasn't been started already.
     * Configures session cookie parameters for better security.
     */
    public function __construct()
    {
        $this->start();
    }

    /**
     * Starts the session safely.
     * Checks if headers have already been sent to avoid errors.
     * Sets secure cookie parameters based on .env settings or defaults.
     *
     * @return void
     */
    public function start(): void
    {
        // Only start if no session exists and headers haven't been sent
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            // Configure session cookie parameters for enhanced security
            session_set_cookie_params([
                // Use SESSION_LIFETIME from .env, default to 1 hour (3600 seconds)
                'lifetime' => (int)($_ENV['SESSION_LIFETIME'] ?? 3600),
                'path'     => '/',
                // Use SESSION_DOMAIN from .env, default to current host
                'domain'   => $_ENV['SESSION_DOMAIN'] ?? $_SERVER['HTTP_HOST'] ?? '',
                // Set 'secure' flag only if connection is HTTPS
                'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
                // Prevent JavaScript access to the session cookie
                'httponly' => true,
                // Mitigate CSRF attacks; 'Lax' is a good default
                'samesite' => 'Lax'
            ]);
            // Start the PHP session
            session_start();
        } elseif (session_status() === PHP_SESSION_NONE && headers_sent()) {
            // Log an error if session couldn't start because headers were already sent
            error_log("Session: Could not start session, headers already sent.");
            // Optionally, throw an exception in development environment for easier debugging
            // if (defined('ENV') && ENV === 'development') {
            //     throw new \RuntimeException("Session: Could not start session, headers already sent.");
            // }
        }
        // If session is already active (PHP_SESSION_ACTIVE), do nothing.
    }

    /**
     * Set a value in the session.
     *
     * @param string $key The session key.
     * @param mixed $value The value to store.
     * @return void
     */
    public function set(string $key, mixed $value): void
    {
        // Ensure session is active before writing
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION[$key] = $value;
        } else {
            error_log("Session: Attempted to set key '{$key}' but session is not active.");
        }
    }

    /**
     * Get a value from the session.
     *
     * @param string $key The session key.
     * @param mixed|null $default The default value to return if the key doesn't exist.
     * @return mixed The session value or the default value.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        // Check if session is active and the key exists
        if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION[$key])) {
            return $_SESSION[$key];
        }
        // Return default value if session not active or key not found
        return $default;
    }

    /**
     * Check if a key exists in the session.
     *
     * @param string $key The session key.
     * @return bool True if the key exists and session is active, false otherwise.
     */
    public function has(string $key): bool
    {
        return session_status() === PHP_SESSION_ACTIVE && isset($_SESSION[$key]);
    }

    /**
     * Remove a value from the session.
     *
     * @param string $key The session key to remove.
     * @return void
     */
    public function remove(string $key): void
    {
        // Ensure session is active before unsetting
        if (session_status() === PHP_SESSION_ACTIVE) {
            unset($_SESSION[$key]);
        }
    }

    /**
     * Destroy the entire session, unset variables, and delete the cookie.
     *
     * @return void
     */
    public function destroy(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            // 1. Unset all session variables
            $_SESSION = [];

            // 2. Delete the session cookie
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                // Set cookie expiration to the past to trigger deletion
                setcookie(
                    session_name(), // Get session name (e.g., PHPSESSID)
                    '',             // Empty value
                    time() - 42000, // Expiry time in the past
                    $params["path"],
                    $params["domain"],
                    $params["secure"],
                    $params["httponly"]
                );
            }

            // 3. Destroy the session on the server
            session_destroy();
        }
    }

    /**
     * Regenerate the session ID to prevent session fixation attacks.
     * Recommended after login or privilege level changes.
     *
     * @param bool $deleteOldSession Whether to delete the old session file associated with the previous ID. Defaults to true.
     * @return void
     */
    public function regenerate(bool $deleteOldSession = true): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            // Regenerate the session ID, optionally deleting the old session data
            session_regenerate_id($deleteOldSession);
        }
    }

    /**
     * Set a flash message (a message that persists only until the next request).
     * Useful for showing success/error messages after redirects.
     *
     * @param string $key The key for the flash message (e.g., 'success', 'error', 'info').
     * @param string $message The message content.
     * @return void
     */
    public function setFlash(string $key, string $message): void
    {
        // Ensure session is active
        if (session_status() === PHP_SESSION_ACTIVE) {
            // Store flash messages under a reserved '_flash' key to avoid conflicts
            $_SESSION['_flash'][$key] = $message;
        } else {
            error_log("Session: Attempted to set flash message '{$key}' but session is not active.");
        }
    }

    /**
     * Get a flash message and immediately remove it from the session.
     * Returns the default value if the key doesn't exist.
     *
     * @param string $key The key for the flash message.
     * @param string|null $default Default value if the message doesn't exist.
     * @return string|null The flash message or the default value.
     */
    public function flash(string $key, ?string $default = null): ?string
    {
        // Check if session is active and the flash message exists
        if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['_flash'][$key])) {
            // Retrieve the message
            $message = $_SESSION['_flash'][$key];
            // Remove the message immediately after retrieval
            unset($_SESSION['_flash'][$key]);
            return $message;
        }
        // Return default if session not active or message not found
        return $default;
    }

    /**
     * Check if a flash message exists for a given key without removing it.
     *
     * @param string $key The flash message key.
     * @return bool True if the flash message exists and session is active, false otherwise.
     */
    public function hasFlash(string $key): bool
    {
        return session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['_flash'][$key]);
    }
}