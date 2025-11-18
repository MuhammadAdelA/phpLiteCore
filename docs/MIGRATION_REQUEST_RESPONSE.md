# Migration Guide: HTTP Request/Response Enhancements

This guide helps you migrate existing phpLiteCore code to use the new enhanced Request and Response abstractions.

## Overview

The HTTP Request/Response layer has been enriched with:
- Rich API for accessing headers, cookies, query parameters, POST data, and files
- Automatic Request injection into controllers and middleware
- Enhanced Response methods with fluent interface
- Better testability through object-oriented abstractions

## Backward Compatibility

**Good news:** All existing code will continue to work without changes! The enhancements are additive and maintain full backward compatibility.

- Controllers that don't use `Request` objects continue to work
- Middleware that accepts `string $method` continue to work
- Direct access to `$_GET`, `$_POST`, `$_SERVER`, etc. still works

However, we **strongly recommend** migrating to the new API for:
- Better testability
- Cleaner, more maintainable code
- Type safety with Request objects
- Easier mocking in tests

---

## Migration Patterns

### Pattern 1: Controllers with Direct Global Access

#### Before
```php
class PostController extends BaseController
{
    public function store(): void
    {
        $title = $_POST['title'] ?? '';
        $body = $_POST['body'] ?? '';
        
        if (empty($title) || empty($body)) {
            http_response_code(422);
            echo json_encode(['error' => 'Missing fields']);
            return;
        }
        
        $post = new Post(['title' => $title, 'body' => $body]);
        $post->save();
        
        header('Location: /posts/' . $post->id);
        exit;
    }
}
```

#### After
```php
use PhpLiteCore\Http\Request;
use PhpLiteCore\Http\Response;

class PostController extends BaseController
{
    public function store(Request $request): void
    {
        $title = $request->post('title', '');
        $body = $request->post('body', '');
        
        if (empty($title) || empty($body)) {
            Response::json(['error' => 'Missing fields'], 422);
            return;
        }
        
        $post = new Post(['title' => $title, 'body' => $body]);
        $post->save();
        
        Response::redirect('/posts/' . $post->id);
    }
}
```

**Benefits:**
- Testable (can inject mock Request)
- Cleaner syntax with `$request->post()`
- Type-safe with Request type hint
- No manual header management

---

### Pattern 2: Query Parameters

#### Before
```php
public function index(): void
{
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $search = $_GET['search'] ?? '';
    $sort = $_GET['sort'] ?? 'created_at';
    
    // Use $page, $search, $sort
}
```

#### After
```php
public function index(Request $request): void
{
    $page = $request->query('page', 1);
    $search = $request->query('search', '');
    $sort = $request->query('sort', 'created_at');
    
    // Use $page, $search, $sort
}
```

**Benefits:**
- Cleaner syntax
- Built-in default values
- Type casting handled by validation layer

---

### Pattern 3: Checking Request Method

#### Before
```php
public function update(int $id): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo 'Method not allowed';
        exit;
    }
    
    // Handle update
}
```

#### After
```php
public function update(Request $request, int $id): void
{
    if (!$request->isPost()) {
        Response::json(['error' => 'Method not allowed'], 405);
        return;
    }
    
    // Handle update
}
```

**Benefits:**
- Cleaner method checks
- No manual response handling
- Better response formatting

---

### Pattern 4: Headers and Content Type

#### Before
```php
public function api(): void
{
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) 
        && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    
    $acceptsJson = isset($_SERVER['HTTP_ACCEPT']) 
        && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;
    
    if ($acceptsJson || $isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['data' => $data]);
    } else {
        $this->view('data', ['data' => $data]);
    }
}
```

#### After
```php
public function api(Request $request): void
{
    if ($request->expectsJson() || $request->isAjax()) {
        Response::json(['data' => $data]);
    } else {
        $this->view('data', ['data' => $data]);
    }
}
```

**Benefits:**
- Much cleaner and readable
- No manual header parsing
- Consistent response handling

---

### Pattern 5: File Uploads

#### Before
```php
public function upload(): void
{
    if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo 'No file uploaded';
        return;
    }
    
    $file = $_FILES['avatar'];
    $filename = $file['name'];
    $tmpPath = $file['tmp_name'];
    
    // Process file
}
```

#### After
```php
public function upload(Request $request): void
{
    if (!$request->hasFile('avatar')) {
        Response::json(['error' => 'No file uploaded'], 400);
        return;
    }
    
    $file = $request->file('avatar');
    $filename = $file['name'];
    $tmpPath = $file['tmp_name'];
    
    // Process file
}
```

**Benefits:**
- Cleaner file validation
- Consistent error handling
- Easier to test

---

### Pattern 6: Middleware

#### Before
```php
class AuthMiddleware
{
    public function handle(string $method): void
    {
        $token = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        
        if (empty($token)) {
            http_response_code(401);
            echo 'Unauthorized';
            exit;
        }
        
        // Validate token
    }
}
```

#### After
```php
use PhpLiteCore\Http\Request;
use PhpLiteCore\Http\Response;

class AuthMiddleware
{
    public function handle(Request $request): void
    {
        $token = $request->header('Authorization', '');
        
        if (empty($token)) {
            Response::json(['error' => 'Unauthorized'], 401);
        }
        
        // Validate token
    }
}
```

**Note:** Middleware with `string $method` signature still works, but Request signature is recommended.

---

### Pattern 7: JSON API Endpoints

#### Before
```php
public function apiCreate(): void
{
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    
    if (strpos($contentType, 'application/json') === false) {
        http_response_code(415);
        echo 'Unsupported Media Type';
        exit;
    }
    
    $rawBody = file_get_contents('php://input');
    $data = json_decode($rawBody, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo 'Invalid JSON';
        exit;
    }
    
    // Process $data
    
    http_response_code(201);
    header('Content-Type: application/json');
    echo json_encode(['id' => $id, 'status' => 'created']);
}
```

#### After
```php
public function apiCreate(Request $request): void
{
    if (!$request->isJson()) {
        Response::json(['error' => 'Unsupported Media Type'], 415);
        return;
    }
    
    $data = $request->json();
    
    if ($data === null) {
        Response::json(['error' => 'Invalid JSON'], 400);
        return;
    }
    
    // Process $data
    
    Response::json(['id' => $id, 'status' => 'created'], 201);
}
```

**Benefits:**
- Much cleaner and more readable
- Built-in JSON parsing and validation
- Consistent error handling

---

### Pattern 8: Complex Responses with Cookies

#### Before
```php
public function login(): void
{
    // Validate credentials
    
    $sessionId = bin2hex(random_bytes(32));
    
    setcookie('session_id', $sessionId, time() + 3600, '/', '', false, true);
    
    header('Content-Type: application/json');
    http_response_code(200);
    echo json_encode(['status' => 'success', 'session_id' => $sessionId]);
}
```

#### After
```php
public function login(Request $request): void
{
    // Validate credentials
    
    $sessionId = bin2hex(random_bytes(32));
    
    $response = new Response();
    $response
        ->setCookie('session_id', $sessionId, time() + 3600, '/', '', false, true)
        ->withJson(['status' => 'success', 'session_id' => $sessionId]);
    
    $response->send();
}
```

**Benefits:**
- Fluent interface for building responses
- All response configuration in one place
- Easier to test

---

## Step-by-Step Migration Process

### Step 1: Add Request Parameter to Controller Methods

Start by adding the `Request` parameter to controller methods that need it:

```php
// Before
public function store(): void

// After
public function store(Request $request): void
```

### Step 2: Replace Global Access with Request Methods

Replace direct global variable access:

```php
// Before
$value = $_POST['field'] ?? 'default';

// After
$value = $request->post('field', 'default');
```

### Step 3: Replace Manual Response with Response Class

```php
// Before
header('Content-Type: application/json');
http_response_code(200);
echo json_encode($data);

// After
Response::json($data, 200);
```

### Step 4: Test Your Changes

The new abstractions make testing easier:

```php
// Create test request
$request = new Request(
    ['page' => 1],
    ['username' => 'test'],
    ['REQUEST_METHOD' => 'POST']
);

// Test controller
$controller->store($request);
```

---

## Common Issues and Solutions

### Issue 1: Route Parameters and Request Object

**Problem:** When adding Request parameter, route parameters don't work.

**Solution:** Place Request parameter first, route parameters after:

```php
// Correct
public function show(Request $request, int $id): void

// Also correct (without Request)
public function show(int $id): void
```

The Router automatically detects if a method accepts Request and injects it before route parameters.

---

### Issue 2: Middleware Signature

**Problem:** Middleware with new signature not working.

**Solution:** Ensure parameter is type-hinted:

```php
// Correct - Request will be injected
public function handle(Request $request): void

// Legacy - string will be passed
public function handle(string $method): void
```

The Router detects the signature automatically.

---

### Issue 3: Static vs Instance Response Methods

**Problem:** Confusion about when to use static vs instance methods.

**Solution:**
- Use **static methods** for immediate responses that terminate execution:
  ```php
  Response::json($data);  // Sends and exits
  Response::redirect('/path');  // Redirects and exits
  ```

- Use **instance methods** for building complex responses:
  ```php
  $response = new Response();
  $response->withJson($data)->setCookie('session', 'id');
  $response->send();
  ```

---

## Testing Benefits

The new abstractions make testing much easier:

### Before (Hard to Test)
```php
class PostControllerTest extends TestCase
{
    public function testStore()
    {
        // Can't easily inject $_POST
        $_POST = ['title' => 'Test'];
        
        // Can't capture response
        ob_start();
        $controller->store();
        $output = ob_get_clean();
        
        // Parse output manually
        $data = json_decode($output, true);
        $this->assertEquals('Test', $data['title']);
    }
}
```

### After (Easy to Test)
```php
class PostControllerTest extends TestCase
{
    public function testStore()
    {
        // Create test request
        $request = new Request(
            [],
            ['title' => 'Test'],
            ['REQUEST_METHOD' => 'POST']
        );
        
        // Inject into controller
        $controller->store($request);
        
        // Assertions are cleaner
        $this->assertTrue($post->exists());
    }
}
```

---

## Timeline and Strategy

### Immediate (No Changes Required)
- All existing code continues to work
- No breaking changes

### Short Term (Recommended for new code)
- Use Request objects in new controllers
- Use Response objects for new API endpoints
- Migrate high-value controllers (API, auth)

### Long Term (Optional for existing code)
- Gradually migrate existing controllers
- Update middleware to use Request objects
- Refactor tests to use Request objects

---

## Getting Help

- **Documentation:** See [HTTP_REQUEST_RESPONSE_API.md](./HTTP_REQUEST_RESPONSE_API.md)
- **Examples:** Check the updated PostController in `app/Controllers/`
- **Issues:** Report issues on GitHub

---

## Summary

The new Request/Response abstractions provide:

✅ **Full backward compatibility** - nothing breaks  
✅ **Better testability** - easy to mock and test  
✅ **Cleaner code** - less boilerplate  
✅ **Type safety** - with type hints  
✅ **Consistency** - unified API for HTTP interactions  

**Recommendation:** Start using the new API for all new code, and gradually migrate existing code as you modify it.
