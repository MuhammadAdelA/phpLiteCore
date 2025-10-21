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

// --- Edit Routes ---
// Show the edit form for a specific post
$router->get('/posts/{id}/edit', ['PostController', 'edit']);

// Handle the update submission for a specific post
$router->post('/posts/{id}', ['PostController', 'update']);

// (Compliant) Show a single post (Must be after specific routes like 'create' and 'edit')
$router->get('/posts/{id}', ['PostController', 'show']);

// (Compliant) Store a new post
$router->post('/posts', ['PostController', 'store']);


// --- Development Only Routes ---
if (defined('ENV') && ENV === 'development') {
    // Database Layer Tests
    $router->get('/run-db-tests', ['TestController', 'runDbTests']);

    // --- NEW: Session Test Routes ---
    $router->get('/test-session-set/{key}/{value}', ['TestController', 'testSessionSet']);
    $router->get('/test-session-get/{key}', ['TestController', 'testSessionGet']);
    $router->get('/test-session-flash-set/{key}/{message}', ['TestController', 'testSessionFlashSet']);
    $router->get('/test-session-flash-get/{key}', ['TestController', 'testSessionFlashGet']);
    $router->get('/test-session-destroy', ['TestController', 'testSessionDestroy']);
    // --- END NEW ---
}