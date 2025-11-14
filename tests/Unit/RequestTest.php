<?php

use PhpLiteCore\Http\Request;

describe('Request Constructor', function () {
    test('it can be instantiated with empty arrays', function () {
        $request = new Request();
        
        expect($request)->toBeInstanceOf(Request::class);
    });

    test('it can be instantiated with custom data', function () {
        $request = new Request(
            ['name' => 'John'],
            ['email' => 'john@example.com'],
            ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/users'],
            ['session' => 'abc123'],
            []
        );
        
        expect($request)->toBeInstanceOf(Request::class);
    });
});

describe('Request::createFromGlobals()', function () {
    test('it creates a Request instance from globals', function () {
        // Save original values
        $originalGet = $_GET;
        $originalPost = $_POST;
        $originalServer = $_SERVER;
        $originalCookie = $_COOKIE;
        $originalFiles = $_FILES;

        // Set test values
        $_GET = ['test' => 'value'];
        $_POST = ['data' => 'posted'];
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_COOKIE = ['token' => 'xyz'];
        $_FILES = [];

        $request = Request::createFromGlobals();
        
        expect($request)->toBeInstanceOf(Request::class);
        expect($request->input('test'))->toBe('value');
        expect($request->input('data'))->toBe('posted');
        expect($request->getMethod())->toBe('POST');

        // Restore original values
        $_GET = $originalGet;
        $_POST = $originalPost;
        $_SERVER = $originalServer;
        $_COOKIE = $originalCookie;
        $_FILES = $originalFiles;
    });
});

describe('Request::getClientIp()', function () {
    test('it returns 0.0.0.0 when no IP is available', function () {
        $request = new Request([], [], []);
        
        expect($request->getClientIp())->toBe('0.0.0.0');
    });

    test('it returns REMOTE_ADDR when available', function () {
        $request = new Request([], [], ['REMOTE_ADDR' => '192.168.1.1']);
        
        expect($request->getClientIp())->toBe('192.168.1.1');
    });

    test('it prioritizes HTTP_CLIENT_IP over REMOTE_ADDR', function () {
        $request = new Request([], [], [
            'HTTP_CLIENT_IP' => '10.0.0.1',
            'REMOTE_ADDR' => '192.168.1.1'
        ]);
        
        expect($request->getClientIp())->toBe('10.0.0.1');
    });

    test('it handles HTTP_X_FORWARDED_FOR with multiple IPs', function () {
        $request = new Request([], [], [
            'HTTP_X_FORWARDED_FOR' => '203.0.113.1, 198.51.100.1, 192.0.2.1',
            'REMOTE_ADDR' => '192.168.1.1'
        ]);
        
        expect($request->getClientIp())->toBe('203.0.113.1');
    });

    test('it trims whitespace from HTTP_X_FORWARDED_FOR', function () {
        $request = new Request([], [], [
            'HTTP_X_FORWARDED_FOR' => '  203.0.113.1  , 198.51.100.1'
        ]);
        
        expect($request->getClientIp())->toBe('203.0.113.1');
    });

    test('it handles HTTP_X_FORWARDED', function () {
        $request = new Request([], [], [
            'HTTP_X_FORWARDED' => '203.0.113.1'
        ]);
        
        expect($request->getClientIp())->toBe('203.0.113.1');
    });

    test('it handles HTTP_FORWARDED_FOR', function () {
        $request = new Request([], [], [
            'HTTP_FORWARDED_FOR' => '203.0.113.1'
        ]);
        
        expect($request->getClientIp())->toBe('203.0.113.1');
    });

    test('it handles HTTP_FORWARDED with for= syntax', function () {
        $request = new Request([], [], [
            'HTTP_FORWARDED' => 'for="203.0.113.1"'
        ]);
        
        expect($request->getClientIp())->toBe('203.0.113.1');
    });

    test('it handles HTTP_FORWARDED without quotes', function () {
        $request = new Request([], [], [
            'HTTP_FORWARDED' => 'for=203.0.113.1'
        ]);
        
        expect($request->getClientIp())->toBe('203.0.113.1');
    });

    test('it validates IPv4 addresses', function () {
        $request = new Request([], [], [
            'REMOTE_ADDR' => 'invalid-ip'
        ]);
        
        expect($request->getClientIp())->toBe('0.0.0.0');
    });

    test('it supports IPv6 addresses', function () {
        $request = new Request([], [], [
            'REMOTE_ADDR' => '2001:0db8:85a3:0000:0000:8a2e:0370:7334'
        ]);
        
        expect($request->getClientIp())->toBe('2001:0db8:85a3:0000:0000:8a2e:0370:7334');
    });

    test('it normalizes IPv6 addresses with brackets', function () {
        $request = new Request([], [], [
            'REMOTE_ADDR' => '[2001:db8::1]'
        ]);
        
        expect($request->getClientIp())->toBe('2001:db8::1');
    });

    test('it normalizes IPv6 addresses with brackets and port', function () {
        $request = new Request([], [], [
            'REMOTE_ADDR' => '[2001:db8::1]:8080'
        ]);
        
        expect($request->getClientIp())->toBe('2001:db8::1');
    });
});

describe('Request::isAjax()', function () {
    test('it returns false when no AJAX header is present', function () {
        $request = new Request([], [], []);
        
        expect($request->isAjax())->toBeFalse();
    });

    test('it returns true when X-Requested-With is XMLHttpRequest', function () {
        $request = new Request([], [], [
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest'
        ]);
        
        expect($request->isAjax())->toBeTrue();
    });

    test('it is case-insensitive for XMLHttpRequest', function () {
        $request = new Request([], [], [
            'HTTP_X_REQUESTED_WITH' => 'xmlhttprequest'
        ]);
        
        expect($request->isAjax())->toBeTrue();
    });

    test('it returns false for non-XMLHttpRequest values', function () {
        $request = new Request([], [], [
            'HTTP_X_REQUESTED_WITH' => 'SomeOtherValue'
        ]);
        
        expect($request->isAjax())->toBeFalse();
    });
});

describe('Request::getPath()', function () {
    test('it returns / when REQUEST_URI is not set', function () {
        $request = new Request([], [], []);
        
        expect($request->getPath())->toBe('/');
    });

    test('it returns the REQUEST_URI path', function () {
        $request = new Request([], [], [
            'REQUEST_URI' => '/users/123'
        ]);
        
        expect($request->getPath())->toBe('/users/123');
    });

    test('it removes query string from REQUEST_URI', function () {
        $request = new Request([], [], [
            'REQUEST_URI' => '/users/123?page=2&sort=name'
        ]);
        
        expect($request->getPath())->toBe('/users/123');
    });

    test('it handles root path with query string', function () {
        $request = new Request([], [], [
            'REQUEST_URI' => '/?search=test'
        ]);
        
        expect($request->getPath())->toBe('/');
    });

    test('it handles empty REQUEST_URI', function () {
        $request = new Request([], [], [
            'REQUEST_URI' => ''
        ]);
        
        expect($request->getPath())->toBe('/');
    });
});

describe('Request::getMethod()', function () {
    test('it returns GET when REQUEST_METHOD is not set', function () {
        $request = new Request([], [], []);
        
        expect($request->getMethod())->toBe('GET');
    });

    test('it returns the REQUEST_METHOD in uppercase', function () {
        $request = new Request([], [], [
            'REQUEST_METHOD' => 'post'
        ]);
        
        expect($request->getMethod())->toBe('POST');
    });

    test('it returns GET for GET requests', function () {
        $request = new Request([], [], [
            'REQUEST_METHOD' => 'GET'
        ]);
        
        expect($request->getMethod())->toBe('GET');
    });

    test('it returns POST for POST requests', function () {
        $request = new Request([], [], [
            'REQUEST_METHOD' => 'POST'
        ]);
        
        expect($request->getMethod())->toBe('POST');
    });

    test('it returns PUT for PUT requests', function () {
        $request = new Request([], [], [
            'REQUEST_METHOD' => 'PUT'
        ]);
        
        expect($request->getMethod())->toBe('PUT');
    });

    test('it returns DELETE for DELETE requests', function () {
        $request = new Request([], [], [
            'REQUEST_METHOD' => 'DELETE'
        ]);
        
        expect($request->getMethod())->toBe('DELETE');
    });

    test('it returns PATCH for PATCH requests', function () {
        $request = new Request([], [], [
            'REQUEST_METHOD' => 'PATCH'
        ]);
        
        expect($request->getMethod())->toBe('PATCH');
    });
});

describe('Request::input()', function () {
    test('it returns null when key is not found', function () {
        $request = new Request([], [], []);
        
        expect($request->input('nonexistent'))->toBeNull();
    });

    test('it returns default value when key is not found', function () {
        $request = new Request([], [], []);
        
        expect($request->input('nonexistent', 'default'))->toBe('default');
    });

    test('it retrieves value from GET data', function () {
        $request = new Request(['name' => 'John'], [], []);
        
        expect($request->input('name'))->toBe('John');
    });

    test('it retrieves value from POST data', function () {
        $request = new Request([], ['email' => 'john@example.com'], []);
        
        expect($request->input('email'))->toBe('john@example.com');
    });

    test('it prioritizes POST over GET', function () {
        $request = new Request(
            ['name' => 'FromGet'],
            ['name' => 'FromPost'],
            []
        );
        
        expect($request->input('name'))->toBe('FromPost');
    });

    test('it retrieves GET value when not in POST', function () {
        $request = new Request(
            ['name' => 'FromGet'],
            ['email' => 'test@example.com'],
            []
        );
        
        expect($request->input('name'))->toBe('FromGet');
    });

    test('it handles numeric values', function () {
        $request = new Request(['page' => 5], [], []);
        
        expect($request->input('page'))->toBe(5);
    });

    test('it handles array values', function () {
        $request = new Request(['tags' => ['php', 'testing']], [], []);
        
        expect($request->input('tags'))->toBe(['php', 'testing']);
    });

    test('it can return empty string as valid value', function () {
        $request = new Request(['search' => ''], [], []);
        
        expect($request->input('search'))->toBe('');
    });

    test('it can return false as valid value', function () {
        $request = new Request(['enabled' => false], [], []);
        
        expect($request->input('enabled'))->toBeFalse();
    });

    test('it can return 0 as valid value', function () {
        $request = new Request(['count' => 0], [], []);
        
        expect($request->input('count'))->toBe(0);
    });
});
