<?php

namespace App\Controllers;

use App\Models\User;
use PhpLiteCore\View\Exceptions\ViewNotFoundException;

class HomeController extends BaseController
{
    /**
     * Show the application home page.
     * Fetches user data and passes translated strings to the view.
     *
     * @return void
     * @throws ViewNotFoundException
     */
    public function index(): void
    {
        // 1. Business Logic: Get the data.
        $user = User::find(1); // Fetches the user with ID 1.

        // 2. Prepare translated variables for the view using short keys.

        // Use a descriptive key for the page title.
        $pageTitle = $this->app->translator->get('messages.page_title_home', [], 'Home Page'); // Added default value

        // Use the translator's second argument for placeholder replacement.
        // Removed the incorrect sprintf().
        $welcomeMessage = $this->app->translator->get(
            'messages.welcome', // The translation key
            ['name' => ($user->name ?? 'Guest')] // The data for placeholder replacement
        );

        // Use the correct short key for the framework status message.
        $PhpLiteCoreIsUpAndRunning = $this->app->translator->get('messages.framework_running');

        // 3. Render the view using the BaseController helper and compact().
        $this->view('home', compact(
            'pageTitle',
            'welcomeMessage',
            'PhpLiteCoreIsUpAndRunning'
        ));
    }
}