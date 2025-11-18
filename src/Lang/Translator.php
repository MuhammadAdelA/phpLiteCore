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
     * The fallback locale code (e.g., 'en').
     * @var string
     */
    protected string $fallbackLocale;

    /**
     * The absolute path to the base language files directory.
     * @var string
     */
    protected string $langPath;

    /**
     * The array of loaded translation messages for the current locale.
     * @var array
     */
    protected array $messages = [];

    /**
     * The array of loaded translation messages for the fallback locale.
     * @var array
     */
    protected array $fallbackMessages = [];

    /**
     * Translator constructor.
     *
     * @param string $locale The language code (e.g., 'en', 'ar').
     * @param string|null $customLangPath Optional custom path to the base language directory.
     * @param string|null $fallbackLocale Optional fallback language code.
     */
    public function __construct(string $locale = 'en', ?string $customLangPath = null, ?string $fallbackLocale = 'en')
    {
        $this->locale = $locale;
        $this->fallbackLocale = $fallbackLocale ?? 'en';
        $this->langPath = $customLangPath
            ?? PHPLITECORE_ROOT . 'resources' . DIRECTORY_SEPARATOR . 'lang';

        // Load the default 'messages' file initially for the main locale.
        $this->loadMessagesFromFile('messages', $this->locale);
    }

    /**
     * Loads translation messages from a specific file.
     *
     * @param string $filename The name of the file to load.
     * @param string $locale The locale to load the file for.
     * @param bool $isFallback Whether the messages are for the fallback locale.
     * @return void
     */
    protected function loadMessagesFromFile(string $filename, string $locale, bool $isFallback = false): void
    {
        $file = $this->langPath . DIRECTORY_SEPARATOR . $locale . DIRECTORY_SEPARATOR . $filename . '.php';

        if (is_file($file)) {
            $messages = require $file;
            if ($isFallback) {
                $this->fallbackMessages[$filename] = $messages;
            } else {
                $this->messages[$filename] = $messages;
            }

            return;
        }

        if (defined('ENV') && ENV === 'development') {
            throw new RuntimeException("Translation file not found: {$file}");
        }

        error_log("Translation file not found: {$file}");
        // Ensure the key exists to avoid repeated file checks.
        if ($isFallback) {
            $this->fallbackMessages[$filename] = [];
        } else {
            $this->messages[$filename] = [];
        }
    }

    /**
     * Retrieves a translated message by its key.
     *
     * @param string $key The key (e.g., 'messages.welcome').
     * @param array $replace Associative array of placeholder => value pairs.
     * @param string|null $default A default value to return if the key is not found.
     * @return string The translated message, the default value, or the key itself.
     */
    public function get(string $key, array $replace = [], ?string $default = null): string
    {
        if (! str_contains($key, '.')) {
            $key = 'messages.' . $key;
        }

        [$file, $messageKey] = explode('.', $key, 2);

        // Lazy-load the primary locale file if not already loaded.
        if (! isset($this->messages[$file])) {
            $this->loadMessagesFromFile($file, $this->locale);
        }

        // Try to find the message in the primary locale.
        $message = $this->findByDotNotation($this->messages[$file] ?? [], $messageKey);

        // If not found, try the fallback locale.
        if ($message === null && $this->locale !== $this->fallbackLocale) {
            if (! isset($this->fallbackMessages[$file])) {
                $this->loadMessagesFromFile($file, $this->fallbackLocale, true);
            }
            $message = $this->findByDotNotation($this->fallbackMessages[$file] ?? [], $messageKey);
        }

        // If still not found, return the default value or the original key.
        if ($message === null) {
            return $default ?? $key;
        }

        // Replace placeholders if the message is a string.
        if (is_string($message) && ! empty($replace)) {
            foreach ($replace as $placeholder => $value) {
                $message = str_replace(":{$placeholder}", (string)$value, $message);
            }
        }

        return (string) $message;
    }

    /**
     * Retrieves a translated message with pluralization support.
     *
     * @param string $key The key for the translation string.
     * @param int|float $number The number to use for pluralization.
     * @param array $replace Associative array of placeholder => value pairs.
     * @param string|null $default A default value to return if not found.
     * @return string The translated message.
     */
    public function getChoice(string $key, int|float $number, array $replace = [], ?string $default = null): string
    {
        $message = $this->get($key, [], $default);

        // If the key was not found, return it or the default.
        if ($message === $key || $message === $default) {
            return $message;
        }

        $parts = explode('|', $message);
        $chosenMessage = ($number == 1) ? $parts[0] : ($parts[1] ?? $parts[0]);

        $replace['count'] = $number;

        foreach ($replace as $placeholder => $value) {
            $chosenMessage = str_replace(":{$placeholder}", (string)$value, $chosenMessage);
        }

        return $chosenMessage;
    }

    /**
     * Checks if a translation key exists.
     *
     * @param string $key The key to check (e.g., 'messages.welcome').
     * @return bool
     */
    public function has(string $key): bool
    {
        // Use a unique sentinel value to differentiate a not-found key from a null/empty one.
        $sentinel = '___TRANSLATION_NOT_FOUND___';

        return $this->get($key, [], $sentinel) !== $sentinel;
    }

    /**
     * Finds a translation value within an array using dot notation.
     *
     * @param array $messagesArray The array of messages for a specific file.
     * @param string $key The dot-separated key within that file.
     * @return mixed The found value, or null if not found.
     */
    protected function findByDotNotation(array $messagesArray, string $key): mixed
    {
        $current = $messagesArray;
        $keys = explode('.', $key);

        foreach ($keys as $segment) {
            if (! is_array($current) || ! array_key_exists($segment, $current)) {
                return null; // Return null if the key path is broken.
            }
            $current = $current[$segment];
        }

        return $current;
    }
}
