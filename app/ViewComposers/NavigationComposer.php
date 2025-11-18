<?php

namespace App\ViewComposers;

use PhpLiteCore\Bootstrap\Application;

class NavigationComposer
{
    /**
     * The application instance.
     * @var Application
     */
    protected Application $app;

    /**
     * Constructor
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Compose the view data.
     * This method will be called by the Layout class.
     *
     * @param array $data The existing view data array.
     * @return array The modified view data array with navigation links.
     */
    public function compose(array $data): array
    {
        // Fetch translated navigation links
        $navData = [
            'navHome' => $this->app->translator->get('messages.nav.home'),
            'navPosts' => $this->app->translator->get('messages.nav.posts'),
            'navCreatePost' => $this->app->translator->get('messages.nav.create_post'),
            'navAbout' => $this->app->translator->get('messages.nav.about'),
        ];

        // Merge navigation data with existing data
        // (Existing data takes precedence if keys conflict)
        return array_merge($navData, $data);
    }
}
