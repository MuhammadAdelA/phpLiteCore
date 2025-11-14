# Building REST APIs with phpLiteCore

This guide demonstrates how to build RESTful APIs using phpLiteCore.

## Table of Contents

- [Basic Setup](#basic-setup)
- [Creating API Controllers](#creating-api-controllers)
- [Route Configuration](#route-configuration)
- [Request Handling](#request-handling)
- [Response Format](#response-format)
- [Authentication](#authentication)
- [Rate Limiting](#rate-limiting)
- [Error Handling](#error-handling)
- [Best Practices](#best-practices)

## Basic Setup

phpLiteCore provides a clean and simple way to build REST APIs. Here's how to get started:

### 1. Create an API Controller

```php
<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use PhpLiteCore\Http\Request;
use PhpLiteCore\Http\Response;

class ApiController extends BaseController
{
    /**
     * Return JSON response
     */
    protected function jsonResponse(mixed $data, int $status = 200): void
    {
        Response::json($data, $status);
    }

    /**
     * Return error response
     */
    protected function errorResponse(string $message, int $status = 400): void
    {
        Response::json([
            'error' => true,
            'message' => $message
        ], $status);
    }

    /**
     * Return success response
     */
    protected function successResponse(mixed $data, string $message = ''): void
    {
        $response = [
            'success' => true,
            'data' => $data
        ];

        if ($message) {
            $response['message'] = $message;
        }

        Response::json($response);
    }
}
```

## Creating API Controllers

### Users API Example

```php
<?php

namespace App\Controllers\Api;

use App\Models\User;
use PhpLiteCore\Http\Request;
use PhpLiteCore\Validation\Validator;
use PhpLiteCore\Validation\Exceptions\ValidationException;

class UsersController extends ApiController
{
    /**
     * GET /api/users
     * List all users
     */
    public function index(Request $request): void
    {
        $page = (int) $request->input('page', 1);
        $perPage = (int) $request->input('per_page', 15);

        $result = User::paginate($perPage, $page);

        $this->successResponse([
            'users' => $result['data'],
            'pagination' => [
                'current_page' => $result['current_page'],
                'total_pages' => $result['total_pages'],
                'per_page' => $perPage,
                'total' => $result['total']
            ]
        ]);
    }

    /**
     * GET /api/users/{id}
     * Get a single user
     */
    public function show(Request $request, int $id): void
    {
        $user = User::find($id);

        if (!$user) {
            $this->errorResponse('User not found', 404);
            return;
        }

        $this->successResponse($user);
    }

    /**
     * POST /api/users
     * Create a new user
     */
    public function store(Request $request): void
    {
        try {
            $data = Validator::validate($_POST, [
                'username' => 'required|min:3|max:50',
                'email' => 'required|email',
                'password' => 'required|min:8'
            ]);

            // Hash password
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

            // Create user
            $userId = User::query()->insert($data);

            $user = User::find($userId);

            $this->successResponse($user, 'User created successfully');
        } catch (ValidationException $e) {
            $this->errorResponse(
                'Validation failed: ' . implode(', ', $e->getErrors())
            );
        }
    }

    /**
     * PUT /api/users/{id}
     * Update a user
     */
    public function update(Request $request, int $id): void
    {
        $user = User::find($id);

        if (!$user) {
            $this->errorResponse('User not found', 404);
            return;
        }

        try {
            $data = Validator::validate($_POST, [
                'username' => 'min:3|max:50',
                'email' => 'email'
            ]);

            // Update user
            User::query()->where('id', '=', $id)->update($data);

            $updatedUser = User::find($id);

            $this->successResponse($updatedUser, 'User updated successfully');
        } catch (ValidationException $e) {
            $this->errorResponse(
                'Validation failed: ' . implode(', ', $e->getErrors())
            );
        }
    }

    /**
     * DELETE /api/users/{id}
     * Delete a user
     */
    public function delete(Request $request, int $id): void
    {
        $user = User::find($id);

        if (!$user) {
            $this->errorResponse('User not found', 404);
            return;
        }

        User::query()->where('id', '=', $id)->delete();

        $this->successResponse(null, 'User deleted successfully');
    }
}
```

## Route Configuration

Define your API routes in `routes/web.php`:

```php
<?php

use App\Controllers\Api\UsersController;
use App\Controllers\Api\PostsController;

// API Routes
$router->group(['prefix' => '/api'], function($router) {
    
    // Users endpoints
    $router->get('/users', [UsersController::class, 'index']);
    $router->get('/users/{id}', [UsersController::class, 'show'])
           ->where(['id' => '[0-9]+']);
    $router->post('/users', [UsersController::class, 'store']);
    $router->post('/users/{id}', [UsersController::class, 'update'])
           ->where(['id' => '[0-9]+']);
    $router->post('/users/{id}/delete', [UsersController::class, 'delete'])
           ->where(['id' => '[0-9]+']);
    
    // Posts endpoints
    $router->get('/posts', [PostsController::class, 'index']);
    $router->get('/posts/{id}', [PostsController::class, 'show'])
           ->where(['id' => '[0-9]+']);
    $router->post('/posts', [PostsController::class, 'store']);
});
```

## Best Practices

### 1. Version Your API

```php
$router->group(['prefix' => '/api/v1'], function($router) {
    // Version 1 routes
});

$router->group(['prefix' => '/api/v2'], function($router) {
    // Version 2 routes
});
```

### 2. Use Proper HTTP Status Codes

- `200 OK` - Successful GET, PUT, PATCH
- `201 Created` - Successful POST
- `204 No Content` - Successful DELETE
- `400 Bad Request` - Validation errors
- `401 Unauthorized` - Authentication required
- `403 Forbidden` - Authenticated but not authorized
- `404 Not Found` - Resource doesn't exist
- `429 Too Many Requests` - Rate limit exceeded
- `500 Internal Server Error` - Server errors

### 3. Use Caching for Performance

```php
use PhpLiteCore\Utils\Cache;

$cache = new Cache();

$users = $cache->remember('all_users', function() {
    return User::all();
}, 300); // Cache for 5 minutes
```

### 4. Log API Requests

```php
use PhpLiteCore\Utils\Logger;

$logger = new Logger();
$logger->info('API Request', [
    'endpoint' => $request->getPath(),
    'method' => $request->getMethod(),
    'ip' => $request->getClientIp()
]);
```

For more examples and detailed documentation, visit the [phpLiteCore documentation](https://muhammadadela.github.io/phpLiteCore/).
