<?php

declare(strict_types=1);

namespace PhpLiteCore\Utils;

use PhpLiteCore\Bootstrap\Application;

class UrlUtils
{
    /**
     * Normalize URL protocol by removing http(s): and using protocol-relative URL.
     *
     * @param string $url Absolute URL or local path.
     * @return string Protocol-relative URL or trimmed local path.
     */
    public static function fixUrlProtocol(string $url): string
    {
        // If already protocol-relative or a root-relative path, just trim trailing slash
        if (str_starts_with($url, '//') || str_starts_with($url, '/')) {
            return rtrim($url, '/');
        }

        // Remove 'http:' or 'https:' and trim surrounding slashes, then prefix '//'
        $trimmed = trim(str_ireplace(['http:', 'https:'], '', $url), '/');

        return '//' . $trimmed;
    }

    /**
     * Generate a URL for a named route with optional parameters.
     *
     * @param string $name The route name
     * @param array $params The route parameters (e.g., ['id' => 123])
     * @return string The generated URL
     * @throws \InvalidArgumentException If route not found or parameters are missing
     */
    public static function route(string $name, array $params = []): string
    {
        $app = Application::getInstance();

        return $app->router->route($name, $params);
    }
}
