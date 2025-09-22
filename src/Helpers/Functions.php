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
 * @return void
 */
#[NoReturn]
function render_http_error_page(int $error_code, string $error_title, string $error_message): void
{
    http_response_code($error_code);

    // Define the path for the custom error page in the theme.
    // In a more advanced implementation, 'default' would come from a config file.
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
    extract(compact('error_code', 'error_title', 'error_message'));

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
function getDirection(string $lang): string {
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
            'expires'  => time() + 30 * 24 * 60 * 60,
            'path'     => '/',
            'secure'   => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        // Determine redirect location safely
        $location = filter_input(INPUT_GET, 'location', FILTER_SANITIZE_URL) ?: '/';
        header("Location: $location");
        exit;
    }

    // Check if cookie exists and is valid
    if (isset($_COOKIE['lang']) && is_lang($_COOKIE['lang'])) {
        $lang = $_COOKIE['lang'];
    } else {
        // Set default cookie for 1 year
        setcookie('lang', $default, [
            'expires'  => time() + 365 * 24 * 60 * 60,
            'path'     => '/',
            'secure'   => isset($_SERVER['HTTPS']),
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
