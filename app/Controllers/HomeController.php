<?php

namespace App\Controllers;

use Exception; // Exception is no longer thrown directly from here
use App\Models\User;
use PhpLiteCore\View\View;

class HomeController extends BaseController
{
    /**
     * @throws Exception
     */
    public function index(): void
    {
        // 1. Business Logic: Get the data.
        $user = User::find(1);

        // 2. Prepare variables for the view.
        $pageTitle = $this->app->translator->get('Home Page');
        $welcomeMessage = sprintf($this->app->translator->get('welcome'), ($user['name'] ?? 'Guest'));
        $PhpLiteCoreIsUpAndRunning = $this->app->translator->get('phpLiteCore is up and running');

        // 3. Render the view using the new helper and compact().
        // compact() automatically creates an array from your variables.
        $this->view('home', compact(
            'pageTitle',
            'welcomeMessage',
            'PhpLiteCoreIsUpAndRunning'
        ));
    }
}