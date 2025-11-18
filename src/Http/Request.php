<?php
// File: src/Http/Request.php
declare(strict_types=1);

namespace PhpLiteCore\Http;

class Request
{
    /**
     * @var array<string, mixed> GET parameters
     */
    private array $get;

    /**
     * @var array<string, mixed> POST parameters
     */
    private array $post;

    /**
     * @var array<string, mixed> Server and execution environment information
     */
    private array $server;

    /**
     * @var array<string, mixed> Cookies
     */
    private array $cookies;

    /**
     * @var array<string, mixed> Uploaded files
     */
    private array $files;

    /**
     * Create a new Request instance.
     *
     * @param array<string, mixed> $get GET parameters
     * @param array<string, mixed> $post POST parameters
     * @param array<string, mixed> $server Server and execution environment information
     * @param array<string, mixed> $cookies Cookies
     * @param array<string, mixed> $files Uploaded files
     */
    public function __construct(
        array $get = [],
        array $post = [],
        array $server = [],
        array $cookies = [],
        array $files = []
    ) {
        $this->get = $get;
        $this->post = $post;
        $this->server = $server;
        $this->cookies = $cookies;
        $this->files = $files;
    }

    /**
     * Create a Request instance from PHP globals.
     *
     * @return static
     */
    public static function createFromGlobals(): static
    {
        return new static(
            $_GET,
            $_POST,
            $_SERVER,
            $_COOKIE,
            $_FILES
        );
    }

    /**
     * Retrieve the client IP address, considering proxy headers.
     *
     * @return string The client IP or '0.0.0.0' if it is unknown.
     */
    public function getClientIp(): string
    {
        $ip = null;

        // Check common headers in order of trust
        if (!empty($this->server['HTTP_CLIENT_IP'])) {
            $ip = $this->server['HTTP_CLIENT_IP'];
        } elseif (!empty($this->server['HTTP_X_FORWARDED_FOR'])) {
            // Can contain multiple IPs, take the first one
            $parts = explode(',', $this->server['HTTP_X_FORWARDED_FOR']);
            $ip = trim($parts[0]);
        } elseif (!empty($this->server['HTTP_X_FORWARDED'])) {
            $ip = $this->server['HTTP_X_FORWARDED'];
        } elseif (!empty($this->server['HTTP_FORWARDED_FOR'])) {
            $ip = $this->server['HTTP_FORWARDED_FOR'];
        } elseif (!empty($this->server['HTTP_FORWARDED'])) {
            $forward = $this->server['HTTP_FORWARDED'];
            // Format: "for="<IP>"... -> extract IP after 'for='
            if (preg_match('/for="?([^;,\"]+)"?/', $forward, $matches)) {
                $ip = $matches[1];
            } else {
                $ip = $forward;
            }
        } elseif (!empty($this->server['REMOTE_ADDR'])) {
            $ip = $this->server['REMOTE_ADDR'];
        }

        // Normalize IPv6 address (remove brackets and port)
        if ($ip !== null) {
            if (str_contains($ip, ':')) {
                if (preg_match('/^\[?([^]]+)]?(?::\d+)?$/', $ip, $matches)) {
                    $ip = $matches[1];
                }
            }
        }

        // Validate IP address (both IPv4 and IPv6)
        if ($ip !== null && filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6)) {
            return $ip;
        }

        return '0.0.0.0';
    }

    /**
     * Check if the request is an AJAX request.
     *
     * @return bool True if AJAX (XMLHttpRequest), false otherwise.
     */
    public function isAjax(): bool
    {
        return !empty($this->server['HTTP_X_REQUESTED_WITH'])
            && strcasecmp($this->server['HTTP_X_REQUESTED_WITH'], 'XMLHttpRequest') === 0;
    }

    /**
     * Get the request URI path.
     *
     * @return string The URI path, or '/' if not available.
     */
    public function getPath(): string
    {
        if (empty($this->server['REQUEST_URI'])) {
            return '/';
        }

        $uri = $this->server['REQUEST_URI'];
        
        // Remove query string if present
        if (($pos = strpos($uri, '?')) !== false) {
            $uri = substr($uri, 0, $pos);
        }

        return $uri;
    }

    /**
     * Get the request method.
     *
     * @return string The request method (e.g., GET, POST, PUT, DELETE), or 'GET' if not available.
     */
    public function getMethod(): string
    {
        return !empty($this->server['REQUEST_METHOD']) 
            ? strtoupper($this->server['REQUEST_METHOD']) 
            : 'GET';
    }

    /**
     * Get a variable from POST or GET data.
     * Checks POST first, then GET.
     *
     * @param string $key The key to retrieve
     * @param mixed $default Default value if key is not found
     * @return mixed The value, or the default if not found
     */
    public function input(string $key, mixed $default = null): mixed
    {
        // Check POST first, then GET
        if (array_key_exists($key, $this->post)) {
            return $this->post[$key];
        }

        if (array_key_exists($key, $this->get)) {
            return $this->get[$key];
        }

        return $default;
    }

    /**
     * Get a query parameter (from GET).
     *
     * @param string $key The key to retrieve
     * @param mixed $default Default value if key is not found
     * @return mixed The value, or the default if not found
     */
    public function query(string $key, mixed $default = null): mixed
    {
        return $this->get[$key] ?? $default;
    }

    /**
     * Get all query parameters (from GET).
     *
     * @return array<string, mixed> All GET parameters
     */
    public function queryAll(): array
    {
        return $this->get;
    }

    /**
     * Get a POST parameter.
     *
     * @param string $key The key to retrieve
     * @param mixed $default Default value if key is not found
     * @return mixed The value, or the default if not found
     */
    public function post(string $key, mixed $default = null): mixed
    {
        return $this->post[$key] ?? $default;
    }

    /**
     * Get all POST parameters.
     *
     * @return array<string, mixed> All POST parameters
     */
    public function postAll(): array
    {
        return $this->post;
    }

    /**
     * Get all input data (both GET and POST).
     * POST data takes precedence over GET data.
     *
     * @return array<string, mixed> All input parameters
     */
    public function all(): array
    {
        return array_merge($this->get, $this->post);
    }

    /**
     * Check if an input parameter exists (in POST or GET).
     *
     * @param string $key The key to check
     * @return bool True if the key exists, false otherwise
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->post) || array_key_exists($key, $this->get);
    }

    /**
     * Get a header value.
     * Header names are case-insensitive.
     *
     * @param string $name The header name (e.g., 'Content-Type', 'Authorization')
     * @param mixed $default Default value if header is not found
     * @return mixed The header value, or the default if not found
     */
    public function header(string $name, mixed $default = null): mixed
    {
        // Convert header name to HTTP_UPPERCASE_FORMAT
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        
        // Some headers don't have HTTP_ prefix
        $specialHeaders = [
            'CONTENT_TYPE' => 'CONTENT_TYPE',
            'CONTENT_LENGTH' => 'CONTENT_LENGTH',
        ];
        
        $upperName = strtoupper(str_replace('-', '_', $name));
        if (isset($specialHeaders[$upperName])) {
            $key = $specialHeaders[$upperName];
        }
        
        return $this->server[$key] ?? $default;
    }

    /**
     * Get all headers.
     *
     * @return array<string, string> All HTTP headers
     */
    public function headers(): array
    {
        $headers = [];
        
        foreach ($this->server as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                // Convert HTTP_CONTENT_TYPE to Content-Type
                $headerName = str_replace('_', '-', substr($key, 5));
                $headerName = implode('-', array_map('ucfirst', explode('-', strtolower($headerName))));
                $headers[$headerName] = $value;
            } elseif (in_array($key, ['CONTENT_TYPE', 'CONTENT_LENGTH'])) {
                $headerName = str_replace('_', '-', $key);
                $headerName = implode('-', array_map('ucfirst', explode('-', strtolower($headerName))));
                $headers[$headerName] = $value;
            }
        }
        
        return $headers;
    }

    /**
     * Check if a header exists.
     *
     * @param string $name The header name
     * @return bool True if the header exists, false otherwise
     */
    public function hasHeader(string $name): bool
    {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        
        $specialHeaders = ['CONTENT_TYPE', 'CONTENT_LENGTH'];
        $upperName = strtoupper(str_replace('-', '_', $name));
        if (in_array($upperName, $specialHeaders)) {
            $key = $upperName;
        }
        
        return isset($this->server[$key]);
    }

    /**
     * Get a cookie value.
     *
     * @param string $name The cookie name
     * @param mixed $default Default value if cookie is not found
     * @return mixed The cookie value, or the default if not found
     */
    public function cookie(string $name, mixed $default = null): mixed
    {
        return $this->cookies[$name] ?? $default;
    }

    /**
     * Get all cookies.
     *
     * @return array<string, mixed> All cookies
     */
    public function cookies(): array
    {
        return $this->cookies;
    }

    /**
     * Check if a cookie exists.
     *
     * @param string $name The cookie name
     * @return bool True if the cookie exists, false otherwise
     */
    public function hasCookie(string $name): bool
    {
        return array_key_exists($name, $this->cookies);
    }

    /**
     * Get an uploaded file.
     *
     * @param string $name The file input name
     * @return array<string, mixed>|null The file array or null if not found
     */
    public function file(string $name): ?array
    {
        return $this->files[$name] ?? null;
    }

    /**
     * Get all uploaded files.
     *
     * @return array<string, mixed> All uploaded files
     */
    public function files(): array
    {
        return $this->files;
    }

    /**
     * Check if a file was uploaded.
     *
     * @param string $name The file input name
     * @return bool True if the file exists and was uploaded successfully
     */
    public function hasFile(string $name): bool
    {
        return isset($this->files[$name]) 
            && is_array($this->files[$name]) 
            && isset($this->files[$name]['error']) 
            && $this->files[$name]['error'] === UPLOAD_ERR_OK;
    }

    /**
     * Check if the request method is GET.
     *
     * @return bool True if GET, false otherwise
     */
    public function isGet(): bool
    {
        return $this->getMethod() === 'GET';
    }

    /**
     * Check if the request method is POST.
     *
     * @return bool True if POST, false otherwise
     */
    public function isPost(): bool
    {
        return $this->getMethod() === 'POST';
    }

    /**
     * Check if the request method is PUT.
     *
     * @return bool True if PUT, false otherwise
     */
    public function isPut(): bool
    {
        return $this->getMethod() === 'PUT';
    }

    /**
     * Check if the request method is DELETE.
     *
     * @return bool True if DELETE, false otherwise
     */
    public function isDelete(): bool
    {
        return $this->getMethod() === 'DELETE';
    }

    /**
     * Check if the request method is PATCH.
     *
     * @return bool True if PATCH, false otherwise
     */
    public function isPatch(): bool
    {
        return $this->getMethod() === 'PATCH';
    }

    /**
     * Check if the request expects JSON response.
     *
     * @return bool True if JSON is expected, false otherwise
     */
    public function expectsJson(): bool
    {
        $accept = $this->header('Accept', '');
        return str_contains($accept, 'application/json') || str_contains($accept, 'text/json');
    }

    /**
     * Check if the request content type is JSON.
     *
     * @return bool True if content type is JSON, false otherwise
     */
    public function isJson(): bool
    {
        $contentType = $this->header('Content-Type', '');
        return str_contains($contentType, 'application/json');
    }

    /**
     * Get the request body as a JSON decoded array.
     *
     * @return array<string, mixed>|null The decoded JSON or null on failure
     */
    public function json(): ?array
    {
        if (!$this->isJson()) {
            return null;
        }
        
        $body = file_get_contents('php://input');
        if ($body === false || $body === '') {
            return null;
        }
        
        $decoded = json_decode($body, true);
        return is_array($decoded) ? $decoded : null;
    }

    /**
     * Get the full URL of the request.
     *
     * @return string The full URL
     */
    public function url(): string
    {
        $scheme = $this->isSecure() ? 'https' : 'http';
        $host = $this->server['HTTP_HOST'] ?? $this->server['SERVER_NAME'] ?? 'localhost';
        $uri = $this->server['REQUEST_URI'] ?? '/';
        
        return $scheme . '://' . $host . $uri;
    }

    /**
     * Check if the request is secure (HTTPS).
     *
     * @return bool True if HTTPS, false otherwise
     */
    public function isSecure(): bool
    {
        if (!empty($this->server['HTTPS']) && $this->server['HTTPS'] !== 'off') {
            return true;
        }
        
        if (!empty($this->server['HTTP_X_FORWARDED_PROTO']) && $this->server['HTTP_X_FORWARDED_PROTO'] === 'https') {
            return true;
        }
        
        if (!empty($this->server['HTTP_X_FORWARDED_SSL']) && $this->server['HTTP_X_FORWARDED_SSL'] === 'on') {
            return true;
        }
        
        return !empty($this->server['SERVER_PORT']) && $this->server['SERVER_PORT'] === '443';
    }

    /**
     * Get the user agent string.
     *
     * @return string The user agent string or empty string if not available
     */
    public function userAgent(): string
    {
        return $this->server['HTTP_USER_AGENT'] ?? '';
    }

    /**
     * Get the referer URL.
     *
     * @return string|null The referer URL or null if not available
     */
    public function referer(): ?string
    {
        return $this->server['HTTP_REFERER'] ?? null;
    }
}
