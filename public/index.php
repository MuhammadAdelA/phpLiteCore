<?php

use PhpLiteCore\View\View; // Import the View class

/** @var PhpLiteCore\Bootstrap\Application $app */
$app = require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . "init.php";

$translator = $app->translator;

// 1. Prepare data for the view
$viewData = [
    'pageTitle' => $translator->get('Home Page'),
    'welcome' => $translator->get('welcome'),
    'welcomeMessage' => $translator->get('PhpLiteCore is successfully running!'),
];

// 2. Render the view using the convenient static method
try {
    echo View::make('home', $viewData);
} catch (\Exception $e) {
    http_response_code(500);
    echo "<h1>Error</h1><p>Could not render the page. Details: " . $e->getMessage() . "</p>";
}