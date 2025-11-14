<?php
declare(strict_types=1);

namespace PhpLiteCore\View;

use App\ViewComposers\NavigationComposer; // Import the composer
use PhpLiteCore\Bootstrap\Application;     // Import Application to pass to composer
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
        // Call the parent View constructor first to set up the content view path and initial data
        parent::__construct($view, $data, $theme);
        // Store the layout name
        $this->layout = $layout;
    }

    /**
     * Renders the layout and injects the content view into it.
     * Executes associated View Composers before rendering the content view.
     *
     * @return string The fully rendered HTML.
     * @throws ViewNotFoundException
     */
    public function render(): string
    {
        // --- 1. Execute View Composers BEFORE rendering the content view ---
        // Get the application instance (needed for the composer's dependencies, e.g., translator)
        $app = Application::getInstance();

        // Define composers associated with specific layouts.
        // In a more complex system, this mapping could come from a configuration file.
        $composers = [
            // If the layout requested is 'app', use the NavigationComposer.
            'app' => NavigationComposer::class,
        ];

        // Check if a composer is defined for the *current* layout being rendered.
        if (isset($composers[$this->layout])) {
            $composerClass = $composers[$this->layout];
            // Ensure the specified composer class actually exists.
            if (class_exists($composerClass)) {
                // Instantiate the composer, passing the Application instance (dependency injection).
                $composerInstance = new $composerClass($app);
                // Call the 'compose' method on the composer instance.
                // Pass the *current* view data ($this->data) to the composer.
                // The composer modifies the data (e.g., adds navigation links) and returns the updated array.
                // Overwrite $this->data with the data returned by the composer.
                $this->data = $composerInstance->compose($this->data);
            } else {
                // Log an error or throw an exception if the composer class is configured but not found.
                error_log("View Composer class not found: {$composerClass}");
            }
        }
        // --- END Composer Execution ---

        // 2. Render the content view using the parent's render method.
        // The composer data is now available in $this->data, so it will be passed to the content view.
        // The result is stored in $content.
        $content = parent::render();

        // 3. Find the path to the layout file itself.
        $layoutPath = $this->buildLayoutPath();
        if (!file_exists($layoutPath)) {
            // Throw an exception if the layout file (e.g., 'views/layouts/app.php') doesn't exist.
            throw new ViewNotFoundException("Layout file not found: {$layoutPath}");
        }

        // 4. Extract the final data array ($this->data now includes composer data) into individual variables.
        // These variables ($pageTitle, $navHome, etc.) become available within the layout file's scope.
        extract($this->data);

        // 5. Render the layout file using output buffering.
        ob_start();
        // The layout file (e.g., app.php) now has access to:
        // - $content (the rendered output of the original view, e.g., home.php)
        // - All variables extracted from the final $this->data (including those added by the composer).
        require $layoutPath;
        // Return the captured output from the layout file.
        return ob_get_clean();
    }

    /**
     * Builds the full path to the layout file based on the layout name.
     * (Correctly placed in views/layouts directory as per Constitution Sec 3)
     *
     * @return string The absolute path to the layout file.
     */
    private function buildLayoutPath(): string
    {
        $pathSegments = [
            PHPLITECORE_ROOT, // The project root directory constant
            'views',          // The main views directory
            'layouts',        // The subdirectory for layout files
            // Convert dot notation in layout name to directory separators if needed (e.g., 'admin.app')
            // and append '.php' extension.
            str_replace('.', DIRECTORY_SEPARATOR, $this->layout) . '.php'
        ];
        // Combine the segments into a full path.
        return implode(DIRECTORY_SEPARATOR, $pathSegments);
    }

    /**
     * Static factory method for convenience.
     * Creates a new Layout instance and renders it immediately.
     * (Renamed to 'create' to avoid conflict with parent's potential 'make' method)
     *
     * @param string $layout The name of the layout file (e.g., 'app').
     * @param string $view The name of the content view file (e.g., 'home').
     * @param array $data Data to pass to the content view.
     * @return string The fully rendered HTML output.
     * @throws ViewNotFoundException If the view or layout file is not found.
     */
    public static function create(string $layout, string $view, array $data = []): string
    {
        // Instantiate the Layout class and immediately call its render method.
        return (new self($layout, $view, $data))->render();
    }
}