<?php

namespace App\Controllers;

use Exception;
use PhpLiteCore\View\Exceptions\ViewNotFoundException;

class AboutController extends BaseController
{
    /**
     * Show the "About Us" page.
     * (Compliant with Translation)
     *
     * @return void
     * @throws ViewNotFoundException
     * @throws Exception
     */
    public function index(): void
    {
        $pageTitle = $this->app->translator->get('messages.about.page_title');
        $content = $this->app->translator->get('messages.about.page_content');

        // 2. Render the view
        $this->view('about', [
            'pageTitle' => $pageTitle,
            'pageContent' => $content,
        ]);
    }
}
