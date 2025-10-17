<?php

declare(strict_types=1);

namespace PhpLiteCore\View;
use PhpLiteCore\View\Exceptions\ViewNotFoundException;

class Layout extends View
{
    /**
     * The name of the layout file.
     * @var string
     */
    protected string $layout;

    /**
     * Layout constructor.
     *
     * @param string $layout The name of the master layout file.
     * @param string $view The name of the content view file.
     * @param array $data Data to be passed to the view.
     * @param string $theme The theme directory.
     * @throws ViewNotFoundException
     */
    public function __construct(string $layout, string $view, array $data = [], string $theme = 'default')
    {
        $this->layout = $layout;
        parent::__construct($view, $data, $theme);
    }

    /**
     * Renders the layout and injects the content view into it.
     *
     * @return string The fully rendered HTML.
     * @throws ViewNotFoundException
     */
    public function render(): string
    {
        $content = parent::render();
        $layoutPath = $this->buildLayoutPath();

        if (!file_exists($layoutPath)) {
            throw new ViewNotFoundException("Layout file not found: {$layoutPath}");
        }

        extract($this->data);

        ob_start();
        require $layoutPath;
        return ob_get_clean();
    }

    /**
     * Builds the full path to the layout file.
     *
     * @return string
     */
    private function buildLayoutPath(): string
    {
        $pathSegments = [
            PHPLITECORE_ROOT,
            'views',
            'layouts',
            str_replace('.', DIRECTORY_SEPARATOR, $this->layout) . '.php'
        ];
        return implode(DIRECTORY_SEPARATOR, $pathSegments);
    }

    /**
     * Static factory method for convenience, renamed to avoid conflict with parent.
     *
     * @param string $layout
     * @param string $view
     * @param array $data
     * @return string
     * @throws ViewNotFoundException
     */
    public static function create(string $layout, string $view, array $data = []): string
    {
        return (new self($layout, $view, $data))->render();
    }
}