<?php

// This file returns a function that defines the routes.

/** @var PhpLiteCore\Routing\Router $router */

// --- General Routes ---
$router->get('/', ['HomeController', 'index'])->name('home');
$router->get('/about', ['AboutController', 'index'])->name('about');


// --- Post Routes ---

// (Compliant) Show create form (Must be before dynamic {id})
$router->get('/posts/create', ['PostController', 'create'])->name('posts.create');

// (Compliant) List all posts
$router->get('/posts', ['PostController', 'index'])->name('posts.index');

// --- Edit Routes ---
// Show the edit form for a specific post
$router->get('/posts/{id}/edit', ['PostController', 'edit'])
    ->name('posts.edit')
    ->where(['id' => '[0-9]+']);

// Handle the update submission for a specific post
$router->post('/posts/{id}', ['PostController', 'update'])
    ->name('posts.update')
    ->where(['id' => '[0-9]+']);

// (Compliant) Show a single post (Must be after specific routes like 'create' and 'edit')
$router->get('/posts/{id}', ['PostController', 'show'])
    ->name('posts.show')
    ->where(['id' => '[0-9]+']);

// (Compliant) Store a new post
$router->post('/posts', ['PostController', 'store'])->name('posts.store');


// --- Development Only Routes ---
if (defined('ENV') && ENV === 'development') {
    // Database Layer Tests
    $router->get('/run-db-tests', ['TestController', 'runDbTests'])->name('dev.db-tests');

    // --- NEW: Session Test Routes ---
    $router->get('/test-session-set/{key}/{value}', ['TestController', 'testSessionSet'])
        ->name('dev.session.set')
        ->where(['key' => '[a-zA-Z0-9_]+', 'value' => '[a-zA-Z0-9_]+']);
    
    $router->get('/test-session-get/{key}', ['TestController', 'testSessionGet'])
        ->name('dev.session.get')
        ->where(['key' => '[a-zA-Z0-9_]+']);
    
    $router->get('/test-session-flash-set/{key}/{message}', ['TestController', 'testSessionFlashSet'])
        ->name('dev.session.flash-set')
        ->where(['key' => '[a-zA-Z0-9_]+', 'message' => '[a-zA-Z0-9_]+']);
    
    $router->get('/test-session-flash-get/{key}', ['TestController', 'testSessionFlashGet'])
        ->name('dev.session.flash-get')
        ->where(['key' => '[a-zA-Z0-9_]+']);
    
    $router->get('/test-session-destroy', ['TestController', 'testSessionDestroy'])
        ->name('dev.session.destroy');
    // --- END NEW ---
}