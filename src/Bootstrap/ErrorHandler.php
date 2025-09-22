<?php

declare(strict_types=1);

/**
 * A simple, environment-aware error and exception handler for phpLiteCore.
 */

set_exception_handler(function (\Throwable $e): void {
    http_response_code(500);

    if (defined('ENV') && ENV === 'development') {
        // In development, show the detailed, styled error page.
        $data = [
            'exception_class' => get_class($e),
            'message'         => $e->getMessage(),
            'file'            => $e->getFile(),
            'line'            => $e->getLine(),
            'trace'           => $e->getTraceAsString(),
        ];
        // In development, show the detailed, styled error page for the developer.
        render_error_view($data);
    } else {
        // In production, log the error and show a generic message.
        $logMessage = sprintf(
            "Uncaught Exception: %s: \"%s\" in %s:%d\nStack trace:\n%s",
            get_class($e), $e->getMessage(), $e->getFile(), $e->getLine(), $e->getTraceAsString()
        );
        error_log($logMessage);

        // In production, render the user-friendly 500 error page.
        render_http_error_page(
            500,
            'Internal Server Error',
            'We are sorry, but something went wrong on our end. Please try again later.'
        );
    }

    exit;
});


set_error_handler(
/**
 * @throws ErrorException
 */
    function (int $severity, string $message, string $file, int $line): bool {
        throw new \ErrorException($message, 0, $severity, $file, $line);
    }
);


/**
 * Renders the error view in a sandboxed function to prevent variable scope issues.
 *
 * @param array $data The data to extract for the view.
 * @return void
 */
function render_error_view(array $data): void
{
    // Extract the data into variables for the view file.
    extract($data);

    // Use output buffering to capture the view.
    ob_start();
    // We don't use the View class here to avoid potential circular dependencies
    // if the View class itself throws an error. This is a robust, self-contained way.
    require PHPLITECORE_ROOT . 'views/system/error.php';
    echo ob_get_clean();
}