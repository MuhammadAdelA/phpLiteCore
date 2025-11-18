<?php

declare(strict_types=1);

// Import the Application class
use PhpLiteCore\Bootstrap\Application;

// This file's sole responsibility is now to get and return the Application instance.
// Autoloading and root constant definition are handled by the entry point (index.php).
return Application::getInstance();
