<?php

namespace PhpLiteCore\View;

use Exception;

class View
{
    /**
     * The full path to the view file.
     * @var string
     */
    protected string $path;

    /**
     * The data to be passed to the view.
     * @var array
     */
    protected array $data;

    /**
     * View constructor.
     *
     * @param string $view The view file name (e.g., 'home' or 'pages.about').
     * @param array $data The data to make available to the view.
     * @param string $theme The theme to use (optional).
     * @throws Exception If the view file is not found.
     */
    public function __construct(string $view, array $data = [], string $theme = 'default')
    {
        $pathSegments = [
            PHPLITECORE_ROOT,
            'views',
            'themes',
            $theme,
            str_replace('.', DIRECTORY_SEPARATOR, $view) . '.php'
        ];
        $this->path = implode(DIRECTORY_SEPARATOR, $pathSegments);

        if (!file_exists($this->path)) {
            throw new Exception("View file not found: {$this->path}");
        }

        $this->data = $data;
    }

    /**
     * Renders the view and returns the output as a string.
     *
     * @return string
     */
    public function render(): string
    {
        extract($this->data);

        ob_start();
        try {
            require $this->path;
        } finally {
            $content = ob_get_clean();
        }

        return $content;
    }

    /**
     * Static factory method for convenience.
     * Creates a new View instance and renders it immediately.
     *
     * @param string $view The view file name.
     * @param array $data The data for the view.
     * @param string $theme The theme to use.
     * @return string
     * @throws Exception
     */
    public static function make(string $view, array $data = [], string $theme = 'default'): string
    {
        return (new self($view, $data, $theme))->render();
    }
}