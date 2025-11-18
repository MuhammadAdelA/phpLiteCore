<?php

/**
 * phpLiteCore - A lightweight PHP framework.
 * Front Controller
 */

// 1. Define the absolute path to the project root. This is the most critical step.
// This constant MUST be defined here before any other file is included.
use PhpLiteCore\Bootstrap\Application;

const PHPLITECORE_ROOT = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;

// 2. Load the Composer autoloader.
// This handles autoloading classes and loading files specified in composer.json ("files" section).
require_once PHPLITECORE_ROOT . 'vendor/autoload.php';

// 3. Bootstrap the application by including the init file.
// The init file now simply returns the Application instance.
/** @var Application $app */
$app = require_once PHPLITECORE_ROOT . 'init.php';

// 4. Load the web routes definition.
// We get the router instance from the application object.
$router = $app->router;
require_once PHPLITECORE_ROOT . 'routes/web.php';

// 5. Dispatch the router to handle the current web request.
// The Application instance is passed to make it available to controllers.
$app->router->dispatch($app);
