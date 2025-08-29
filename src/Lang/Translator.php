<?php

namespace PhpLiteCore\Lang;

use RuntimeException;

/**
 * Class Translator
 *
 * Handles the loading and retrieval of language strings from translation files.
 * It supports nested keys via dot notation and environment-aware error handling.
 *
 * @package PhpLiteCore\Lang
 */
class Translator
{
    /**
     * The current locale code (e.g., 'en', 'ar').
     * @var string
     */
    protected string $locale;

    /**
     * The absolute path to the language files directory.
     * @var string
     */
    protected string $langPath;

    /**
     * The array of loaded translation messages for the current locale.
     * @var array
     */
    protected array $messages = [];

    /**
     * Translator constructor.
     *
     * @param string $locale The language code to use for translations.
     * @param string|null $customLangPath An optional custom path to the language directory.
     * If not provided, it defaults to the 'resources/lang' directory in the project root.
     */
    public function __construct(string $locale = 'en', string $customLangPath = null)
    {
        $this->locale = $locale;

        // Use the custom path if provided, otherwise build the default path
        // using the PHPLITECORE_ROOT constant for robustness and consistency.
        $this->langPath = $customLangPath
            ?? PHPLITECORE_ROOT . 'resources' . DIRECTORY_SEPARATOR . 'lang';

        $this->loadMessages();
    }

    /**
     * Loads the translation messages from the appropriate language file.
     *
     * The behavior for a missing file depends on the application environment:
     * - In 'development', it throws a RuntimeException to alert the developer.
     * - In 'production', it fails silently to prevent the application from crashing.
     *
     * @return void
     * @throws RuntimeException If the translation file is not found in the 'development' environment.
     */
    protected function loadMessages(): void
    {
        $file = $this->langPath . DIRECTORY_SEPARATOR . $this->locale . '.php';

        if (is_file($file)) {
            $this->messages = require $file;
            return; // Exit the function on success.
        }

        // If the file is not found, handle based on the environment.
        if (defined('ENV') && ENV === 'development') {
            // In development, we want to fail loudly and clearly.
            throw new RuntimeException(
                "Translation file not found: {$file}"
            );
        }

        // In production or other environments, we log the error for the developer to review later.
        error_log("Translation file not found: {$file}");

        // In production or other environments, we fail silently.
        // The messages array will remain empty, causing keys to be returned by default.
        $this->messages = [];
    }

    /**
     * Retrieves a translated message by its key.
     *
     * Supports dot notation for accessing nested array keys.
     * If the key is not found, it returns the key itself.
     * Also supports replacing placeholders in the format {{placeholder}}.
     *
     * @param string $key The key of the message to retrieve (e.g., 'welcome' or 'messages.welcome').
     * @param array $replace An associative array of placeholder => value pairs.
     * @return string The translated message or the key if not found.
     */
    public function get(string $key, array $replace = []): string
    {
        $message = $this->findByDotNotation($key);

        // If the key was not found, findByDotNotation returns the original key.
        // We also check if the result is a string before replacing placeholders.
        if (is_string($message)) {
            foreach ($replace as $placeholder => $value) {
                $message = str_replace("{{{$placeholder}}}", (string)$value, $message);
            }
        }

        return (string) $message;
    }

    /**
     * Finds a translation value in the messages array using dot notation.
     *
     * @param string $key The dot-separated key.
     * @return mixed The found value, or the original key if not found.
     */
    protected function findByDotNotation(string $key): mixed
    {
        $current = $this->messages;
        $keys = explode('.', $key);

        foreach ($keys as $segment) {
            if (!is_array($current) || !isset($current[$segment])) {
                // If any part of the path is not found, return the original full key.
                return $key;
            }
            $current = $current[$segment];
        }

        // If the final key points to an array, it's ambiguous, so we return the original key.
        return is_array($current) ? $key : $current;
    }
}