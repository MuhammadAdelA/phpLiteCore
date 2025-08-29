<?php
declare(strict_types=1);

use PhpLiteCore\Bootstrap\Application;

// 1. Define project root
if (! defined('PHPLITECORE_ROOT')) {
    define('PHPLITECORE_ROOT', __DIR__ . DIRECTORY_SEPARATOR);
}

// 2. Autoload
require PHPLITECORE_ROOT . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

// 3. Load helper functions (assuming it's done via composer.json)

// 4. Create and return the Application instance
return Application::getInstance();