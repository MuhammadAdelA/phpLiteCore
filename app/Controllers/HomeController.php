<?php

namespace App\Controllers;

use App\Models\User;
use PhpLiteCore\View\Exceptions\ViewNotFoundException;

/**
 * Handles the logic for the application's home page.
 */
class HomeController extends BaseController
{
    /**
     * Show the application startup page.
     * Fetches user data and passes all necessary translated strings to the view.
     *
     * @return void
     * @throws ViewNotFoundException
     */
    public function index(): void
    {
        // 1. Business Logic (Example: Get a user)
        $user = User::find(1);

        // 2. Prepare ALL translated variables for the view using correct keys.
        // Ensure keys match the structure in resources/lang/xx/messages.php

        $pageTitle = $this->app->translator->get('messages.home.page_title', [], 'Welcome to phpLiteCore');

        $heroTitle = $this->app->translator->get('messages.home.hero_title', [], 'Installation Successful!');
        $heroSubtitle = $this->app->translator->get(
            'messages.home.hero_subtitle',
            // Use the top-level 'guest' key for the fallback
            ['name' => $user->name ?? $this->app->translator->get('messages.guest')]
        );
        $heroDescription = $this->app->translator->get('messages.home.hero_description');

        $cardDocsTitle = $this->app->translator->get('messages.home.card_docs_title');
        $cardDocsText = $this->app->translator->get('messages.home.card_docs_text');
        $cardDocsButton = $this->app->translator->get('messages.home.card_docs_button');

        $cardCodeTitle = $this->app->translator->get('messages.home.card_code_title');
        $cardCodeText = $this->app->translator->get('messages.home.card_code_text');
        $cardCodeButton = $this->app->translator->get('messages.home.card_code_button');

        $cardCommunityTitle = $this->app->translator->get('messages.home.card_community_title');
        $cardCommunityText = $this->app->translator->get('messages.home.card_community_text');
        $cardCommunityButton = $this->app->translator->get('messages.home.card_community_button');

        $frameworkRunning = $this->app->translator->get('messages.framework_running');
        $versionLabel = $this->app->translator->get('messages.home.version_label');


        // 3. Render the view, passing all final translated strings.
        $this->view('home', compact(
            'pageTitle',
            'heroTitle',
            'heroSubtitle',
            'heroDescription',
            'cardDocsTitle',
            'cardDocsText',
            'cardDocsButton',
            'cardCodeTitle',
            'cardCodeText',
            'cardCodeButton',
            'cardCommunityTitle',
            'cardCommunityText',
            'cardCommunityButton',
            'frameworkRunning',
            'versionLabel'
        ));
    }
}
