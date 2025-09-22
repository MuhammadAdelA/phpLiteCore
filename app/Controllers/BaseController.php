<?php

namespace App\Controllers;

use Exception;
use PhpLiteCore\Bootstrap\Application;
use PhpLiteCore\View\View;

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
     * Renders a view and passes data to it.
     * This is a helper to abstract the View::make call.
     *
     * @param string $view The name of the view file.
     * @param array $data The data to pass to the view.
     * @return void
     * @throws Exception if the view file is not found.
     */
    protected function view(string $view, array $data = []): void
    {
        echo View::make($view, $data);
    }
}