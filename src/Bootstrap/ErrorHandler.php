<?php

declare(strict_types=1);

// Import PHPMailer and Translator classes at the top of the file
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;
use PhpLiteCore\Lang\Translator; // Import the Translator class

/**
 * A simple, environment-aware error and exception handler for phpLiteCore.
 * Logs errors, notifies developers in production via SMTP, and displays appropriate error pages.
 */

set_exception_handler(function (Throwable $e): void {
    // 1. Log the detailed error to a file regardless of the environment.
    $logMessage = sprintf(
        "Uncaught Exception: %s: \"%s\" in %s:%d\nStack trace:\n%s",
        get_class($e), $e->getMessage(), $e->getFile(), $e->getLine(), $e->getTraceAsString()
    );
    // Use error_log() for standard PHP error logging.
    error_log($logMessage);

    // Check the application environment constant.
    if (defined('ENV') && ENV === 'development') {
        // 2. In DEVELOPMENT environment: Show the detailed error page for the developer.
        http_response_code(500); // Set appropriate HTTP status code.

        $message = $e->getMessage();

        // Provide a more helpful message for the common "object as array" error.
        if ($e instanceof Error && str_contains($message, 'Cannot use object of type')) {
            preg_match("/Cannot use object of type '([^']+)' as array/", $message, $matches);
            $className = $matches[1] ?? 'Object';
            $message = "Error: Attempted to access an object of type '{$className}' as an array. Did you mean to use the object operator '->' instead of array brackets '[]'?";
        }

        // Prepare data for the detailed error view.
        $data = [
            'exception_class' => get_class($e),
            'message'         => $message, // Use the potentially modified message
            'file'            => $e->getFile(),
            'line'            => $e->getLine(),
            'trace'           => $e->getTraceAsString(),
        ];

        // Render the detailed error view.
        render_error_view($data);

    } else {
        // 3. In PRODUCTION environment: Notify the developer via SMTP and show a generic, translated error page.

        // --- Developer Notification Logic ---
        $developerEmail = $_ENV['DEVELOPER_EMAIL'] ?? null;

        // Proceed only if a developer email is configured in .env.
        if (!empty($developerEmail)) {
            $mail = new PHPMailer(true); // Enable exceptions for PHPMailer.

            try {
                // Configure PHPMailer to use SMTP using settings from .env.
                $mail->isSMTP();
                $mail->Host       = $_ENV['SMTP_HOST'] ?? 'localhost';
                $mail->SMTPAuth   = true;
                $mail->Username   = $_ENV['SMTP_USERNAME'] ?? '';
                $mail->Password   = $_ENV['SMTP_PASSWORD'] ?? '';
                $mail->SMTPSecure = $_ENV['SMTP_ENCRYPTION'] ?? PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = (int)($_ENV['SMTP_PORT'] ?? 587);
                $mail->CharSet    = 'UTF-8'; // Ensure correct encoding for email body.

                // Set sender and recipient.
                $mail->setFrom($_ENV['SMTP_FROM_ADDRESS'] ?? 'noreply@example.com', $_ENV['SMTP_FROM_NAME'] ?? 'Error Reporter');
                $mail->addAddress($developerEmail); // Add the developer's email address.

                // Set email content format and subject.
                $mail->isHTML(true);
                $mail->Subject = "Critical Error on phpLiteCore Application";

                // Construct the HTML email body with detailed error information.
                $body = "<h1>🚨 Application Error</h1><p>A critical error occurred that requires your attention.</p>";
                $body .= "<h2>Error Details:</h2><ul>";
                $body .= "<li><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</li>";
                $body .= "<li><strong>Class:</strong> " . get_class($e) . "</li>";
                $body .= "<li><strong>File:</strong> " . $e->getFile() . " in line " . $e->getLine() . "</li>";
                $body .= "</ul>";
                $body .= "<h2>Request Info:</h2><ul>";
                $body .= "<li><strong>URL:</strong> " . ($_SERVER['REQUEST_URI'] ?? 'N/A') . "</li>";
                $body .= "<li><strong>Method:</strong> " . ($_SERVER['REQUEST_METHOD'] ?? 'N/A') . "</li>";
                // You could add IP address, User Agent, etc., here if needed.
                $body .= "</ul>";
                $body .= "<h2>Stack Trace:</h2><pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";

                $mail->Body = $body;

                // Send the email.
                $mail->send();
            } catch (PHPMailerException $mailException) {
                // If email sending fails, log the mailer error.
                // The original application error is already logged, so no critical info is lost.
                error_log("Mailer Error: {$mailException->getMessage()}");
            }
        }
        // --- END Developer Notification Logic ---

        // --- Translation Logic for User Message ---
        // Instantiate the translator using the current language constant (LANG).
        // Fallback to default language if LANG is not defined.
        $currentLang = defined('LANG') ? LANG : ($_ENV['DEFAULT_LANG'] ?? 'en');
        $translator = new Translator($currentLang);

        // Get translated strings for the user-facing error page.
        $errorTitle = $translator->get('messages.error_500_title');
        $errorMessage = $translator->get('messages.error_500_message');
        $homeLinkText = $translator->get('messages.home_link_text'); // Get the translated home link text
        // --- END Translation Logic ---

        // Finally, show the generic, translated error page to the user.
        render_http_error_page(
            500, // Set the 500 status code.
            $errorTitle,
            $errorMessage,
            $homeLinkText // Pass the translated text to the render function
        );
    }
});

/**
 * Set a custom error handler to convert traditional PHP errors (Warnings, Notices)
 * into ErrorExceptions, so they can be caught by the exception handler.
 */
set_error_handler(
/**
 * @throws ErrorException
 */
    function (int $severity, string $message, string $file, int $line): bool {
        // Throw an ErrorException which will be caught by set_exception_handler.
        throw new ErrorException($message, 0, $severity, $file, $line);
    }
);

/**
 * Renders the detailed development error view (`views/system/error.php`)
 * in a sandboxed function scope to prevent variable conflicts.
 *
 * @param array $data The data to extract for the view (exception_class, message, file, line, trace).
 * @return void
 */
function render_error_view(array $data): void
{
    // Extract the data array into local variables accessible by the required file.
    extract($data);
    // Start output buffering.
    ob_start();
    // Include the view file.
    require PHPLITECORE_ROOT . 'views/system/error.php';
    // Get the buffered content and clean the buffer.
    echo ob_get_clean();
}

/**
 * Renders the generic HTTP error page (`views/system/http_error.php`).
 * This function is defined in src/Helpers/Functions.php but included here
 * conceptually as part of the error handling flow.
 * Note: Ensure Functions.php is loaded *before* ErrorHandler.php in composer.json.
 *
 * function render_http_error_page(int $error_code, string $error_title, string $error_message, string $homeLinkText): void { ... }
 */