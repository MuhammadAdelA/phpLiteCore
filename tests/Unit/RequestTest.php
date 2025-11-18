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

describe('Request - Enhanced Query Parameters', function () {
    test('query() retrieves GET parameter', function () {
        $request = new Request(['name' => 'John', 'age' => '30'], [], [], [], []);
        
        expect($request->query('name'))->toBe('John');
        expect($request->query('age'))->toBe('30');
    });

    test('query() returns default when parameter does not exist', function () {
        $request = new Request([], [], [], [], []);
        
        expect($request->query('missing', 'default'))->toBe('default');
    });

    test('queryAll() returns all GET parameters', function () {
        $request = new Request(['name' => 'John', 'age' => '30'], [], [], [], []);
        
        expect($request->queryAll())->toBe(['name' => 'John', 'age' => '30']);
    });
});

describe('Request - Enhanced POST Parameters', function () {
    test('post() retrieves POST parameter', function () {
        $request = new Request([], ['username' => 'admin', 'password' => 'secret'], [], [], []);
        
        expect($request->post('username'))->toBe('admin');
        expect($request->post('password'))->toBe('secret');
    });

    test('post() returns default when parameter does not exist', function () {
        $request = new Request([], [], [], [], []);
        
        expect($request->post('missing', 'default'))->toBe('default');
    });

    test('postAll() returns all POST parameters', function () {
        $request = new Request([], ['username' => 'admin', 'password' => 'secret'], [], [], []);
        
        expect($request->postAll())->toBe(['username' => 'admin', 'password' => 'secret']);
    });
});

describe('Request - all() and has()', function () {
    test('all() merges GET and POST with POST taking precedence', function () {
        $request = new Request(['a' => '1', 'b' => '2'], ['b' => '3', 'c' => '4'], [], [], []);
        
        expect($request->all())->toBe(['a' => '1', 'b' => '3', 'c' => '4']);
    });

    test('has() checks both POST and GET', function () {
        $request = new Request(['getKey' => 'value'], ['postKey' => 'value'], [], [], []);
        
        expect($request->has('getKey'))->toBeTrue();
        expect($request->has('postKey'))->toBeTrue();
        expect($request->has('missing'))->toBeFalse();
    });
});

describe('Request - Headers', function () {
    test('header() retrieves HTTP header', function () {
        $request = new Request([], [], [
            'HTTP_CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
        ], [], []);
        
        expect($request->header('Content-Type'))->toBe('application/json');
        expect($request->header('Accept'))->toBe('application/json');
    });

    test('header() handles special headers without HTTP_ prefix', function () {
        $request = new Request([], [], [
            'CONTENT_TYPE' => 'text/html',
            'CONTENT_LENGTH' => '1234',
        ], [], []);
        
        expect($request->header('Content-Type'))->toBe('text/html');
        expect($request->header('Content-Length'))->toBe('1234');
    });

    test('header() returns default when not found', function () {
        $request = new Request([], [], [], [], []);
        
        expect($request->header('Missing', 'default'))->toBe('default');
    });

    test('hasHeader() checks if header exists', function () {
        $request = new Request([], [], [
            'HTTP_AUTHORIZATION' => 'Bearer token',
            'CONTENT_TYPE' => 'application/json',
        ], [], []);
        
        expect($request->hasHeader('Authorization'))->toBeTrue();
        expect($request->hasHeader('Content-Type'))->toBeTrue();
        expect($request->hasHeader('Missing'))->toBeFalse();
    });

    test('headers() returns all HTTP headers', function () {
        $request = new Request([], [], [
            'HTTP_HOST' => 'example.com',
            'HTTP_USER_AGENT' => 'Mozilla/5.0',
            'CONTENT_TYPE' => 'application/json',
            'SERVER_NAME' => 'example.com',
        ], [], []);
        
        $headers = $request->headers();
        
        expect($headers)->toHaveKey('Host');
        expect($headers)->toHaveKey('User-Agent');
        expect($headers)->toHaveKey('Content-Type');
    });
});

describe('Request - Cookies', function () {
    test('cookie() retrieves cookie value', function () {
        $request = new Request([], [], [], ['session_id' => 'abc123', 'theme' => 'dark'], []);
        
        expect($request->cookie('session_id'))->toBe('abc123');
        expect($request->cookie('theme'))->toBe('dark');
    });

    test('cookie() returns default when not found', function () {
        $request = new Request([], [], [], [], []);
        
        expect($request->cookie('missing', 'default'))->toBe('default');
    });

    test('cookies() returns all cookies', function () {
        $request = new Request([], [], [], ['session_id' => 'abc123', 'theme' => 'dark'], []);
        
        expect($request->cookies())->toBe(['session_id' => 'abc123', 'theme' => 'dark']);
    });

    test('hasCookie() checks if cookie exists', function () {
        $request = new Request([], [], [], ['session_id' => 'abc123'], []);
        
        expect($request->hasCookie('session_id'))->toBeTrue();
        expect($request->hasCookie('missing'))->toBeFalse();
    });
});

describe('Request - Files', function () {
    test('file() retrieves uploaded file', function () {
        $fileData = [
            'avatar' => [
                'name' => 'photo.jpg',
                'type' => 'image/jpeg',
                'tmp_name' => '/tmp/phpXXXXXX',
                'error' => UPLOAD_ERR_OK,
                'size' => 12345,
            ],
        ];
        
        $request = new Request([], [], [], [], $fileData);
        
        expect($request->file('avatar'))->toBe($fileData['avatar']);
    });

    test('file() returns null when file does not exist', function () {
        $request = new Request([], [], [], [], []);
        
        expect($request->file('missing'))->toBeNull();
    });

    test('files() returns all uploaded files', function () {
        $fileData = [
            'avatar' => ['name' => 'photo.jpg', 'error' => UPLOAD_ERR_OK],
            'document' => ['name' => 'doc.pdf', 'error' => UPLOAD_ERR_OK],
        ];
        
        $request = new Request([], [], [], [], $fileData);
        
        expect($request->files())->toBe($fileData);
    });

    test('hasFile() checks if file was uploaded successfully', function () {
        $fileData = [
            'good' => ['error' => UPLOAD_ERR_OK],
            'bad' => ['error' => UPLOAD_ERR_NO_FILE],
        ];
        
        $request = new Request([], [], [], [], $fileData);
        
        expect($request->hasFile('good'))->toBeTrue();
        expect($request->hasFile('bad'))->toBeFalse();
        expect($request->hasFile('missing'))->toBeFalse();
    });
});

describe('Request - Method Checks', function () {
    test('isPost() returns true for POST method', function () {
        $request = new Request([], [], ['REQUEST_METHOD' => 'POST'], [], []);
        expect($request->isPost())->toBeTrue();
        
        $request = new Request([], [], ['REQUEST_METHOD' => 'GET'], [], []);
        expect($request->isPost())->toBeFalse();
    });

    test('isGet() returns true for GET method', function () {
        $request = new Request([], [], ['REQUEST_METHOD' => 'GET'], [], []);
        expect($request->isGet())->toBeTrue();
        
        $request = new Request([], [], ['REQUEST_METHOD' => 'POST'], [], []);
        expect($request->isGet())->toBeFalse();
    });

    test('isPut() checks for PUT method', function () {
        $request = new Request([], [], ['REQUEST_METHOD' => 'PUT'], [], []);
        expect($request->isPut())->toBeTrue();
    });

    test('isDelete() checks for DELETE method', function () {
        $request = new Request([], [], ['REQUEST_METHOD' => 'DELETE'], [], []);
        expect($request->isDelete())->toBeTrue();
    });

    test('isPatch() checks for PATCH method', function () {
        $request = new Request([], [], ['REQUEST_METHOD' => 'PATCH'], [], []);
        expect($request->isPatch())->toBeTrue();
    });
});

describe('Request - Content Type', function () {
    test('isJson() checks if content type is JSON', function () {
        $request = new Request([], [], ['CONTENT_TYPE' => 'application/json'], [], []);
        expect($request->isJson())->toBeTrue();
        
        $request = new Request([], [], ['CONTENT_TYPE' => 'application/json; charset=utf-8'], [], []);
        expect($request->isJson())->toBeTrue();
        
        $request = new Request([], [], ['CONTENT_TYPE' => 'text/html'], [], []);
        expect($request->isJson())->toBeFalse();
    });

    test('expectsJson() checks Accept header', function () {
        $request = new Request([], [], ['HTTP_ACCEPT' => 'application/json'], [], []);
        expect($request->expectsJson())->toBeTrue();
        
        $request = new Request([], [], ['HTTP_ACCEPT' => 'text/html'], [], []);
        expect($request->expectsJson())->toBeFalse();
    });
});

describe('Request - URL and Security', function () {
    test('url() constructs full URL', function () {
        $request = new Request([], [], [
            'HTTP_HOST' => 'example.com',
            'REQUEST_URI' => '/path/to/page?query=value',
        ], [], []);
        
        expect($request->url())->toBe('http://example.com/path/to/page?query=value');
    });

    test('isSecure() checks for HTTPS', function () {
        $request = new Request([], [], ['HTTPS' => 'on'], [], []);
        expect($request->isSecure())->toBeTrue();
        
        $request = new Request([], [], ['HTTP_X_FORWARDED_PROTO' => 'https'], [], []);
        expect($request->isSecure())->toBeTrue();
        
        $request = new Request([], [], ['SERVER_PORT' => '443'], [], []);
        expect($request->isSecure())->toBeTrue();
        
        $request = new Request([], [], [], [], []);
        expect($request->isSecure())->toBeFalse();
    });
});

describe('Request - User Agent and Referer', function () {
    test('userAgent() returns user agent string', function () {
        $request = new Request([], [], ['HTTP_USER_AGENT' => 'Mozilla/5.0'], [], []);
        expect($request->userAgent())->toBe('Mozilla/5.0');
        
        $request = new Request([], [], [], [], []);
        expect($request->userAgent())->toBe('');
    });

    test('referer() returns referer URL', function () {
        $request = new Request([], [], ['HTTP_REFERER' => 'https://google.com'], [], []);
        expect($request->referer())->toBe('https://google.com');
        
        $request = new Request([], [], [], [], []);
        expect($request->referer())->toBeNull();
    });
});
