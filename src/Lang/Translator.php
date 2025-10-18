<?php

namespace PhpLiteCore\Lang;

use RuntimeException;

/**
 * Class Translator
 *
 * Handles loading and retrieval of language strings from translation files
 * organized in subdirectories per locale.
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
     * The absolute path to the base language files directory.
     * @var string
     */
    protected string $langPath;

    /**
     * The array of loaded translation messages for the current locale.
     * Messages are stored under keys representing the file they came from (e.g., 'messages').
     * @var array
     */
    protected array $messages = [];

    /**
     * Translator constructor.
     *
     * @param string $locale The language code (e.g., 'en', 'ar').
     * @param string|null $customLangPath Optional custom path to the base language directory.
     */
    public function __construct(string $locale = 'en', string $customLangPath = null)
    {
        $this->locale = $locale;
        $this->langPath = $customLangPath
            ?? PHPLITECORE_ROOT . 'resources' . DIRECTORY_SEPARATOR . 'lang';

        // Load the default 'messages' file initially.
        $this->loadMessagesFromFile('messages');
    }

    /**
     * Loads translation messages from a specific file within the locale's directory.
     *
     * Handles errors based on the application environment.
     *
     * @param string $filename The name of the file to load (without .php extension, e.g., 'messages').
     * @return void
     * @throws RuntimeException If the translation file is not found in 'development'.
     */
    protected function loadMessagesFromFile(string $filename): void
    {
        // Construct the path to the file inside the locale directory.
        $file = $this->langPath . DIRECTORY_SEPARATOR . $this->locale . DIRECTORY_SEPARATOR . $filename . '.php';

        if (is_file($file)) {
            // Store messages under a key matching the filename.
            $this->messages[$filename] = require $file;
            return;
        }

        // Handle missing file based on environment.
        if (defined('ENV') && ENV === 'development') {
            throw new RuntimeException("Translation file not found: {$file}");
        }

        error_log("Translation file not found: {$file}");
        // Ensure the key exists even if the file is missing in production.
        $this->messages[$filename] = [];
    }

    /**
     * Retrieves a translated message by its key (e.g., 'messages.welcome').
     *
     * Supports dot notation: the first segment is the filename, the rest is the nested key.
     * If the key format is invalid or not found, returns the key itself.
     * Supports placeholder replacement.
     *
     * @param string $key The key (e.g., 'messages.welcome' or just 'welcome' which defaults to 'messages.welcome').
     * @param array $replace Associative array of placeholder => value pairs.
     * @param string|null $default A default value to return if the key is not found (optional).
     * @return string The translated message, the default value, or the key itself.
     */
    public function get(string $key, array $replace = [], ?string $default = null): string
    {
        // Assume 'messages' file if no file is specified in the key.
        if (!str_contains($key, '.')) {
            $key = 'messages.' . $key;
        }

        [$file, $messageKey] = explode('.', $key, 2);

        // Load the file if it hasn't been loaded yet.
        // This enables lazy-loading of files like 'validation.php' or 'auth.php'
        if (!isset($this->messages[$file])) {
            $this->loadMessagesFromFile($file);
        }

        $message = $this->findByDotNotation($this->messages[$file] ?? [], $messageKey);

        // If the key was not found, return the default value or the original full key.
        if ($message === $messageKey) { // findByDotNotation returns the key segment if not found
            return $default ?? $key;
        }

        // Replace placeholders if the message is a string.
        if (is_string($message)) {
            foreach ($replace as $placeholder => $value) {
                $message = str_replace("{{{$placeholder}}}", (string)$value, $message);
            }
        }

        return (string) $message;
    }

    /**
     * Finds a translation value within a specific message array using dot notation.
     *
     * @param array $messagesArray The array of messages for a specific file.
     * @param string $key The dot-separated key within that file.
     * @return mixed The found value, or the key itself if not found.
     */
    protected function findByDotNotation(array $messagesArray, string $key): mixed
    {
        $current = $messagesArray;
        $keys = explode('.', $key);

        foreach ($keys as $segment) {
            if (!is_array($current) || !isset($current[$segment])) {
                return $key; // Return the key segment if not found
            }
            $current = $current[$segment];
        }

        return $current; // Return the found value (could be string or array)
    }
}