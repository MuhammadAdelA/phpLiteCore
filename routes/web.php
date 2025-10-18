<?php

// This file returns a function that defines the routes.

/** @var PhpLiteCore\Routing\Router $router */

// --- General Routes ---
$router->get('/', ['HomeController', 'index']);
$router->get('/about', ['AboutController', 'index']);


// --- Post Routes ---

// (Compliant) Show create form (Must be before dynamic {id})
$router->get('/posts/create', ['PostController', 'create']);

// (Compliant) List all posts
$router->get('/posts', ['PostController', 'index']);

// --- NEW: Edit Routes ---
// Show the edit form for a specific post
$router->get('/posts/{id}/edit', ['PostController', 'edit']);

// Handle the update submission for a specific post
$router->post('/posts/{id}', ['PostController', 'update']);
// --- END NEW ---

// (Compliant) Show a single post (Must be after specific routes like 'create' and 'edit')
$router->get('/posts/{id}', ['PostController', 'show']);

// (Compliant) Store a new post
$router->post('/posts', ['PostController', 'store']);


// IMPORTANT: Only register test routes in the development environment
if (defined('ENV') && ENV === 'development') {
    $router->get('/run-db-tests', ['TestController', 'runDbTests']);
}