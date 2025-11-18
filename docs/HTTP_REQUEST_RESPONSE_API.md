# HTTP Request/Response API Documentation

This document describes the enhanced HTTP Request and Response abstractions in phpLiteCore, providing a rich, testable API for handling HTTP interactions.

## Table of Contents

- [Request Class](#request-class)
  - [Creating Requests](#creating-requests)
  - [Query Parameters](#query-parameters)
  - [POST Data](#post-data)
  - [Input Methods](#input-methods)
  - [Headers](#headers)
  - [Cookies](#cookies)
  - [File Uploads](#file-uploads)
  - [Method Checks](#method-checks)
  - [Content Type](#content-type)
  - [URL and Security](#url-and-security)
- [Response Class](#response-class)
  - [Creating Responses](#creating-responses)
  - [Status Codes](#status-codes)
  - [Headers](#response-headers)
  - [Cookies](#response-cookies)
  - [Content Types](#content-types)
  - [Static Helpers](#static-helpers)
  - [Method Chaining](#method-chaining)

---

## Request Class

The `Request` class (`PhpLiteCore\Http\Request`) provides a clean abstraction for working with HTTP requests.

### Creating Requests

#### From PHP Globals

```php
use PhpLiteCore\Http\Request;

$request = Request::createFromGlobals();
```

#### Custom Request (for testing)

```php
$request = new Request(
    ['key' => 'value'],          // GET parameters
    ['username' => 'admin'],     // POST parameters
    ['REQUEST_METHOD' => 'POST'], // Server variables
    ['session' => 'abc123'],     // Cookies
    []                           // Files
);
```

### Query Parameters

Retrieve GET parameters:

```php
// Get a single query parameter
$page = $request->query('page', 1); // Returns 1 if 'page' not found

// Get all query parameters
$allQuery = $request->queryAll(); // Returns array of all GET params
```

### POST Data

Retrieve POST parameters:

```php
// Get a single POST parameter
$username = $request->post('username', 'guest');

// Get all POST parameters
$allPost = $request->postAll();
```

### Input Methods

Unified access to both GET and POST data (POST takes precedence):

```php
// Get from POST first, then GET
$value = $request->input('name', 'default');

// Get all input (merged GET and POST)
$allInput = $request->all();

// Check if a parameter exists
if ($request->has('email')) {
    // Parameter exists in either GET or POST
}
```

### Headers

Work with HTTP headers:

```php
// Get a single header
$contentType = $request->header('Content-Type', 'text/html');
$auth = $request->header('Authorization');

// Get all headers
$allHeaders = $request->headers();

// Check if header exists
if ($request->hasHeader('Authorization')) {
    // Header exists
}
```

### Cookies

Access cookies:

```php
// Get a single cookie
$sessionId = $request->cookie('session_id', null);

// Get all cookies
$allCookies = $request->cookies();

// Check if cookie exists
if ($request->hasCookie('session_id')) {
    // Cookie exists
}
```

### File Uploads

Handle uploaded files:

```php
// Get a single uploaded file
$avatar = $request->file('avatar');
// Returns: ['name' => 'photo.jpg', 'type' => 'image/jpeg', ...]

// Get all uploaded files
$allFiles = $request->files();

// Check if file was uploaded successfully
if ($request->hasFile('avatar')) {
    // File uploaded successfully (UPLOAD_ERR_OK)
}
```

### Method Checks

Check the HTTP method:

```php
if ($request->isGet()) {
    // Handle GET request
}

if ($request->isPost()) {
    // Handle POST request
}

// Also available:
$request->isPut();
$request->isDelete();
$request->isPatch();

// Get method string
$method = $request->getMethod(); // Returns 'GET', 'POST', etc.
```

### Content Type

Check request content type:

```php
// Check if request is JSON
if ($request->isJson()) {
    $data = $request->json(); // Get decoded JSON body
}

// Check if client expects JSON response
if ($request->expectsJson()) {
    // Return JSON response
}

// Check if AJAX request
if ($request->isAjax()) {
    // Handle AJAX request
}
```

### URL and Security

Access URL and security information:

```php
// Get full URL
$url = $request->url();
// Returns: 'http://example.com/path?query=value'

// Check if HTTPS
if ($request->isSecure()) {
    // Request is over HTTPS
}

// Get client IP address
$ip = $request->getClientIp();

// Get request path
$path = $request->getPath(); // Returns '/path' (without query string)

// Get user agent
$userAgent = $request->userAgent();

// Get referer
$referer = $request->referer();
```

---

## Response Class

The `Response` class (`PhpLiteCore\Http\Response`) provides a fluent interface for building HTTP responses.

### Creating Responses

```php
use PhpLiteCore\Http\Response;

$response = new Response();
```

### Status Codes

```php
// Set status code
$response->setStatusCode(404);

// Get current status code
$code = $response->getStatusCode();
```

### Response Headers

```php
// Set a single header
$response->setHeader('Content-Type', 'application/json');

// Set multiple headers
$response->setHeaders([
    'Content-Type' => 'application/json',
    'X-Custom' => 'value',
]);

// Get a header
$contentType = $response->getHeader('Content-Type');

// Get all headers
$headers = $response->getHeaders();
```

### Response Cookies

```php
// Set a cookie
$response->setCookie(
    'session_id',      // name
    'abc123',          // value
    time() + 3600,     // expire (optional)
    '/',               // path (optional)
    '',                // domain (optional)
    false,             // secure (optional)
    true               // httponly (optional)
);

// Delete a cookie
$response->deleteCookie('session_id');
```

### Content Types

```php
// JSON response (instance method)
$response->withJson(['status' => 'success'], 201);

// Plain text response
$response->text('Hello World', 200);

// HTML response
$response->html('<h1>Hello</h1>', 200);

// Set content manually
$response->setContent('Custom content');

// Get content
$content = $response->getContent();
```

### Static Helpers

These methods send the response and terminate execution:

```php
// JSON response (static)
Response::json(['error' => 'Not found'], 404);

// Redirect
Response::redirect('/dashboard', 302);

// 404 Not Found
Response::notFound('Page not found');

// 403 Forbidden
Response::forbidden('Access denied');

// 429 Too Many Requests
Response::tooManyRequests('Rate limit exceeded', 60);
```

### Method Chaining

Build complex responses with method chaining:

```php
$response = (new Response())
    ->setStatusCode(201)
    ->setHeader('X-Custom', 'value')
    ->setCookie('session', 'abc123')
    ->withJson(['status' => 'created']);

$response->send(); // Send to client
```

#### Cache Control

```php
// No cache
$response->noCache();

// Cache for 1 hour
$response->cache(3600);
```

#### File Downloads

```php
// Download a file
$response->download('/path/to/file.pdf', 'document.pdf');
$response->send();
```

#### Instance Redirect

```php
// Redirect (non-static, doesn't exit)
$response->redirectTo('/dashboard', 302);
$response->send();
```

---

## Using Request in Controllers

Controllers can now accept the `Request` object as a parameter:

```php
namespace App\Controllers;

use PhpLiteCore\Http\Request;
use PhpLiteCore\Bootstrap\Application;

class UserController extends BaseController
{
    public function store(Request $request): void
    {
        // Access request data
        $username = $request->post('username');
        $email = $request->input('email');
        
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            // Handle file upload
        }
        
        // Return JSON response
        if ($request->expectsJson()) {
            Response::json(['success' => true], 201);
        } else {
            Response::redirect('/users');
        }
    }
    
    public function show(Request $request, int $id): void
    {
        // Request is injected before route parameters
        $user = User::find($id);
        
        if ($request->isAjax()) {
            Response::json($user);
        } else {
            $this->view('user', ['user' => $user]);
        }
    }
}
```

**Note:** When a controller method accepts a `Request` parameter, it will be automatically injected by the Router before any route parameters.

---

## Using Request in Middleware

Middleware can now accept the `Request` object:

```php
namespace App\Middleware;

use PhpLiteCore\Http\Request;

class CustomMiddleware
{
    public function handle(Request $request): void
    {
        // Check request properties
        if (!$request->hasHeader('X-API-Key')) {
            Response::forbidden('API key required');
        }
        
        // Log request
        error_log("Request to: " . $request->url());
    }
}
```

**Legacy Support:** Middleware that accepts a `string $method` parameter will continue to work (the Router automatically detects the signature).

---

## Migration Guide

### Migrating from Direct Global Access

**Before:**
```php
$page = $_GET['page'] ?? 1;
$username = $_POST['username'];
$ip = $_SERVER['REMOTE_ADDR'];
```

**After:**
```php
public function index(Request $request): void
{
    $page = $request->query('page', 1);
    $username = $request->post('username');
    $ip = $request->getClientIp();
}
```

### Migrating Controllers

**Before:**
```php
public function store(): void
{
    $title = $_POST['title'];
    $body = $_POST['body'];
    // ...
}
```

**After (Option 1 - Using Request object):**
```php
public function store(Request $request): void
{
    $title = $request->post('title');
    $body = $request->post('body');
    // ...
}
```

**After (Option 2 - Still works without changes):**
```php
public function store(): void
{
    $title = $_POST['title'];
    $body = $_POST['body'];
    // ...
}
```

The old approach still works, but using the `Request` object is recommended for better testability.

---

## Testing with Request Objects

The Request abstraction makes testing much easier:

```php
// Create a test request
$request = new Request(
    ['page' => 2],                    // Query params
    ['username' => 'testuser'],       // POST data
    ['REQUEST_METHOD' => 'POST'],     // Server vars
    ['session_id' => 'test123'],      // Cookies
    []                                // Files
);

// Test controller
$controller = new UserController($app);
$controller->index($request);
```

---

## Best Practices

1. **Use Request object in controllers** for better testability
2. **Type-hint Request parameter** to enable automatic injection
3. **Use instance Response methods** for complex responses with chaining
4. **Use static Response methods** for simple, immediate responses
5. **Check content type** before parsing JSON: `if ($request->isJson())`
6. **Validate file uploads** with `hasFile()` before accessing
7. **Use method helpers** (`isPost()`, `isGet()`) instead of manual checks

---

## Examples

### Complete CRUD Controller

```php
use PhpLiteCore\Http\Request;
use PhpLiteCore\Http\Response;

class PostController extends BaseController
{
    public function index(Request $request): void
    {
        $page = $request->query('page', 1);
        $posts = Post::paginate(10, $page);
        
        if ($request->expectsJson()) {
            Response::json($posts);
        } else {
            $this->view('posts.index', ['posts' => $posts]);
        }
    }
    
    public function store(Request $request): void
    {
        if (!$request->isPost()) {
            Response::forbidden('Method not allowed');
        }
        
        $post = new Post([
            'title' => $request->post('title'),
            'body' => $request->post('body'),
        ]);
        
        $post->save();
        
        $response = new Response();
        $response
            ->setStatusCode(201)
            ->setHeader('Location', '/posts/' . $post->id)
            ->withJson(['id' => $post->id, 'status' => 'created']);
        
        $response->send();
    }
    
    public function update(Request $request, int $id): void
    {
        $post = Post::find($id);
        
        if (!$post) {
            Response::notFound('Post not found');
        }
        
        $post->title = $request->input('title');
        $post->body = $request->input('body');
        $post->save();
        
        Response::redirect('/posts/' . $id);
    }
}
```

---

For more examples and detailed information, see the main [phpLiteCore documentation](https://muhammadadela.github.io/phpLiteCore/).
