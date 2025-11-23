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
     * Get a header value from the request.
     * Normalizes header name and checks both HTTP_* and special headers like CONTENT_TYPE.
     *
     * @param string $name The header name (e.g., 'Content-Type', 'X-Requested-With')
     * @param mixed $default Default value if header is not found
     * @return mixed The header value, or the default if not found
     */
    public function header(string $name, mixed $default = null): mixed
    {
        // Normalize header name to HTTP_* server key
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));

        if (isset($this->server[$key])) {
            return $this->server[$key];
        }

        // Some headers are provided without the HTTP_ prefix (CONTENT_TYPE, CONTENT_LENGTH)
        $special = strtoupper(str_replace('-', '_', $name));
        if (isset($this->server[$special])) {
            return $this->server[$special];
        }

        // Case-insensitive fallback: scan server keys for case-insensitive match
        foreach ($this->server as $k => $v) {
            if (strcasecmp($k, $key) === 0 || strcasecmp($k, $special) === 0) {
                return $v;
            }
        }

        return $default;
    }

    /**
     * Get all request headers.
     * Extracts HTTP_* and special headers from $_SERVER.
     *
     * @return array<string, mixed> Array of headers
     */
    public function headers(): array
    {
        $headers = [];
        
        foreach ($this->server as $key => $value) {
            // Check for HTTP_* headers
            if (str_starts_with($key, 'HTTP_')) {
                // Remove HTTP_ prefix and convert to header name format
                $headerName = substr($key, 5);
                $headers[$headerName] = $value;
            }
            // Include special headers that don't have HTTP_ prefix
            elseif (in_array($key, ['CONTENT_TYPE', 'CONTENT_LENGTH', 'CONTENT_MD5'])) {
                $headers[$key] = $value;
            }
        }
        
        return $headers;
    }

    /**
     * Check if a header exists in the request.
     *
     * @param string $name The header name
     * @return bool True if header exists, false otherwise
     */
    public function hasHeader(string $name): bool
    {
        // Normalize header name to HTTP_* server key
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));

        if (isset($this->server[$key])) {
            return true;
        }

        // Some headers are provided without the HTTP_ prefix
        $special = strtoupper(str_replace('-', '_', $name));
        if (isset($this->server[$special])) {
            return true;
        }

        // Case-insensitive fallback
        foreach ($this->server as $k => $v) {
            if (strcasecmp($k, $key) === 0 || strcasecmp($k, $special) === 0) {
                return true;
            }
        }

        return false;
    }
}
