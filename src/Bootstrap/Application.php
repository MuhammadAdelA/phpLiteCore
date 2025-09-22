<?php
namespace PhpLiteCore\Bootstrap;

use Dotenv\Dotenv;
use PhpLiteCore\Database\Database; // Added for Database service
use PhpLiteCore\Lang\Translator;
use PhpLiteCore\Routing\Router;     // Added for Router service

class Application
{
    /**
     * The single instance of the Application.
     * @var Application|null
     */
    private static ?Application $instance = null;

    /**
     * The Translator instance.
     * @var Translator
     */
    public Translator $translator;

    /**
     * The Database connection instance.
     * @var Database
     */
    public Database $db;

    /**
     * The Router instance.
     * @var Router
     */
    public Router $router;

    /**
     * Private constructor to prevent direct creation.
     */
    private function __construct()
    {
        $this->bootstrap();
    }

    /**
     * Get the single instance of the Application.
     *
     * @return Application
     */
    public static function getInstance(): Application
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Runs the bootstrapping process in the correct order.
     */
    private function bootstrap(): void
    {
        // Step 1: Load environment variables first.
        $dotenv = Dotenv::createImmutable(PHPLITECORE_ROOT);
        $dotenv->load();

        // Step 2: Define constants that depend on the environment variables.
        $this->defineConstants();

        // Step 3: Now, set up error handling using the defined constants.
        $this->setupErrorHandling();

        // Step 4: Set the system timezone.
        date_default_timezone_set(SYSTEM_TIMEZONE);

        // Step 5: Instantiate and register core services.
        $this->registerCoreServices();
    }

    /**
     * Creates instances of the core services and registers them on the application.
     */
    private function registerCoreServices(): void
    {
        // --- Translator Service ---
        $locale = set_language() ?: DEFAULT_LANG;
        define('LANG', $locale);
        define('HTML_DIR', getDirection(LANG) ?: 'ltr');
        $this->translator = new Translator(LANG);

        // --- Database Service ---
        $dbConfig = [
            'host'     => $_ENV['MYSQL_DB_HOST'] ?? 'localhost',
            'port'     => (int) ($_ENV['MYSQL_DB_PORT'] ?? 3306),
            'database' => $_ENV['MYSQL_DB_NAME'],
            'username' => $_ENV['MYSQL_DB_USER'],
            'password' => $_ENV['MYSQL_DB_PASS'],
            'charset'  => $_ENV['MYSQL_DB_CHAR'] ?? 'utf8mb4',
        ];
        $this->db = new Database($dbConfig);

        // --- Router Service ---
        $this->router = new Router();
    }

    /**
     * Configures error reporting based on the application environment.
     */
    private function setupErrorHandling(): void
    {
        ini_set('display_errors', '1');
        error_reporting(E_ALL);
        ini_set('log_errors', '1');

        // Use the ENV constant as it's guaranteed to be defined at this point.
        if (defined('ENV') && ENV === 'production') {
            ini_set('display_errors', '0');
            ini_set('error_log', PHPLITECORE_ROOT . 'storage/logs/php-error.log');
        }
    }

    /**
     * Defines global constants from environment variables.
     */
    private function defineConstants(): void
    {
        define('ENV', $_ENV['APP_ENV'] ?? 'production');
        define('SYSTEM_TIMEZONE', $_ENV['SYSTEM_TIMEZONE'] ?? 'UTC');
        define('DEFAULT_LANG', $_ENV['DEFAULT_LANG'] ?? 'en');
    }
}