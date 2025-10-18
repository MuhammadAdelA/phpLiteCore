<?php

// This file returns a function that defines the routes.
// This approach is clean and prevents variables from leaking into the global scope.

/** @var PhpLiteCore\Routing\Router $router */

// --- General Routes ---
$router->get('/', ['HomeController', 'index']);
$router->get('/about', ['AboutController', 'index']);


// --- Post Routes ---

// (FIX) The 'create' route MUST be defined *before* the dynamic '{id}' route
// to ensure '/posts/create' is not captured as an ID.
$router->get('/posts/create', ['PostController', 'create']);

// (Compliant) List all posts
$router->get('/posts', ['PostController', 'index']);

// (Compliant) Show a single post (now correctly handles IDs only)
$router->get('/posts/{id}', ['PostController', 'show']);

// (Compliant) Store a new post
$router->post('/posts', ['PostController', 'store']);

// IMPORTANT: Only register test routes in the development environment
if (defined('ENV') && ENV === 'development') {
    $router->get('/run-db-tests', ['TestController', 'runDbTests']);
}