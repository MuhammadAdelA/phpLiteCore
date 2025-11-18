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

    /** @var array The cookies to set. */
    protected array $cookies = [];

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
     * Set multiple headers at once.
     *
     * @param array<string, string> $headers Array of headers
     * @return static
     */
    public function setHeaders(array $headers): static
    {
        foreach ($headers as $key => $value) {
            $this->headers[$key] = $value;
        }
        return $this;
    }

    /**
     * Get a header value.
     *
     * @param string $key The header name
     * @return string|null The header value or null if not set
     */
    public function getHeader(string $key): ?string
    {
        return $this->headers[$key] ?? null;
    }

    /**
     * Get all headers.
     *
     * @return array<string, string> All headers
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Set a cookie.
     *
     * @param string $name The cookie name
     * @param string $value The cookie value
     * @param int $expire The expiration time (Unix timestamp, default: 0 = session cookie)
     * @param string $path The path on the server where the cookie will be available (default: '/')
     * @param string $domain The domain that the cookie is available to (default: '')
     * @param bool $secure Whether the cookie should only be transmitted over HTTPS (default: false)
     * @param bool $httponly Whether the cookie is accessible only through HTTP protocol (default: true)
     * @return static
     */
    public function setCookie(
        string $name,
        string $value,
        int $expire = 0,
        string $path = '/',
        string $domain = '',
        bool $secure = false,
        bool $httponly = true
    ): static {
        $this->cookies[$name] = [
            'value' => $value,
            'expire' => $expire,
            'path' => $path,
            'domain' => $domain,
            'secure' => $secure,
            'httponly' => $httponly,
        ];
        return $this;
    }

    /**
     * Delete a cookie by setting its expiration to the past.
     *
     * @param string $name The cookie name
     * @param string $path The path on the server where the cookie is available
     * @param string $domain The domain that the cookie is available to
     * @return static
     */
    public function deleteCookie(string $name, string $path = '/', string $domain = ''): static
    {
        return $this->setCookie($name, '', time() - 3600, $path, $domain);
    }

    /**
     * Get the current status code.
     *
     * @return int The HTTP status code
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Get the response content.
     *
     * @return string The response body content
     */
    public function getContent(): string
    {
        return $this->content;
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

        // Send all cookies.
        foreach ($this->cookies as $name => $cookie) {
            setcookie(
                $name,
                $cookie['value'],
                $cookie['expire'],
                $cookie['path'],
                $cookie['domain'],
                $cookie['secure'],
                $cookie['httponly']
            );
        }

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
     * Create an instance-based JSON response (non-static).
     * Allows for method chaining.
     *
     * @param mixed $data The data to encode as JSON
     * @param int $statusCode The HTTP status code (default: 200 OK)
     * @return static
     */
    public function withJson(mixed $data, int $statusCode = 200): static
    {
        $this->setStatusCode($statusCode);
        $this->setHeader('Content-Type', 'application/json');
        $this->setContent(json_encode($data));
        return $this;
    }

    /**
     * Create a plain text response.
     *
     * @param string $content The text content
     * @param int $statusCode The HTTP status code (default: 200 OK)
     * @return static
     */
    public function text(string $content, int $statusCode = 200): static
    {
        $this->setStatusCode($statusCode);
        $this->setHeader('Content-Type', 'text/plain');
        $this->setContent($content);
        return $this;
    }

    /**
     * Create an HTML response.
     *
     * @param string $content The HTML content
     * @param int $statusCode The HTTP status code (default: 200 OK)
     * @return static
     */
    public function html(string $content, int $statusCode = 200): static
    {
        $this->setStatusCode($statusCode);
        $this->setHeader('Content-Type', 'text/html; charset=UTF-8');
        $this->setContent($content);
        return $this;
    }

    /**
     * Create a file download response.
     *
     * @param string $filePath Path to the file to download
     * @param string|null $filename The filename to send to the browser (defaults to basename of filePath)
     * @param array<string, string> $headers Additional headers
     * @return static
     */
    public function download(string $filePath, ?string $filename = null, array $headers = []): static
    {
        if (!file_exists($filePath)) {
            throw new \RuntimeException("File not found: {$filePath}");
        }

        $filename = $filename ?? basename($filePath);
        $mimeType = mime_content_type($filePath) ?: 'application/octet-stream';

        $this->setStatusCode(200);
        $this->setHeader('Content-Type', $mimeType);
        $this->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"');
        $this->setHeader('Content-Length', (string)filesize($filePath));
        
        foreach ($headers as $key => $value) {
            $this->setHeader($key, $value);
        }

        $this->setContent(file_get_contents($filePath));
        return $this;
    }

    /**
     * Create an instance-based redirect response (non-static).
     * Returns the instance for method chaining, does NOT exit.
     *
     * @param string $url The URL to redirect to
     * @param int $statusCode The HTTP status code for the redirect (default: 302 Found)
     * @return static
     */
    public function redirectTo(string $url, int $statusCode = 302): static
    {
        $this->setStatusCode($statusCode);
        $this->setHeader('Location', $url);
        return $this;
    }

    /**
     * Set cache control headers to prevent caching.
     *
     * @return static
     */
    public function noCache(): static
    {
        $this->setHeader('Cache-Control', 'no-cache, no-store, must-revalidate');
        $this->setHeader('Pragma', 'no-cache');
        $this->setHeader('Expires', '0');
        return $this;
    }

    /**
     * Set cache control headers for a specified duration.
     *
     * @param int $seconds Number of seconds to cache
     * @return static
     */
    public function cache(int $seconds): static
    {
        $this->setHeader('Cache-Control', 'public, max-age=' . $seconds);
        $this->setHeader('Expires', gmdate('D, d M Y H:i:s', time() + $seconds) . ' GMT');
        return $this;
    }
}