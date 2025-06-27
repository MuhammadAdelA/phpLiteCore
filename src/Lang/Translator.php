<?php
namespace PhpLiteCore\Lang;

class Translator
{
    protected string $locale;
    protected string $langPath;
    protected array  $messages = [];

    public function __construct(string $locale = 'en', string $customLangPath = null)
    {
        $this->locale   = $locale;
        // Use the injected path or default base path two levels up
        $this->langPath = $customLangPath
            ?? dirname(__DIR__, 2)
            . DIRECTORY_SEPARATOR . 'resources'
            . DIRECTORY_SEPARATOR . 'lang';

        $this->loadMessages();
    }

    protected function loadMessages(): void
    {
        $file = $this->langPath
            . DIRECTORY_SEPARATOR . $this->locale . '.php';

        if (! is_file($file)) {
            throw new \RuntimeException(
                "Translation file not found: {$file}"
            );
        }

        $this->messages = require $file;
    }

    public function get(string $key, array $replace = []): string
    {
        $message = $this->messages[$key] ?? $key;
        foreach ($replace as $p => $v) {
            $message = str_replace("{{$p}}", $v, $message);
        }
        return $message;
    }
}
