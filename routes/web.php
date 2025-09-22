<?php

// This file returns a function that defines the routes.
// This approach is clean and prevents variables from leaking into the global scope.

/** @var PhpLiteCore\Routing\Router $router */

$router->get('/', ['HomeController', 'index']);
$router->get('/about', ['AboutController', 'index']);


$router->get('/posts', ['PostController', 'index']);
$router->get('/posts/{id}', ['PostController', 'show']);
