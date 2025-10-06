<?php

namespace App\Controllers;

use Exception;
use phpDocumentor\Reflection\Types\This;
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

        $this->view('about', [
            'pageTitle' => $pageTitle,
            'pageContent' => $content
        ]);
    }
}