<?php

declare(strict_types=1);

namespace PhpLiteCore\Http;

use JetBrains\PhpStorm\NoReturn;

/**
 * HTTP response helper methods.
 */
class Response
{
    /**
     * Redirect to a given location with a specific HTTP status code.
     *
     * @param string $location The target URL or path.
     * @param int    $status   HTTP status code for redirection (3xx).
     * @return void
     */
    #[NoReturn] public static function redirect(string $location = '/', int $status = 302): void
    {
        // Sanitize location (basic)
        $safeLocation = filter_var($location, FILTER_SANITIZE_URL);

        // Send redirect header with explicit status code
        header(sprintf('Location: %s', $safeLocation), true, $status);

        // Terminate execution
        exit;
    }

    /**
     * Send an HTTP status code and terminate execution.
     *
     * @param int    $code    HTTP status code to send.
     * @param string $message Optional message body.
     * @return void
     */
    #[NoReturn] public static function sendStatus(int $code, string $message = ''): void
    {
        // Send the status header
        header(sprintf('HTTP/1.1 %d %s', $code, self::getStatusText($code)), true, $code);

        // Optionally output a custom message
        if ($message !== '') {
            echo $message;
        }

        exit;
    }

    /**
     * Get standard reason phrase for a status code.
     *
     * @param int $code
     * @return string
     */
    private static function getStatusText(int $code): string
    {
        $texts = [
            404 => 'Not Found'
        ];
        return $texts[$code] ?? '';
    }

    /**
     * Shortcut for 404 Not Found.
     *
     * @param string $message Optional message body.
     * @return void
     */
    #[NoReturn] public static function notFound(string $message = ''): void
    {
        self::sendStatus(404, $message);
    }
}
