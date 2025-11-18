# Reverse Route URL Generation

## Overview

phpLiteCore provides powerful reverse route URL generation capabilities, allowing you to generate URLs from named routes with parameters. This feature makes it easier to maintain your application's URLs and ensures consistency across your codebase.

## Table of Contents

1. [Naming Routes](#naming-routes)
2. [Generating URLs](#generating-urls)
3. [Routes with Parameters](#routes-with-parameters)
4. [Routes with Constraints](#routes-with-constraints)
5. [Grouped Routes](#grouped-routes)
6. [Usage Examples](#usage-examples)
7. [API Reference](#api-reference)

## Naming Routes

To generate URLs from routes, you first need to name your routes using the `name()` method when defining them:

```php
// In routes/web.php

// Simple route without parameters
$router->get('/home', ['HomeController', 'index'])->name('home');

// Route with parameters
$router->get('/posts/{id}', ['PostController', 'show'])->name('posts.show');

// Route with multiple parameters
$router->get('/users/{userId}/posts/{postId}', ['UserPostController', 'show'])
    ->name('users.posts.show');
```

## Generating URLs

There are three ways to generate URLs from named routes:

### 1. Using the `route()` Helper Function (Recommended)

The global `route()` helper function is the simplest and most commonly used method:

```php
// Generate URL for a simple route
$url = route('home');
// Result: '/home'

// Generate URL with parameters
$url = route('posts.show', ['id' => 123]);
// Result: '/posts/123'

// Generate URL with multiple parameters
$url = route('users.posts.show', ['userId' => 5, 'postId' => 42]);
// Result: '/users/5/posts/42'
```

### 2. Using the Router Instance

You can also use the router instance directly:

```php
$app = Application::getInstance();
$url = $app->router->route('posts.show', ['id' => 123]);
```

### 3. Using UrlUtils Class

For an object-oriented approach, use the `UrlUtils` class:

```php
use PhpLiteCore\Utils\UrlUtils;

$url = UrlUtils::route('posts.show', ['id' => 123]);
```

## Routes with Parameters

When generating URLs for routes with parameters, you must provide all required parameters:

```php
// Define a route with a parameter
$router->get('/posts/{id}', ['PostController', 'show'])->name('posts.show');

// Generate URL - parameter is required
$url = route('posts.show', ['id' => 123]);
// Result: '/posts/123'

// Missing parameter will throw an exception
try {
    $url = route('posts.show'); // Missing 'id' parameter
} catch (InvalidArgumentException $e) {
    // "Missing required parameter [id] for route [posts.show]."
}
```

### Multiple Parameters

Routes can have multiple parameters:

```php
// Define route
$router->get('/categories/{categoryId}/posts/{postId}', ['PostController', 'showInCategory'])
    ->name('categories.posts.show');

// Generate URL
$url = route('categories.posts.show', [
    'categoryId' => 10,
    'postId' => 42
]);
// Result: '/categories/10/posts/42'
```

### String Parameters

Parameters can be strings as well:

```php
// Define route
$router->get('/posts/{slug}', ['PostController', 'showBySlug'])
    ->name('posts.byslug');

// Generate URL
$url = route('posts.byslug', ['slug' => 'my-first-post']);
// Result: '/posts/my-first-post'
```

## Routes with Constraints

Route constraints don't affect URL generation, but they ensure the generated URL matches the expected format:

```php
// Define route with constraint
$router->get('/posts/{id}', ['PostController', 'show'])
    ->name('posts.show')
    ->where(['id' => '[0-9]+']);

// Generate URL - works as expected
$url = route('posts.show', ['id' => 456]);
// Result: '/posts/456'

// Note: The constraint only affects route matching, not URL generation
// So you could generate '/posts/abc' if you pass ['id' => 'abc'],
// but it wouldn't match when accessed
```

## Grouped Routes

Routes defined within groups automatically include the group prefix in generated URLs:

### Simple Group

```php
$router->group(['prefix' => 'api'], function ($router) {
    $router->get('/users', ['UserController', 'index'])->name('api.users');
});

// Generate URL
$url = route('api.users');
// Result: '/api/users'
```

### Nested Groups

```php
$router->group(['prefix' => 'api'], function ($router) {
    $router->group(['prefix' => 'v1'], function ($router) {
        $router->get('/users/{id}', ['UserController', 'show'])
            ->name('api.v1.users.show');
    });
});

// Generate URL
$url = route('api.v1.users.show', ['id' => 99]);
// Result: '/api/v1/users/99'
```

## Usage Examples

### In Controllers

```php
use PhpLiteCore\Bootstrap\Application;

class PostController extends BaseController
{
    public function store()
    {
        // Create post logic...
        $postId = 123;
        
        // Redirect to the show page using route name
        $url = route('posts.show', ['id' => $postId]);
        header("Location: $url");
        exit;
    }
    
    public function index()
    {
        $posts = Post::all();
        
        // Generate URLs for each post
        foreach ($posts as $post) {
            $post->url = route('posts.show', ['id' => $post->id]);
        }
        
        return view('posts.index', ['posts' => $posts]);
    }
}
```

### In Views

```php
<!-- In a view file -->
<nav>
    <a href="<?= route('home') ?>">Home</a>
    <a href="<?= route('posts.index') ?>">Posts</a>
    <a href="<?= route('about') ?>">About</a>
</nav>

<!-- Link to a specific post -->
<a href="<?= route('posts.show', ['id' => $post->id]) ?>">
    <?= e($post->title) ?>
</a>

<!-- Form action -->
<form action="<?= route('posts.store') ?>" method="POST">
    <?= csrf_field() ?>
    <!-- Form fields -->
</form>
```

### In CLI Commands

```php
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateReportCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Generate report...
        
        // Generate URL for the report
        $url = route('reports.show', ['id' => $reportId]);
        $output->writeln("Report available at: $url");
        
        return Command::SUCCESS;
    }
}
```

### Dynamic Menu Generation

```php
$menuItems = [
    ['name' => 'Home', 'route' => 'home'],
    ['name' => 'Posts', 'route' => 'posts.index'],
    ['name' => 'About', 'route' => 'about'],
];

foreach ($menuItems as $item) {
    echo '<a href="' . route($item['route']) . '">' . $item['name'] . '</a>';
}
```

### API URL Generation

```php
// Generate API endpoint URLs
$response = [
    'data' => $post,
    'links' => [
        'self' => route('api.posts.show', ['id' => $post->id]),
        'edit' => route('api.posts.update', ['id' => $post->id]),
        'delete' => route('api.posts.destroy', ['id' => $post->id]),
        'author' => route('api.users.show', ['id' => $post->user_id]),
    ]
];
```

## API Reference

### Router Methods

#### `registerNamedRoute(string $name, Route $route): void`

Registers a named route for lookup. This is called automatically when you use the `name()` method on a route.

#### `getNamedRoute(string $name): ?Route`

Retrieves a route by its name. Returns `null` if the route is not found.

```php
$route = $app->router->getNamedRoute('posts.show');
if ($route !== null) {
    echo "Route URI: " . $route->getUri();
}
```

#### `route(string $name, array $params = []): string`

Generates a URL for a named route with optional parameters.

**Parameters:**
- `$name` (string): The name of the route
- `$params` (array): Associative array of parameter values

**Returns:** The generated URL as a string

**Throws:** `InvalidArgumentException` if the route is not found or if required parameters are missing

### Helper Function

#### `route(string $name, array $params = []): string`

Global helper function for generating URLs.

```php
$url = route('posts.show', ['id' => 123]);
```

### UrlUtils Methods

#### `UrlUtils::route(string $name, array $params = []): string`

Static method for generating URLs using the OOP approach.

```php
use PhpLiteCore\Utils\UrlUtils;

$url = UrlUtils::route('posts.show', ['id' => 123]);
```

## Error Handling

### Route Not Found

If you try to generate a URL for a non-existent route:

```php
try {
    $url = route('nonexistent.route');
} catch (InvalidArgumentException $e) {
    // Handle error: "Route [nonexistent.route] not found."
}
```

### Missing Parameters

If you don't provide all required parameters:

```php
try {
    $url = route('posts.show'); // Missing 'id' parameter
} catch (InvalidArgumentException $e) {
    // Handle error: "Missing required parameter [id] for route [posts.show]."
}
```

## Best Practices

1. **Always name your routes**: Even if you don't think you'll need to generate URLs for them initially, it's good practice to name all routes.

2. **Use descriptive names**: Use a consistent naming convention like `resource.action` (e.g., `posts.show`, `users.edit`).

3. **Group related routes**: Use route groups with prefixes to organize your routes logically.

4. **Validate parameters**: Ensure parameters exist before passing them to the `route()` function.

5. **Use in views**: Generate URLs in views using the `route()` helper instead of hardcoding paths.

6. **Cache routes in production**: Use route caching in production for better performance (routes can be cached and named route mappings are preserved).

## Testing

The route URL generation feature is thoroughly tested. See `tests/Unit/RouteUrlGenerationTest.php` for comprehensive test examples covering:

- Simple routes without parameters
- Routes with single and multiple parameters
- Routes with constraints
- Nested and grouped routes
- Error handling (missing routes and parameters)
- Route caching preservation
- String and numeric parameters

## Comparison with Other Frameworks

If you're coming from other frameworks:

- **Laravel**: Similar to Laravel's `route()` helper function
- **Symfony**: Similar to Symfony's URL generation with the Router service
- **CodeIgniter**: Similar to CodeIgniter's `route_to()` helper

## Conclusion

Reverse route URL generation makes your application more maintainable by:

- Centralizing URL definitions in one place (routes file)
- Making it easy to change URLs without updating code throughout your application
- Providing type safety with parameter validation
- Enabling clean, readable code in controllers and views

For more information on routing, see the main routing documentation.
