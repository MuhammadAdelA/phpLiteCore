<?php
declare(strict_types=1);

// 1. Define project root
use Dotenv\Dotenv;
use PhpLiteCore\Lang\Translator;

if (! defined('PHPLITECORE_ROOT')) {
    define('PHPLITECORE_ROOT', __DIR__ . DIRECTORY_SEPARATOR);
}

// 2. Display all errors (dev)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 3. Autoload
require PHPLITECORE_ROOT . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

$dotenv = Dotenv::createImmutable(PHPLITECORE_ROOT);
$dotenv->load();

define('ENV', $_ENV['APP_ENV'] ?: 'production');
define('SYSTEM_TIMEZONE', $_ENV['SYSTEM_TIMEZONE'] ?: 'UTC');
define('DEFAULT_LANG', $_ENV['DEFAULT_LANG'] ?: 'en');
date_default_timezone_set(SYSTEM_TIMEZONE);

switch (ENV) {
    case 'production':
        ini_set('display_errors', '0');
        error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
        ini_set('log_errors', '1');
        ini_set('error_log', PHPLITECORE_ROOT . 'storage/logs/php-error.log');
        break;

    case 'development':
    default:
        ini_set('display_errors', '1');
        error_reporting(E_ALL);
        ini_set('log_errors', '1');
        break;
}

// 4. Determine locale
$locale = set_language() ?: DEFAULT_LANG;
define('HTML_DIR', getDirection(DEFAULT_LANG) ?: 'ltr');

// 5. Instantiate Translator
return $translator = new Translator($locale);

// 6. (Optional) expose $translator globally
