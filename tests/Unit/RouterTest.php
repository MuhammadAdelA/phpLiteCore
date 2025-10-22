<?php

use PhpLiteCore\Routing\Router;
use PhpLiteCore\Routing\Route;

describe('Router', function () {
    beforeEach(function () {
        $this->router = new Router();
    });

    test('get() returns Route instance for fluent chaining', function () {
        $route = $this->router->get('/test', ['TestController', 'index']);
        expect($route)->toBeInstanceOf(Route::class);
    });

    test('post() returns Route instance for fluent chaining', function () {
        $route = $this->router->post('/test', ['TestController', 'store']);
        expect($route)->toBeInstanceOf(Route::class);
    });

    test('route can be named using name() method', function () {
        $route = $this->router->get('/test', ['TestController', 'index'])
            ->name('test.index');
        
        expect($route->getName())->toBe('test.index');
    });

    test('route constraints can be set using where() method', function () {
        $route = $this->router->get('/posts/{id}', ['PostController', 'show'])
            ->where(['id' => '[0-9]+']);
        
        expect($route->getConstraints())->toHaveKey('id');
        expect($route->getConstraints()['id'])->toBe('[0-9]+');
    });

    test('route middleware can be set using middleware() method', function () {
        $route = $this->router->get('/secure', ['SecureController', 'index'])
            ->middleware(['AuthMiddleware']);
        
        expect($route->getMiddleware())->toContain('AuthMiddleware');
    });

    test('route middleware accepts single string', function () {
        $route = $this->router->get('/secure', ['SecureController', 'index'])
            ->middleware('AuthMiddleware');
        
        expect($route->getMiddleware())->toContain('AuthMiddleware');
    });

    test('multiple constraints can be set at once', function () {
        $route = $this->router->get('/users/{id}/posts/{postId}', ['UserPostController', 'show'])
            ->where(['id' => '[0-9]+', 'postId' => '[0-9]+']);
        
        expect($route->getConstraints())->toHaveKey('id');
        expect($route->getConstraints())->toHaveKey('postId');
    });

    test('fluent chaining works with multiple methods', function () {
        $route = $this->router->get('/posts/{id}', ['PostController', 'show'])
            ->name('posts.show')
            ->where(['id' => '[0-9]+'])
            ->middleware(['AuthMiddleware']);
        
        expect($route->getName())->toBe('posts.show');
        expect($route->getConstraints()['id'])->toBe('[0-9]+');
        expect($route->getMiddleware())->toContain('AuthMiddleware');
    });

    test('route regex uses constraints when provided', function () {
        $route = $this->router->get('/posts/{id}', ['PostController', 'show'])
            ->where(['id' => '[0-9]+']);
        
        $regex = $route->getRegex();
        expect($regex)->toContain('([0-9]+)');
        expect($regex)->not->toContain('[^/]+');
    });

    test('route regex uses default pattern when no constraints', function () {
        $route = $this->router->get('/posts/{id}', ['PostController', 'show']);
        
        $regex = $route->getRegex();
        expect($regex)->toContain('([^/]+)');
    });

    test('group() creates routes with prefix', function () {
        $this->router->group(['prefix' => 'api'], function ($router) {
            $router->get('/users', ['UserController', 'index']);
        });
        
        // Use reflection to check internal routes
        $reflection = new ReflectionClass($this->router);
        $property = $reflection->getProperty('routes');
        $property->setAccessible(true);
        $routes = $property->getValue($this->router);
        
        expect($routes[0]->getUri())->toBe('/api/users');
    });

    test('group() applies middleware to routes', function () {
        $this->router->group(['middleware' => ['AuthMiddleware']], function ($router) {
            $router->get('/secure', ['SecureController', 'index']);
        });
        
        // Use reflection to check internal routes
        $reflection = new ReflectionClass($this->router);
        $property = $reflection->getProperty('routes');
        $property->setAccessible(true);
        $routes = $property->getValue($this->router);
        
        expect($routes[0]->getMiddleware())->toContain('AuthMiddleware');
    });

    test('nested groups accumulate prefixes', function () {
        $this->router->group(['prefix' => 'api'], function ($router) {
            $router->group(['prefix' => 'v1'], function ($router) {
                $router->get('/users', ['UserController', 'index']);
            });
        });
        
        // Use reflection to check internal routes
        $reflection = new ReflectionClass($this->router);
        $property = $reflection->getProperty('routes');
        $property->setAccessible(true);
        $routes = $property->getValue($this->router);
        
        expect($routes[0]->getUri())->toBe('/api/v1/users');
    });

    test('nested groups accumulate middleware', function () {
        $this->router->group(['middleware' => ['AuthMiddleware']], function ($router) {
            $router->group(['middleware' => ['RoleMiddleware']], function ($router) {
                $router->get('/admin', ['AdminController', 'index']);
            });
        });
        
        // Use reflection to check internal routes
        $reflection = new ReflectionClass($this->router);
        $property = $reflection->getProperty('routes');
        $property->setAccessible(true);
        $routes = $property->getValue($this->router);
        
        expect($routes[0]->getMiddleware())->toContain('AuthMiddleware');
        expect($routes[0]->getMiddleware())->toContain('RoleMiddleware');
    });

    test('route caching can save and load routes', function () {
        // Create routes
        $this->router->get('/test', ['TestController', 'index'])->name('test.index');
        $this->router->get('/posts/{id}', ['PostController', 'show'])
            ->name('posts.show')
            ->where(['id' => '[0-9]+']);
        
        // Save to cache
        $cachePath = '/tmp/test-routes-cache.php';
        $saved = $this->router->saveToCache($cachePath);
        expect($saved)->toBeTrue();
        expect(file_exists($cachePath))->toBeTrue();
        
        // Create new router and load from cache
        $newRouter = new Router();
        $loaded = $newRouter->loadFromCache($cachePath);
        expect($loaded)->toBeTrue();
        
        // Clean up
        if (file_exists($cachePath)) {
            unlink($cachePath);
        }
    });

    test('route caching preserves route metadata', function () {
        // Create route with all features
        $this->router->get('/posts/{id}', ['PostController', 'show'])
            ->name('posts.show')
            ->where(['id' => '[0-9]+'])
            ->middleware(['AuthMiddleware']);
        
        // Save to cache
        $cachePath = '/tmp/test-routes-metadata-cache.php';
        $this->router->saveToCache($cachePath);
        
        // Load into new router
        $newRouter = new Router();
        $newRouter->loadFromCache($cachePath);
        
        // Use reflection to check the loaded route
        $reflection = new ReflectionClass($newRouter);
        $property = $reflection->getProperty('routes');
        $property->setAccessible(true);
        $routes = $property->getValue($newRouter);
        
        expect($routes[0]->getName())->toBe('posts.show');
        expect($routes[0]->getConstraints()['id'])->toBe('[0-9]+');
        expect($routes[0]->getMiddleware())->toContain('AuthMiddleware');
        
        // Clean up
        if (file_exists($cachePath)) {
            unlink($cachePath);
        }
    });

    test('loadFromCache returns false for non-existent file', function () {
        $newRouter = new Router();
        $loaded = $newRouter->loadFromCache('/tmp/non-existent-cache.php');
        expect($loaded)->toBeFalse();
    });
});

describe('Route', function () {
    test('Route stores basic properties', function () {
        $route = new Route('GET', '/test', ['TestController', 'index']);
        
        expect($route->getMethod())->toBe('GET');
        expect($route->getUri())->toBe('/test');
        expect($route->getAction())->toBe(['TestController', 'index']);
    });

    test('Route extracts parameter names from URI', function () {
        $route = new Route('GET', '/posts/{id}/comments/{commentId}', ['CommentController', 'show']);
        
        expect($route->getParams())->toBe(['id', 'commentId']);
    });

    test('Route compiles pattern with constraints', function () {
        $route = new Route('GET', '/posts/{id}', ['PostController', 'show']);
        $route->where(['id' => '[0-9]+']);
        
        $regex = $route->getRegex();
        expect($regex)->toBe('#^/posts/([0-9]+)$#');
    });

    test('Route compiles pattern with default constraint', function () {
        $route = new Route('GET', '/posts/{id}', ['PostController', 'show']);
        
        $regex = $route->getRegex();
        expect($regex)->toBe('#^/posts/([^/]+)$#');
    });

    test('Route compiles pattern with mixed constraints', function () {
        $route = new Route('GET', '/users/{id}/posts/{slug}', ['UserPostController', 'show']);
        $route->where(['id' => '[0-9]+']);
        
        $regex = $route->getRegex();
        expect($regex)->toBe('#^/users/([0-9]+)/posts/([^/]+)$#');
    });

    test('Route toArray includes all metadata', function () {
        $route = new Route('GET', '/posts/{id}', ['PostController', 'show']);
        $route->name('posts.show')
            ->where(['id' => '[0-9]+'])
            ->middleware(['AuthMiddleware']);
        
        $array = $route->toArray();
        
        expect($array)->toHaveKey('method');
        expect($array)->toHaveKey('uri');
        expect($array)->toHaveKey('action');
        expect($array)->toHaveKey('name');
        expect($array)->toHaveKey('constraints');
        expect($array)->toHaveKey('middleware');
        expect($array)->toHaveKey('regex');
        expect($array)->toHaveKey('params');
        
        expect($array['name'])->toBe('posts.show');
        expect($array['constraints']['id'])->toBe('[0-9]+');
        expect($array['middleware'])->toContain('AuthMiddleware');
    });
});
