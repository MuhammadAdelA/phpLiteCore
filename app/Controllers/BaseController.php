<?php

namespace App\Controllers;

use PhpLiteCore\Bootstrap\Application;
use PhpLiteCore\View\Exceptions\ViewNotFoundException;
use PhpLiteCore\View\Layout;

abstract class BaseController
{
    /**
     * The main application instance.
     * @var Application
     */
    protected Application $app;

    /**
     * BaseController constructor.
     *
     * @param Application $app The application instance.
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Renders a view within the main layout.
     *
     * @param string $view The name of the view file.
     * @param array $data The data to pass to the view.
     * @return void
     * @throws ViewNotFoundException
     */
    protected function view(string $view, array $data = []): void
    {
        // It calls the correctly named Layout::create
        echo Layout::create('app', $view, $data);
    }

    /**
     * Renders a partial view.
     *
     * @param string $partial The name of the partial view file.
     * @param array $data Data to make available to the partial.
     * @return void
     */
    protected function partial(string $partial, array $data = []): void
    {
        extract($data);
        $path = PHPLITECORE_ROOT . 'views' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . $partial . '.php';

        if (file_exists($path)) {
            require $path;
        }
    }
}
