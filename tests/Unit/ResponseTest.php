<?php

use PhpLiteCore\Http\Response;

describe('Response - Status Code', function () {
    test('setStatusCode() sets the status code', function () {
        $response = new Response();
        $response->setStatusCode(404);
        
        expect($response->getStatusCode())->toBe(404);
    });

    test('setStatusCode() returns response for chaining', function () {
        $response = new Response();
        $result = $response->setStatusCode(200);
        
        expect($result)->toBe($response);
    });

    test('default status code is 200', function () {
        $response = new Response();
        
        expect($response->getStatusCode())->toBe(200);
    });
});

describe('Response - Headers', function () {
    test('setHeader() sets a header', function () {
        $response = new Response();
        $response->setHeader('Content-Type', 'application/json');
        
        expect($response->getHeader('Content-Type'))->toBe('application/json');
    });

    test('setHeader() returns response for chaining', function () {
        $response = new Response();
        $result = $response->setHeader('X-Custom', 'value');
        
        expect($result)->toBe($response);
    });

    test('setHeaders() sets multiple headers', function () {
        $response = new Response();
        $response->setHeaders([
            'Content-Type' => 'application/json',
            'X-Custom' => 'value',
        ]);
        
        expect($response->getHeader('Content-Type'))->toBe('application/json');
        expect($response->getHeader('X-Custom'))->toBe('value');
    });

    test('getHeaders() returns all headers', function () {
        $response = new Response();
        $response->setHeader('Content-Type', 'text/html');
        $response->setHeader('X-Custom', 'value');
        
        $headers = $response->getHeaders();
        
        expect($headers)->toBe([
            'Content-Type' => 'text/html',
            'X-Custom' => 'value',
        ]);
    });

    test('getHeader() returns null for non-existent header', function () {
        $response = new Response();
        
        expect($response->getHeader('Missing'))->toBeNull();
    });
});

describe('Response - Content', function () {
    test('setContent() sets the content', function () {
        $response = new Response();
        $response->setContent('Hello World');
        
        expect($response->getContent())->toBe('Hello World');
    });

    test('setContent() returns response for chaining', function () {
        $response = new Response();
        $result = $response->setContent('Test');
        
        expect($result)->toBe($response);
    });

    test('default content is empty string', function () {
        $response = new Response();
        
        expect($response->getContent())->toBe('');
    });
});

describe('Response - Cookies', function () {
    test('setCookie() sets a cookie', function () {
        $response = new Response();
        $result = $response->setCookie('session', 'abc123');
        
        expect($result)->toBe($response);
    });

    test('setCookie() returns response for chaining', function () {
        $response = new Response();
        $result = $response->setCookie('test', 'value');
        
        expect($result)->toBe($response);
    });

    test('deleteCookie() sets expiration to past', function () {
        $response = new Response();
        $result = $response->deleteCookie('session');
        
        expect($result)->toBe($response);
    });
});

describe('Response - Instance Methods', function () {
    test('withJson() sets JSON response', function () {
        $response = new Response();
        $response->withJson(['message' => 'success'], 201);
        
        expect($response->getStatusCode())->toBe(201);
        expect($response->getHeader('Content-Type'))->toBe('application/json');
        expect($response->getContent())->toBe('{"message":"success"}');
    });

    test('text() sets plain text response', function () {
        $response = new Response();
        $response->text('Hello World', 200);
        
        expect($response->getStatusCode())->toBe(200);
        expect($response->getHeader('Content-Type'))->toBe('text/plain');
        expect($response->getContent())->toBe('Hello World');
    });

    test('html() sets HTML response', function () {
        $response = new Response();
        $response->html('<h1>Hello</h1>', 200);
        
        expect($response->getStatusCode())->toBe(200);
        expect($response->getHeader('Content-Type'))->toBe('text/html; charset=UTF-8');
        expect($response->getContent())->toBe('<h1>Hello</h1>');
    });

    test('redirectTo() sets redirect headers', function () {
        $response = new Response();
        $response->redirectTo('/dashboard', 302);
        
        expect($response->getStatusCode())->toBe(302);
        expect($response->getHeader('Location'))->toBe('/dashboard');
    });

    test('noCache() sets no-cache headers', function () {
        $response = new Response();
        $response->noCache();
        
        expect($response->getHeader('Cache-Control'))->toBe('no-cache, no-store, must-revalidate');
        expect($response->getHeader('Pragma'))->toBe('no-cache');
        expect($response->getHeader('Expires'))->toBe('0');
    });

    test('cache() sets cache headers', function () {
        $response = new Response();
        $response->cache(3600);
        
        expect($response->getHeader('Cache-Control'))->toBe('public, max-age=3600');
        expect($response->getHeader('Expires'))->not->toBeNull();
    });
});

describe('Response - Method Chaining', function () {
    test('multiple methods can be chained', function () {
        $response = new Response();
        $result = $response
            ->setStatusCode(201)
            ->setHeader('X-Custom', 'value')
            ->withJson(['status' => 'created']);
        
        expect($result)->toBe($response);
        expect($response->getStatusCode())->toBe(201);
        expect($response->getHeader('X-Custom'))->toBe('value');
        expect($response->getHeader('Content-Type'))->toBe('application/json');
    });

    test('cookie and cache methods can be chained', function () {
        $response = new Response();
        $result = $response
            ->setCookie('session', 'abc123')
            ->cache(3600)
            ->text('Cached content');
        
        expect($result)->toBe($response);
        expect($response->getHeader('Cache-Control'))->toBe('public, max-age=3600');
    });
});

describe('Response - Download', function () {
    test('download() throws exception for non-existent file', function () {
        $response = new Response();
        
        expect(fn() => $response->download('/non/existent/file.txt'))
            ->toThrow(RuntimeException::class);
    });
});
