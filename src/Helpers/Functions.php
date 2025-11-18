<?php

use JetBrains\PhpStorm\NoReturn;
use JetBrains\PhpStorm\Pure;

/**
 * Renders a generic HTTP error page.
 * This is a self-contained function to avoid dependencies on the View class.
 *
 * @param int    $error_code    The HTTP status code (e.g., 404, 500).
 * @param string $error_title   The title of the error (e.g., 'Not Found').
 * @param string $error_message The user-friendly message to display.
 * @param string $homeLinkText  The translated text for the "home" button.
 * @return void
 */
#[NoReturn]
function render_http_error_page(int $error_code, string $error_title, string $error_message, string $homeLinkText): void
{
    http_response_code($error_code);

    // Define the path for the custom error page in the theme.
    $customErrorViewPath = PHPLITECORE_ROOT . 'views' . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . 'default' . DIRECTORY_SEPARATOR . 'error-pages' . DIRECTORY_SEPARATOR . $error_code . '.php';

    // Define the path for the default system error page.
    $defaultErrorViewPath = PHPLITECORE_ROOT . 'views' . DIRECTORY_SEPARATOR . 'system' . DIRECTORY_SEPARATOR . 'http_error.php';

    // Check if a custom error page exists.
    if (file_exists($customErrorViewPath)) {
        $viewToRender = $customErrorViewPath;
    } else {
        $viewToRender = $defaultErrorViewPath;
    }

    // Extract variables for the chosen view file.
    extract(compact('error_code', 'error_title', 'error_message', 'homeLinkText'));

    // Use output buffering to capture the view.
    ob_start();
    require $viewToRender;
    echo ob_get_clean();

    exit;
}

/**
 * @param string $lang
 * @return string
 */
function getDirection(string $lang): string
{
    return in_array($lang, ['ar', 'he', 'fa']) ? 'rtl' : 'ltr';
}
/**
 * Determine and return the language code.
 *
 * @param string $default Default language code.
 * @return string Selected language code.
 */
function set_language(string $default = DEFAULT_LANG): string
{
    // Get and validate 'lang' from GET
    $requested = filter_input(INPUT_GET, 'lang');

    if ($requested && is_lang($requested)) {
        $lang = $requested;

        // Set cookie for 30 days with secure options
        setcookie('lang', $lang, [
            'expires' => time() + 30 * 24 * 60 * 60,
            'path' => '/',
            'secure' => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        // 1. Get the current URL's path (e.g., /posts)
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';

        // 2. Get the current query string (e.g., page=2&lang=ar)
        $queryString = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);

        // 3. Parse the query string into an array
        $queryParams = [];
        if ($queryString) {
            parse_str($queryString, $queryParams);
        }

        // 4. Remove the 'lang' parameter from the array
        unset($queryParams['lang']);

        // 5. Re-build the query string (if any params are left)
        $location = $path;
        if (! empty($queryParams)) {
            // This will result in (e.g., /posts?page=2)
            $location .= '?' . http_build_query($queryParams);
        }

        // (The old line was: $location = filter_input(INPUT_GET, 'location', FILTER_SANITIZE_URL) ?: '/';)
        header("Location: $location");
        exit;
    }

    // Check if cookie exists and is valid
    if (isset($_COOKIE['lang']) && is_lang($_COOKIE['lang'])) {
        $lang = $_COOKIE['lang'];
    } else {
        // Set default cookie for 1 year
        setcookie('lang', $default, [
            'expires' => time() + 365 * 24 * 60 * 60,
            'path' => '/',
            'secure' => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        $lang = $default;
    }

    return $lang;
}

/**
 * Check if the given language code is supported.
 *
 * @param string $lang Language code to validate.
 * @return bool Returns true if supported, false otherwise.
 */
#[Pure] function is_lang(string $lang): bool
{
    $supported_languages = ['ar', 'en'];

    return in_array($lang, $supported_languages, true);
}

function is_rtl(): bool
{
    return HTML_DIR === "rtl";
}

/**
 * Escape HTML special characters.
 *
 * This is a convenience wrapper around htmlspecialchars() with safe defaults.
 * It uses ENT_QUOTES to escape both double and single quotes, ENT_SUBSTITUTE to replace
 * invalid characters with a Unicode Replacement Character, and UTF-8 as the encoding.
 *
 * @param string|null $string The string to escape.
 * @return string The escaped string, or an empty string if null is provided.
 */
if (! function_exists('e')) {
    function e(?string $string): string
    {
        if ($string === null) {
            return '';
        }

        return htmlspecialchars($string, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

/**
 * Generate a hidden CSRF token field for forms.
 *
 * @return string The HTML for the hidden CSRF token field.
 */
if (! function_exists('csrf_field')) {
    function csrf_field(): string
    {
        $token = \PhpLiteCore\Http\Middleware\CsrfMiddleware::token();

        return '<input type="hidden" name="_token" value="' . e($token) . '">';
    }
}

/**
 * Generate a URL for a named route with optional parameters.
 *
 * @param string $name The route name
 * @param array $params The route parameters (e.g., ['id' => 123])
 * @return string The generated URL
 * @throws \InvalidArgumentException If route not found or parameters are missing
 */
if (! function_exists('route')) {
    function route(string $name, array $params = []): string
    {
        $app = \PhpLiteCore\Bootstrap\Application::getInstance();

        return $app->router->route($name, $params);
    }
}
