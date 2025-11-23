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
     * @param string $message Optional custom message (which MUST be pre-translated by the controller).
     * If empty, the default 404 message will be used.
     * @return void
     */
    #[NoReturn] public static function notFound(string $message = ''): void
    {
        // Instantiate the translator using the current language constant (LANG).
        $currentLang = defined('LANG') ? LANG : ($_ENV['DEFAULT_LANG'] ?? 'en');
        $translator = new Translator($currentLang);

        // Get standard translated strings for 404.
        $errorTitle = $translator->get('messages.error_404_title');
        $defaultMessage = $translator->get('messages.error_404_message');
        $homeLinkText = $translator->get('messages.home_link_text');


        // Use the provided $message directly if it exists (it's already translated).
        // Otherwise, fall back to the default translated message.
        // This stops the "re-translation" bug.
        $finalMessage = $message ?: $defaultMessage;

        // Use the global helper function to render the standard HTTP error page.
        // This function sets the status code and exits.
        render_http_error_page(
            404, // Status code
            $errorTitle,
            $finalMessage,
            $homeLinkText
        );
    }

    /**
     * Static helper for 403 Forbidden response.
     * Renders a simple 403 error page or message.
     * Terminates script execution.
     *
     * @param string $message The error message to display.
     * @return void
     */
    #[NoReturn] public static function forbidden(string $message = 'Forbidden'): void
    {
        http_response_code(403);
        header('Content-Type: text/plain');
        echo $message;
        exit;
    }

    /**
     * Static helper for 429 Too Many Requests response.
     * Sends a rate limit exceeded response with Retry-After header.
     * Terminates script execution.
     *
     * @param string $message The error message to display.
     * @param int $retryAfter Number of seconds to wait before retrying.
     * @return void
     */
    #[NoReturn] public static function tooManyRequests(string $message = 'Too Many Requests', int $retryAfter = 60): void
    {
        http_response_code(429);
        header('Content-Type: text/plain');
        header('Retry-After: ' . $retryAfter);
        echo $message;
        exit;
    }

    /**
     * Instance method to create a JSON response.
     * Sets the appropriate Content-Type header and encodes the data.
     * This method is chainable and preserves status code if already set.
     *
     * @param mixed $data The data to encode as JSON.
     * @param int|null $status Optional status code to set.
     * @return static
     */
    public function withJson(mixed $data, ?int $status = null): static
    {
        if ($status !== null) {
            $this->setStatusCode($status);
        }
        $this->setHeader('Content-Type', 'application/json');
        $this->setContent(json_encode($data));
        return $this;
    }

    /**
     * Instance method to create a plain text response.
     * This method is chainable and preserves status code if already set.
     *
     * @param string $content The text content.
     * @param int|null $status Optional status code to set.
     * @return static
     */
    public function text(string $content, ?int $status = null): static
    {
        if ($status !== null) {
            $this->setStatusCode($status);
        }
        $this->setHeader('Content-Type', 'text/plain');
        $this->setContent($content);
        return $this;
    }

    /**
     * Instance method to create an HTML response.
     * This method is chainable and preserves status code if already set.
     *
     * @param string $content The HTML content.
     * @param int|null $status Optional status code to set.
     * @return static
     */
    public function html(string $content, ?int $status = null): static
    {
        if ($status !== null) {
            $this->setStatusCode($status);
        }
        $this->setHeader('Content-Type', 'text/html');
        $this->setContent($content);
        return $this;
    }

    /**
     * Instance method to create a redirect response.
     * This method is chainable and preserves status code if already set to a non-default value.
     * If status code is still at default (200), it will be set to 302.
     *
     * @param string $url The URL to redirect to.
     * @param int|null $status Optional status code to explicitly set.
     * @return static
     */
    public function redirectTo(string $url, ?int $status = null): static
    {
        if ($status !== null) {
            $this->setStatusCode($status);
        } elseif ($this->statusCode === 200) {
            // Set default redirect status (302) only if status code is still at initial default (200)
            $this->setStatusCode(302);
        }
        // If status code is not 200 (e.g., was set to 301, 307, etc.), preserve it
        $this->setHeader('Location', $url);
        return $this;
    }
}