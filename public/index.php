<?php

/**
 * phpLiteCore - A lightweight PHP framework.
 *
 * @var PhpLiteCore\Bootstrap\Application $app
 */
$app = require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . "init.php";

// Define a variable to be used inside the routes file.
$router = $app->router;

// Load the routes' definition.
require_once PHPLITECORE_ROOT . 'Routes/web.php';

// Dispatch the router to handle the request, passing the application instance.
$app->router->dispatch($app);