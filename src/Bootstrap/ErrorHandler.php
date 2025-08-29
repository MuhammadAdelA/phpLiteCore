<?php

declare(strict_types=1);

/**
 * A simple, environment-aware error and exception handler for phpLiteCore.
 */

// Set the global exception handler.
set_exception_handler(function (\Throwable $e): void {
    // Set a generic 500 server error status code.
    http_response_code(500);

    // Behavior depends on the environment.
    if (defined('ENV') && ENV === 'development') {
        // In development, show detailed error information.
        echo '<style>body { font-family: sans-serif; padding: 1em; } .stack-trace { white-space: pre-wrap; }</style>';
        echo '<h1>Uncaught Exception</h1>';
        echo '<h3>' . htmlspecialchars(get_class($e)) . '</h3>';
        echo '<p><b>Message:</b> ' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '<p><b>File:</b> ' . htmlspecialchars($e->getFile()) . ' line ' . $e->getLine() . '</p>';
        echo '<h3>Stack Trace:</h3>';
        echo '<pre class="stack-trace">' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    } else {
        // In production, log the detailed error and show a generic message.
        $logMessage = sprintf(
            "Uncaught Exception: %s: \"%s\" in %s:%d\nStack trace:\n%s",
            get_class($e),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString()
        );
        error_log($logMessage);

        // Display a simple, user-friendly error message.
        echo '<h1>An error occurred</h1>';
        echo '<p>We are sorry, but something went wrong. Please try again later.</p>';
    }

    // Stop execution.
    exit;
});

// Set the error handler to convert all errors to ErrorException.
set_error_handler(
/**
 * @throws ErrorException
 */
    function (int $severity, string $message, string $file, int $line): bool {
        // This function will throw an exception, which will then be caught by our set_exception_handler.
        throw new \ErrorException($message, 0, $severity, $file, $line);
    }
);