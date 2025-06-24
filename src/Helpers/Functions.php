<?php

use JetBrains\PhpStorm\Pure;

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
