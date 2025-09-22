<?php

namespace App\Controllers;

use Exception;
use PhpLiteCore\View\View;
class AboutController extends BaseController
{
    /**
     * @throws Exception
     */
    public function index(): void
    {
        // Using the translator service available via $this->app
        $pageTitle = $this->app->translator->get('About Us');
        $content = $this->app->translator->get('This is the about us page, powered by phpLiteCore.');

        echo View::make('about', [
            'pageTitle' => $pageTitle,
            'pageContent' => $content
        ]);
    }
}