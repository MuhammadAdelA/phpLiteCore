<?php
namespace PhpLiteCore\Bootstrap;

use Dotenv\Dotenv;
use PhpLiteCore\Auth\Auth;       // 1. Added use statement
use PhpLiteCore\Database\Database;
use PhpLiteCore\Database\Model\BaseModel;
use PhpLiteCore\Lang\Translator;
use PhpLiteCore\Routing\Router;
use PhpLiteCore\Session\Session;
use PhpLiteCore\Validation\Validator;

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
     * The Session service instance.
     * @var Session
     */
    public Session $session;

    /**
     * The Auth service instance. (NEW)
     * @var Auth
     */
    public Auth $auth; // 2. Added auth property

    /**
     * Private constructor to prevent direct creation.
     * Ensures singleton pattern and runs bootstrapping process.
     */
    private function __construct()
    {
        // Register this instance as the singleton immediately.
        self::$instance = $this;

        // Boot the BaseModel with the application instance FIRST.
        // Needs to happen before potential model usage during bootstrapping.
        BaseModel::setApp($this);

        // Now, proceed with the rest of the bootstrap process.
        $this->bootstrap();
    }

    /**
     * Get the single instance of the Application (Singleton pattern).
     *
     * @return Application
     */
    public static function getInstance(): Application
    {
        // If instance doesn't exist, create it (triggers private constructor)
        return self::$instance ?? new self();
    }

    /**
     * Runs the main bootstrapping sequence in the correct order.
     * Loads environment variables, defines constants, sets up error handling,
     * configures timezone, and registers core services.
     * @return void
     */
    private function bootstrap(): void
    {
        // Load .env variables into $_ENV superglobal
        $dotenv = Dotenv::createImmutable(PHPLITECORE_ROOT);
        $dotenv->load();

        // Define global constants based on environment variables
        $this->defineConstants();

        // Configure PHP error reporting and logging based on APP_ENV
        $this->setupErrorHandling();

        // Set the default timezone for date/time functions
        // Uses SYSTEM_TIMEZONE from .env or defaults to UTC
        date_default_timezone_set(SYSTEM_TIMEZONE);

        // Instantiate and register core services (Session, Translator, DB, Auth, Router)
        $this->registerCoreServices();
    }

    /**
     * Creates instances of the core services and registers them as public properties.
     * Ensures services are instantiated in the correct order based on dependencies.
     * @return void
     */
    private function registerCoreServices(): void
    {
        // --- Session Service ---
        // Must be started *before* any output is sent by the application.
        // It reads SESSION_* variables from $_ENV, so Dotenv must be loaded first.
        // It also sets cookies, which requires headers not to be sent.
        $this->session = new Session();

        // --- Translator Service ---
        // Determines language (using set_language helper) which might set cookies/redirect.
        // Defines LANG and HTML_DIR constants.
        // Depends on DEFAULT_LANG constant defined after dotenv load.
        $locale = set_language() ?: DEFAULT_LANG; // set_language() needs Session to be active for cookies
        // Define constants *after* set_language might have redirected or set cookies.
        if (!defined('LANG')) define('LANG', $locale);
        if (!defined('HTML_DIR')) define('HTML_DIR', getDirection(LANG) ?: 'ltr');
        // Instantiate Translator with the determined locale.
        $this->translator = new Translator(LANG);

        // --- Inject Translator into Validator Service ---
        // Validator uses the Translator for error messages.
        // Set statically as Validator is used statically.
        Validator::setTranslator($this->translator);

        // --- Database Service ---
        // Reads database credentials from $_ENV.
        $dbConfig = [
            'host'     => $_ENV['MYSQL_DB_HOST'] ?? 'localhost',
            'port'     => (int) ($_ENV['MYSQL_DB_PORT'] ?? 3306),
            'database' => $_ENV['MYSQL_DB_NAME'] ?? '', // Provide default or ensure it's set
            'username' => $_ENV['MYSQL_DB_USER'] ?? '', // Provide default or ensure it's set
            'password' => $_ENV['MYSQL_DB_PASS'] ?? '', // Provide default or ensure it's set
            'charset'  => $_ENV['MYSQL_DB_CHAR'] ?? 'utf8mb4',
        ];
        // Ensure required DB config is present, maybe throw exception if not in development?
        if (empty($dbConfig['database']) || empty($dbConfig['username'])) {
            error_log("Database configuration missing in .env file.");
            // Consider throwing exception in development
            // if (ENV === 'development') {
            //     throw new \InvalidArgumentException("Missing required database configuration in .env (MYSQL_DB_NAME, MYSQL_DB_USER)");
            // }
        }
        $this->db = new Database($dbConfig); // Instantiate Database

        // --- Auth Service (Depends on Session AND Database) ---
        // Pass both required services to the Auth constructor
        $this->auth = new Auth($this->session, $this->db); // 3. Added Auth instantiation

        // --- Router Service ---
        // No direct dependencies on other services during instantiation.
        $this->router = new Router();
    }

    /**
     * Configures PHP error reporting based on the application environment (APP_ENV).
     * Shows all errors in development, hides them and logs to file in production.
     * @return void
     */
    private function setupErrorHandling(): void
    {
        // Default: Show all errors
        ini_set('display_errors', '1');
        error_reporting(E_ALL);
        // Ensure errors are logged regardless of display settings
        ini_set('log_errors', '1');
        ini_set('error_log', PHPLITECORE_ROOT . 'storage/logs/php-error.log'); // Default log file

        // If in production environment, hide errors from the user
        if (defined('ENV') && ENV === 'production') {
            ini_set('display_errors', '0');
            // Ensure log path exists if modified or keep default
        }
    }

    /**
     * Defines global constants from critical environment variables.
     * Ensures these constants are available throughout the application lifecycle.
     * @return void
     */
    private function defineConstants(): void
    {
        // Define APP_ENV, default to 'production' if not set
        if (!defined('ENV')) define('ENV', $_ENV['APP_ENV'] ?? 'production');
        // Define default timezone, default to 'UTC' if not set
        if (!defined('SYSTEM_TIMEZONE')) define('SYSTEM_TIMEZONE', $_ENV['SYSTEM_TIMEZONE'] ?? 'UTC');
        // Define default language, default to 'en' if not set
        if (!defined('DEFAULT_LANG')) define('DEFAULT_LANG', $_ENV['DEFAULT_LANG'] ?? 'en');
    }
}