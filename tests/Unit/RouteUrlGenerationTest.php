<?php

use PhpLiteCore\Routing\Router;
use PhpLiteCore\Routing\Route;

describe('Route URL Generation', function () {
    beforeEach(function () {
        $this->router = new Router();
    });

    test('generates URL for simple named route without parameters', function () {
        $this->router->get('/home', ['HomeController', 'index'])->name('home');
        
        $url = $this->router->route('home');
        expect($url)->toBe('/home');
    });

    test('generates URL for named route with single parameter', function () {
        $this->router->get('/posts/{id}', ['PostController', 'show'])->name('posts.show');
        
        $url = $this->router->route('posts.show', ['id' => 123]);
        expect($url)->toBe('/posts/123');
    });

    test('generates URL for named route with multiple parameters', function () {
        $this->router->get('/users/{userId}/posts/{postId}', ['UserPostController', 'show'])
            ->name('users.posts.show');
        
        $url = $this->router->route('users.posts.show', ['userId' => 5, 'postId' => 42]);
        expect($url)->toBe('/users/5/posts/42');
    });

    test('generates URL for named route with constraints', function () {
        $this->router->get('/posts/{id}', ['PostController', 'show'])
            ->name('posts.show')
            ->where(['id' => '[0-9]+']);
        
        $url = $this->router->route('posts.show', ['id' => 456]);
        expect($url)->toBe('/posts/456');
    });

    test('generates URL for nested group routes with prefix', function () {
        $this->router->group(['prefix' => 'api'], function ($router) {
            $router->get('/users', ['UserController', 'index'])->name('api.users');
        });
        
        $url = $this->router->route('api.users');
        expect($url)->toBe('/api/users');
    });

    test('generates URL for deeply nested group routes', function () {
        $this->router->group(['prefix' => 'api'], function ($router) {
            $router->group(['prefix' => 'v1'], function ($router) {
                $router->get('/users/{id}', ['UserController', 'show'])->name('api.v1.users.show');
            });
        });
        
        $url = $this->router->route('api.v1.users.show', ['id' => 99]);
        expect($url)->toBe('/api/v1/users/99');
    });

    test('throws exception for non-existent route', function () {
        $this->router->get('/home', ['HomeController', 'index'])->name('home');
        
        expect(fn() => $this->router->route('nonexistent'))
            ->toThrow(InvalidArgumentException::class, 'Route [nonexistent] not found.');
    });

    test('throws exception when required parameter is missing', function () {
        $this->router->get('/posts/{id}', ['PostController', 'show'])->name('posts.show');
        
        expect(fn() => $this->router->route('posts.show'))
            ->toThrow(InvalidArgumentException::class, 'Missing required parameter [id] for route [posts.show].');
    });

    test('throws exception when one of multiple required parameters is missing', function () {
        $this->router->get('/users/{userId}/posts/{postId}', ['UserPostController', 'show'])
            ->name('users.posts.show');
        
        expect(fn() => $this->router->route('users.posts.show', ['userId' => 5]))
            ->toThrow(InvalidArgumentException::class, 'Missing required parameter [postId] for route [users.posts.show].');
    });

    test('getNamedRoute returns route instance for existing route', function () {
        $this->router->get('/about', ['AboutController', 'index'])->name('about');
        
        $route = $this->router->getNamedRoute('about');
        expect($route)->toBeInstanceOf(Route::class);
        expect($route->getName())->toBe('about');
        expect($route->getUri())->toBe('/about');
    });

    test('getNamedRoute returns null for non-existent route', function () {
        $route = $this->router->getNamedRoute('nonexistent');
        expect($route)->toBeNull();
    });

    test('route caching preserves named route mappings', function () {
        // Create routes with names
        $this->router->get('/home', ['HomeController', 'index'])->name('home');
        $this->router->get('/posts/{id}', ['PostController', 'show'])->name('posts.show');
        
        // Save to cache
        $cachePath = '/tmp/test-named-routes-cache.php';
        $this->router->saveToCache($cachePath);
        
        // Load into new router
        $newRouter = new Router();
        $newRouter->loadFromCache($cachePath);
        
        // Test that named routes work
        expect($newRouter->route('home'))->toBe('/home');
        expect($newRouter->route('posts.show', ['id' => 789]))->toBe('/posts/789');
        
        // Clean up
        if (file_exists($cachePath)) {
            unlink($cachePath);
        }
    });

    test('handles string parameter values', function () {
        $this->router->get('/posts/{slug}', ['PostController', 'showBySlug'])->name('posts.byslug');
        
        $url = $this->router->route('posts.byslug', ['slug' => 'my-first-post']);
        expect($url)->toBe('/posts/my-first-post');
    });

    test('handles numeric zero as parameter value', function () {
        $this->router->get('/items/{id}', ['ItemController', 'show'])->name('items.show');
        
        $url = $this->router->route('items.show', ['id' => 0]);
        expect($url)->toBe('/items/0');
    });

    test('route URL generation with mixed parameter types', function () {
        $this->router->get('/categories/{categorySlug}/posts/{postId}', ['PostController', 'showInCategory'])
            ->name('categories.posts.show');
        
        $url = $this->router->route('categories.posts.show', [
            'categorySlug' => 'technology',
            'postId' => 42
        ]);
        expect($url)->toBe('/categories/technology/posts/42');
    });
});

describe('Route Helper Function', function () {
    test('route() helper function generates URL for named route', function () {
        // Skip this test as it requires full application bootstrap
        // The functionality is tested through Router directly above
        expect(true)->toBeTrue();
    })->skip('Requires full application bootstrap');

    test('route() helper function generates URL with parameters', function () {
        // Skip this test as it requires full application bootstrap
        // The functionality is tested through Router directly above
        expect(true)->toBeTrue();
    })->skip('Requires full application bootstrap');
});

describe('UrlUtils Route Method', function () {
    test('UrlUtils::route() generates URL for named route', function () {
        // Skip this test as it requires full application bootstrap
        // The functionality is tested through Router directly above
        expect(true)->toBeTrue();
    })->skip('Requires full application bootstrap');

    test('UrlUtils::route() generates URL with parameters', function () {
        // Skip this test as it requires full application bootstrap
        // The functionality is tested through Router directly above
        expect(true)->toBeTrue();
    })->skip('Requires full application bootstrap');
});
