<?php
// File: src/Http/Request.php
declare(strict_types=1);

namespace PhpLiteCore\Http;

class Request
{
    /**
     * Retrieve the client IP address, considering proxy headers.
     *
     * @return string The client IP or '0.0.0.0' if it is unknown.
     */
    public static function getClientIp(): string
    {
        $ip = null;

        // Check common headers in order of trust
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            // Can contain multiple IPs, take the first one
            $parts = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = trim($parts[0]);
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED'];
        } elseif (!empty($_SERVER['HTTP_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_FORWARDED_FOR'];
        } elseif (!empty($_SERVER['HTTP_FORWARDED'])) {
            $forward = $_SERVER['HTTP_FORWARDED'];
            // Format: "for="<IP>"... -> extract IP after 'for='
            if (preg_match('/for="?([^;,\"]+)"?/', $forward, $matches)) {
                $ip = $matches[1];
            } else {
                $ip = $forward;
            }
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
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
    public static function isAjax(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strcasecmp($_SERVER['HTTP_X_REQUESTED_WITH'], 'XMLHttpRequest') === 0;
    }
}
