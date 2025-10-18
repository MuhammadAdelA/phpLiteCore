<?php

declare(strict_types=1);

namespace PhpLiteCore\Http;

use PhpLiteCore\Lang\Translator; // Import the Translator class
use ReturnTypeWillChange; // For compatibility with PHP 8.1+ regarding ArrayAccess implementation
use JetBrains\PhpStorm\NoReturn; // For static analysis indicating termination

/**
 * Represents an HTTP response.
 * Provides methods for setting headers, status codes, cookies, and content.
 * Includes helpers for common response types like redirects and JSON.
 */
class Response // Add ArrayAccess if you need header manipulation like $response['Content-Type'] = '...'
{
    /** @var int The HTTP status code. */
    protected int $statusCode = 200;

    /** @var array The HTTP headers. */
    protected array $headers = [];

    /** @var string The response body content. */
    protected string $content = '';

    /**
     * Sets the HTTP status code for the response.
     *
     * @param int $code The HTTP status code (e.g., 200, 404, 500).
     * @return static
     */
    public function setStatusCode(int $code): static
    {
        $this->statusCode = $code;
        return $this;
    }

    /**
     * Adds or updates an HTTP header.
     *
     * @param string $key The header name (e.g., 'Content-Type').
     * @param string $value The header value (e.g., 'application/json').
     * @return static
     */
    public function setHeader(string $key, string $value): static
    {
        $this->headers[$key] = $value;
        return $this;
    }

    /**
     * Sets the response body content.
     *
     * @param string $content The content to send.
     * @return static
     */
    public function setContent(string $content): static
    {
        $this->content = $content;
        return $this;
    }

    /**
     * Sends the HTTP headers and content to the browser.
     * This method should typically be called only once at the end of the request lifecycle.
     *
     * @return void
     */
    public function send(): void
    {
        // Send status code header.
        http_response_code($this->statusCode);

        // Send all other headers.
        foreach ($this->headers as $key => $value) {
            header("{$key}: {$value}");
        }

        // Echo the response body.
        echo $this->content;
    }

    /**
     * Static helper to create a redirect response.
     * Terminates script execution after sending headers.
     *
     * @param string $url The URL to redirect to.
     * @param int $statusCode The HTTP status code for the redirect (default: 302 Found).
     * @return void
     */
    #[NoReturn] public static function redirect(string $url, int $statusCode = 302): void
    {
        header('Location: ' . $url, true, $statusCode);
        exit;
    }

    /**
     * Static helper to create a JSON response.
     * Sets the appropriate Content-Type header and encodes the data.
     *
     * @param mixed $data The data to encode as JSON.
     * @param int $statusCode The HTTP status code (default: 200 OK).
     * @param array $headers Additional headers to send.
     * @return void
     */
    public static function json(mixed $data, int $statusCode = 200, array $headers = []): void
    {
        $response = new static();
        $response->setStatusCode($statusCode);
        $response->setHeader('Content-Type', 'application/json');
        // Merge additional headers
        foreach ($headers as $key => $value) {
            $response->setHeader($key, $value);
        }
        $response->setContent(json_encode($data));
        $response->send();
    }

    /**
     * Static helper for 404 Not Found response.
     * Renders the default, translated 404 error page using the helper function.
     * Terminates script execution.
     *
     * @param string $message Optional custom message key (will be translated if key exists).
     * If the key doesn't exist, the provided string is used as fallback.
     * @return void
     */
    #[NoReturn] public static function notFound(string $message = ''): void
    {
        // Instantiate the translator using the current language constant (LANG).
        // Fallback to default language if LANG is not defined.
        $currentLang = defined('LANG') ? LANG : ($_ENV['DEFAULT_LANG'] ?? 'en');
        $translator = new Translator($currentLang);

        // Get standard translated strings for 404.
        $errorTitle = $translator->get('messages.error_404_title');
        $defaultMessage = $translator->get('messages.error_404_message');
        $homeLinkText = $translator->get('messages.home_link_text'); // Get the translated home link text

        // If a custom message key was provided, try to translate it.
        // If translation fails for the custom key, use the provided message string itself.
        $finalMessage = $message ? $translator->get($message, [], $message) : $defaultMessage;

        // Use the global helper function to render the standard HTTP error page.
        // This function sets the status code and exits.
        render_http_error_page(
            404, // Status code
            $errorTitle,
            $finalMessage,
            $homeLinkText // Pass the translated text
        );
    }

    // --- ArrayAccess methods (Optional: Implement if needed) ---
    /*
    #[ReturnTypeWillChange]
    public function offsetExists(mixed $offset): bool { ... }
    #[ReturnTypeWillChange]
    public function offsetGet(mixed $offset): mixed { ... }
    #[ReturnTypeWillChange]
    public function offsetSet(mixed $offset, mixed $value): void { ... }F
    #[ReturnTypeWillChange]
    public function offsetUnset(mixed $offset): void { ... }
    */
}