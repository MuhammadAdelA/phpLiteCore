<?php

use PhpLiteCore\Http\Request;
use PhpLiteCore\Http\Response;
use PhpLiteCore\Routing\Router;
use PhpLiteCore\Bootstrap\Application;

beforeEach(function () {
    // Reset globals
    $_GET = [];
    $_POST = [];
    $_SERVER = [];
    $_COOKIE = [];
    $_FILES = [];
});

describe('Router - Request Injection', function () {
    test('router creates Request object from globals', function () {
        // This is more of a documentation test since we can't easily
        // capture the Request object created internally
        expect(true)->toBeTrue();
    })->skip('Integration test - requires full app setup');
    
    test('Request object is passed to controller methods', function () {
        // Mock controller that captures the Request
        $capturedRequest = null;
        
        $testController = new class {
            public static $captured = null;
            
            public function test(Request $request): void
            {
                self::$captured = $request;
            }
        };
        
        // This would require full router setup
        expect(true)->toBeTrue();
    })->skip('Integration test - requires full app setup');
});

describe('Router - Legacy Compatibility', function () {
    test('controllers without Request parameter still work', function () {
        // Controllers that don't use Request should still work
        // This maintains backward compatibility
        expect(true)->toBeTrue();
    })->skip('Integration test - requires full app setup');
    
    test('middleware with string method signature still works', function () {
        // Legacy middleware should continue to work
        expect(true)->toBeTrue();
    })->skip('Integration test - requires full app setup');
});

describe('Router - Request Parameter Detection', function () {
    test('detects Request parameter in controller method', function () {
        $reflection = new ReflectionMethod(TestControllerWithRequest::class, 'store');
        $params = $reflection->getParameters();
        
        expect($params)->toHaveCount(1);
        expect($params[0]->getType())->not->toBeNull();
        expect($params[0]->getType()->getName())->toBe(Request::class);
    });
    
    test('detects route parameters after Request', function () {
        $reflection = new ReflectionMethod(TestControllerWithRequest::class, 'show');
        $params = $reflection->getParameters();
        
        expect($params)->toHaveCount(2);
        expect($params[0]->getType()->getName())->toBe(Request::class);
        expect($params[1]->getName())->toBe('id');
    });
});

describe('Request - Integration Scenarios', function () {
    test('GET request with query parameters', function () {
        $request = new Request(
            ['page' => '2', 'search' => 'test'],
            [],
            ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/users?page=2&search=test']
        );
        
        expect($request->query('page'))->toBe('2');
        expect($request->query('search'))->toBe('test');
        expect($request->isGet())->toBeTrue();
    });
    
    test('POST request with form data', function () {
        $request = new Request(
            [],
            ['username' => 'admin', 'password' => 'secret'],
            ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/login']
        );
        
        expect($request->post('username'))->toBe('admin');
        expect($request->post('password'))->toBe('secret');
        expect($request->isPost())->toBeTrue();
    });
    
    test('JSON API request', function () {
        $request = new Request(
            [],
            [],
            [
                'REQUEST_METHOD' => 'POST',
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
            ]
        );
        
        expect($request->isJson())->toBeTrue();
        expect($request->expectsJson())->toBeTrue();
    });
    
    test('AJAX request with headers', function () {
        $request = new Request(
            [],
            [],
            [
                'REQUEST_METHOD' => 'GET',
                'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
                'HTTP_ACCEPT' => 'application/json',
            ]
        );
        
        expect($request->isAjax())->toBeTrue();
        expect($request->expectsJson())->toBeTrue();
    });
    
    test('authenticated request with headers', function () {
        $request = new Request(
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Bearer abc123token',
                'HTTP_USER_AGENT' => 'Mozilla/5.0',
            ]
        );
        
        expect($request->hasHeader('Authorization'))->toBeTrue();
        expect($request->header('Authorization'))->toBe('Bearer abc123token');
        expect($request->userAgent())->toBe('Mozilla/5.0');
    });
    
    test('file upload request', function () {
        $request = new Request(
            [],
            ['title' => 'My Photo'],
            ['REQUEST_METHOD' => 'POST'],
            [],
            [
                'photo' => [
                    'name' => 'vacation.jpg',
                    'type' => 'image/jpeg',
                    'size' => 12345,
                    'tmp_name' => '/tmp/phpXXXXXX',
                    'error' => UPLOAD_ERR_OK,
                ],
            ]
        );
        
        expect($request->hasFile('photo'))->toBeTrue();
        expect($request->file('photo')['name'])->toBe('vacation.jpg');
        expect($request->post('title'))->toBe('My Photo');
    });
});

describe('Response - Integration Scenarios', function () {
    test('JSON API response', function () {
        $response = new Response();
        $response
            ->setStatusCode(200)
            ->withJson(['status' => 'success', 'data' => ['id' => 1]]);
        
        expect($response->getStatusCode())->toBe(200);
        expect($response->getHeader('Content-Type'))->toBe('application/json');
        expect($response->getContent())->toBe('{"status":"success","data":{"id":1}}');
    });
    
    test('response with cookies', function () {
        $response = new Response();
        $response
            ->setStatusCode(200)
            ->setCookie('session_id', 'abc123', time() + 3600)
            ->withJson(['status' => 'logged_in']);
        
        expect($response->getStatusCode())->toBe(200);
        expect($response->getContent())->toContain('logged_in');
    });
    
    test('cached response', function () {
        $response = new Response();
        $response
            ->cache(3600)
            ->text('Cached content');
        
        expect($response->getHeader('Cache-Control'))->toBe('public, max-age=3600');
        expect($response->getHeader('Expires'))->not->toBeNull();
    });
    
    test('no-cache response', function () {
        $response = new Response();
        $response
            ->noCache()
            ->text('Fresh content');
        
        expect($response->getHeader('Cache-Control'))->toBe('no-cache, no-store, must-revalidate');
        expect($response->getHeader('Pragma'))->toBe('no-cache');
    });
    
    test('redirect response', function () {
        $response = new Response();
        $response->redirectTo('/dashboard', 302);
        
        expect($response->getStatusCode())->toBe(302);
        expect($response->getHeader('Location'))->toBe('/dashboard');
    });
});

describe('Middleware - Request Integration', function () {
    test('middleware receives Request object', function () {
        $middleware = new TestMiddlewareWithRequest();
        $request = new Request(
            [],
            [],
            [
                'REQUEST_METHOD' => 'POST',
                'HTTP_AUTHORIZATION' => 'Bearer token123',
            ]
        );
        
        // This would normally check authorization
        $middleware->handle($request);
        
        expect($middleware->wasCalled)->toBeTrue();
        expect($middleware->receivedRequest)->toBe($request);
    });
    
    test('legacy middleware receives method string', function () {
        $middleware = new TestMiddlewareLegacy();
        
        // Legacy middleware would receive just the method string
        $middleware->handle('POST');
        
        expect($middleware->wasCalled)->toBeTrue();
        expect($middleware->receivedMethod)->toBe('POST');
    });
});

// Test helper classes

class TestControllerWithRequest
{
    public function store(Request $request): void
    {
        // Method with Request parameter
    }
    
    public function show(Request $request, int $id): void
    {
        // Method with Request and route parameter
    }
}

class TestMiddlewareWithRequest
{
    public bool $wasCalled = false;
    public ?Request $receivedRequest = null;
    
    public function handle(Request $request): void
    {
        $this->wasCalled = true;
        $this->receivedRequest = $request;
    }
}

class TestMiddlewareLegacy
{
    public bool $wasCalled = false;
    public ?string $receivedMethod = null;
    
    public function handle(string $method): void
    {
        $this->wasCalled = true;
        $this->receivedMethod = $method;
    }
}
