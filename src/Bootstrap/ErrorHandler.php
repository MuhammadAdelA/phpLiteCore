<?php

declare(strict_types=1);

// Register a global exception handler
set_exception_handler(function (\Throwable $e): void {
    // Prefix the message for distinction
    $message = '<b>phpLiteCore said</b>: ' . $e->getMessage();

    // Send HTTP 500 response
    http_response_code(500);

    // Display the prefixed message
    echo $message;

    // Stop execution
    exit;
});

// Convert PHP errors to ErrorException so they're caught by the exception handler

set_error_handler(
    /**
     * @throws ErrorException
     */
    function (int $severity, string $message, string $file, int $line): bool {
    throw new \ErrorException($message, 0, $severity, $file, $line);
});