<?php

use PhpLiteCore\Http\Response;

describe('Response::setStatusCode()', function () {
    test('it sets the status code', function () {
        $response = new Response();
        $response->setStatusCode(404);
        
        // Use reflection to access protected property
        $reflection = new ReflectionClass($response);
        $property = $reflection->getProperty('statusCode');
        $property->setAccessible(true);
        
        expect($property->getValue($response))->toBe(404);
    });

    test('it returns the response instance for chaining', function () {
        $response = new Response();
        $result = $response->setStatusCode(201);
        
        expect($result)->toBe($response);
    });
});

describe('Response::withJson()', function () {
    test('it sets JSON content and content-type header', function () {
        $response = new Response();
        $data = ['name' => 'John', 'age' => 30];
        
        $response->withJson($data);
        
        // Use reflection to access protected properties
        $reflection = new ReflectionClass($response);
        
        $contentProperty = $reflection->getProperty('content');
        $contentProperty->setAccessible(true);
        expect($contentProperty->getValue($response))->toBe(json_encode($data));
        
        $headersProperty = $reflection->getProperty('headers');
        $headersProperty->setAccessible(true);
        $headers = $headersProperty->getValue($response);
        expect($headers['Content-Type'])->toBe('application/json');
    });

    test('it sets status code when provided', function () {
        $response = new Response();
        $response->withJson(['status' => 'created'], 201);
        
        $reflection = new ReflectionClass($response);
        $property = $reflection->getProperty('statusCode');
        $property->setAccessible(true);
        
        expect($property->getValue($response))->toBe(201);
    });

    test('it preserves previously set status code when status not provided', function () {
        $response = new Response();
        $response->setStatusCode(201);
        $response->withJson(['status' => 'created']);
        
        $reflection = new ReflectionClass($response);
        $property = $reflection->getProperty('statusCode');
        $property->setAccessible(true);
        
        expect($property->getValue($response))->toBe(201);
    });

    test('it returns the response instance for chaining', function () {
        $response = new Response();
        $result = $response->withJson(['test' => 'data']);
        
        expect($result)->toBe($response);
    });

    test('it supports method chaining with setStatusCode', function () {
        $response = new Response();
        $response->setStatusCode(201)->withJson(['status' => 'created']);
        
        $reflection = new ReflectionClass($response);
        $property = $reflection->getProperty('statusCode');
        $property->setAccessible(true);
        
        expect($property->getValue($response))->toBe(201);
    });
});

describe('Response::text()', function () {
    test('it sets text content and content-type header', function () {
        $response = new Response();
        $response->text('Hello, World!');
        
        $reflection = new ReflectionClass($response);
        
        $contentProperty = $reflection->getProperty('content');
        $contentProperty->setAccessible(true);
        expect($contentProperty->getValue($response))->toBe('Hello, World!');
        
        $headersProperty = $reflection->getProperty('headers');
        $headersProperty->setAccessible(true);
        $headers = $headersProperty->getValue($response);
        expect($headers['Content-Type'])->toBe('text/plain');
    });

    test('it sets status code when provided', function () {
        $response = new Response();
        $response->text('Not Found', 404);
        
        $reflection = new ReflectionClass($response);
        $property = $reflection->getProperty('statusCode');
        $property->setAccessible(true);
        
        expect($property->getValue($response))->toBe(404);
    });

    test('it preserves previously set status code when status not provided', function () {
        $response = new Response();
        $response->setStatusCode(500);
        $response->text('Server Error');
        
        $reflection = new ReflectionClass($response);
        $property = $reflection->getProperty('statusCode');
        $property->setAccessible(true);
        
        expect($property->getValue($response))->toBe(500);
    });

    test('it returns the response instance for chaining', function () {
        $response = new Response();
        $result = $response->text('test');
        
        expect($result)->toBe($response);
    });
});

describe('Response::html()', function () {
    test('it sets HTML content and content-type header', function () {
        $response = new Response();
        $response->html('<h1>Hello</h1>');
        
        $reflection = new ReflectionClass($response);
        
        $contentProperty = $reflection->getProperty('content');
        $contentProperty->setAccessible(true);
        expect($contentProperty->getValue($response))->toBe('<h1>Hello</h1>');
        
        $headersProperty = $reflection->getProperty('headers');
        $headersProperty->setAccessible(true);
        $headers = $headersProperty->getValue($response);
        expect($headers['Content-Type'])->toBe('text/html');
    });

    test('it sets status code when provided', function () {
        $response = new Response();
        $response->html('<h1>Created</h1>', 201);
        
        $reflection = new ReflectionClass($response);
        $property = $reflection->getProperty('statusCode');
        $property->setAccessible(true);
        
        expect($property->getValue($response))->toBe(201);
    });

    test('it preserves previously set status code when status not provided', function () {
        $response = new Response();
        $response->setStatusCode(403);
        $response->html('<h1>Forbidden</h1>');
        
        $reflection = new ReflectionClass($response);
        $property = $reflection->getProperty('statusCode');
        $property->setAccessible(true);
        
        expect($property->getValue($response))->toBe(403);
    });

    test('it returns the response instance for chaining', function () {
        $response = new Response();
        $result = $response->html('<div>test</div>');
        
        expect($result)->toBe($response);
    });
});

describe('Response::redirectTo()', function () {
    test('it sets location header', function () {
        $response = new Response();
        $response->redirectTo('/home');
        
        $reflection = new ReflectionClass($response);
        $headersProperty = $reflection->getProperty('headers');
        $headersProperty->setAccessible(true);
        $headers = $headersProperty->getValue($response);
        
        expect($headers['Location'])->toBe('/home');
    });

    test('it sets default 302 status when no status provided', function () {
        $response = new Response();
        $response->redirectTo('/home');
        
        $reflection = new ReflectionClass($response);
        $property = $reflection->getProperty('statusCode');
        $property->setAccessible(true);
        
        expect($property->getValue($response))->toBe(302);
    });

    test('it sets custom status code when provided', function () {
        $response = new Response();
        $response->redirectTo('/moved', 301);
        
        $reflection = new ReflectionClass($response);
        $property = $reflection->getProperty('statusCode');
        $property->setAccessible(true);
        
        expect($property->getValue($response))->toBe(301);
    });

    test('it preserves previously set status code when status not provided', function () {
        $response = new Response();
        $response->setStatusCode(307);
        $response->redirectTo('/temporary');
        
        $reflection = new ReflectionClass($response);
        $property = $reflection->getProperty('statusCode');
        $property->setAccessible(true);
        
        expect($property->getValue($response))->toBe(307);
    });

    test('it returns the response instance for chaining', function () {
        $response = new Response();
        $result = $response->redirectTo('/home');
        
        expect($result)->toBe($response);
    });
});

describe('Response Method Chaining', function () {
    test('it chains setStatusCode with withJson', function () {
        $response = new Response();
        $response->setStatusCode(201)->withJson(['created' => true]);
        
        $reflection = new ReflectionClass($response);
        $statusProperty = $reflection->getProperty('statusCode');
        $statusProperty->setAccessible(true);
        
        expect($statusProperty->getValue($response))->toBe(201);
        
        $contentProperty = $reflection->getProperty('content');
        $contentProperty->setAccessible(true);
        expect($contentProperty->getValue($response))->toBe(json_encode(['created' => true]));
    });

    test('it chains setStatusCode with text', function () {
        $response = new Response();
        $response->setStatusCode(404)->text('Not Found');
        
        $reflection = new ReflectionClass($response);
        $statusProperty = $reflection->getProperty('statusCode');
        $statusProperty->setAccessible(true);
        
        expect($statusProperty->getValue($response))->toBe(404);
    });

    test('it chains setStatusCode with html', function () {
        $response = new Response();
        $response->setStatusCode(500)->html('<h1>Error</h1>');
        
        $reflection = new ReflectionClass($response);
        $statusProperty = $reflection->getProperty('statusCode');
        $statusProperty->setAccessible(true);
        
        expect($statusProperty->getValue($response))->toBe(500);
    });

    test('it chains setStatusCode with redirectTo', function () {
        $response = new Response();
        $response->setStatusCode(301)->redirectTo('/new-location');
        
        $reflection = new ReflectionClass($response);
        $statusProperty = $reflection->getProperty('statusCode');
        $statusProperty->setAccessible(true);
        
        expect($statusProperty->getValue($response))->toBe(301);
    });
});
