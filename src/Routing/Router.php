<?php

declare(strict_types=1);

namespace PhpLiteCore\Routing;

use PhpLiteCore\Bootstrap\Application;
use PhpLiteCore\Container\Container;
use PhpLiteCore\Http\Response;
// Import the specific exceptions
use PhpLiteCore\Routing\Exceptions\ControllerNotFoundException;
use PhpLiteCore\Routing\Exceptions\MethodNotFoundException;

/**
 * The Router class is responsible for matching incoming HTTP requests to controller actions.
 * It supports dynamic route parameters and different HTTP methods.
 */
class Router
{
    /**
     * The array of registered routes.
     * Each route is a Route instance.
     * @var Route[]
     */
    protected array $routes = [];

    /**
     * Named routes for easy lookup.
     * @var array
     */
    protected array $namedRoutes = [];

    /**
     * The namespace prefix for controller classes.
     * @var string
     */
    protected string $controllerNamespace = 'App\\Controllers\\';

    /**
     * The array of global middleware to run before routing.
     * @var array
     */
    protected array $middleware = [];

    /**
     * The container instance for dependency injection.
     * @var Container|null
     */
    protected ?Container $container = null;

    /**
     * Current group attributes (prefix, as, middleware).
     * @var array
     */
    protected array $groupStack = [];

    /**
     * Add a new GET route to the collection.
     * @param string $uri The URI pattern (e.g., '/users', '/posts/{id}').
     * @param array $action The controller and method array [ControllerName::class, 'methodName'].
     * @return Route The route instance for fluent chaining
     */
    public function get(string $uri, array $action): Route
    {
        return $this->addRoute('GET', $uri, $action);
    }

    /**
     * Add a new POST route to the collection.
     * @param string $uri The URI pattern.
     * @param array $action The controller and method array.
     * @return Route The route instance for fluent chaining
     */
    public function post(string $uri, array $action): Route
    {
        return $this->addRoute('POST', $uri, $action);
    }

    /**
     * Create a route group with shared attributes.
     * 
     * @param array $attributes Group attributes (prefix, as, middleware)
     * @param callable $callback Callback to register routes within the group
     * @return void
     */
    public function group(array $attributes, callable $callback): void
    {
        // Push current group attributes onto the stack
        $this->groupStack[] = $attributes;
        
        // Execute the callback to register routes
        $callback($this);
        
        // Pop the group attributes from the stack
        array_pop($this->groupStack);
    }

    /**
     * Register a global middleware to run before routing.
     * @param object $middleware The middleware instance.
     * @return void
     */
    public function addMiddleware(object $middleware): void
    {
        $this->middleware[] = $middleware;
    }

    /**
     * Set the container instance for dependency injection.
     * @param Container $container The container instance.
     * @return void
     */
    public function setContainer(Container $container): void
    {
        $this->container = $container;
    }

    /**
     * Resolve the current request URI and HTTP method against registered routes
     * and dispatch to the appropriate controller action.
     *
     * @param Application $app The main application instance.
     * @return void
     * @throws
     * */
    public function dispatch(Application $app): void
    {
        $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Execute global middleware before routing
        $this->runGlobalMiddleware($requestMethod);

        foreach ($this->routes as $route) {
            // Check if the request method matches the route's method.
            if ($route->getMethod() !== $requestMethod) {
                continue;
            }

            // Check if the request URI matches the route's regex pattern.
            if (preg_match($route->getRegex(), $requestUri, $matches)) {
                // Remove the full matched string (index 0) from the results.
                array_shift($matches);

                // Combine the extracted parameter values with their names if parameters exist.
                $params = !empty($route->getParams()) ? array_combine($route->getParams(), $matches) : [];

                // Execute route-specific middleware
                $this->runRouteMiddleware($route, $requestMethod);

                // Call the controller action.
                $this->callAction($app, $route->getAction()[0], $route->getAction()[1], $params);
                return; // Stop processing routes once a match is found and dispatched.
            }
        }

        // If no route was matched after checking all registered routes, send a 404 response.
        Response::notFound('Page Not Found');
    }

    /**
     * Execute all registered global middleware.
     * 
     * @param string $method The HTTP request method.
     * @return void
     */
    protected function runGlobalMiddleware(string $method): void
    {
        foreach ($this->middleware as $middleware) {
            if (method_exists($middleware, 'handle')) {
                $middleware->handle($method);
            }
        }
    }

    /**
     * Execute route-specific middleware.
     * 
     * @param Route $route The route instance
     * @param string $method The HTTP request method
     * @return void
     */
    protected function runRouteMiddleware(Route $route, string $method): void
    {
        foreach ($route->getMiddleware() as $middlewareClass) {
            // Instantiate the middleware
            if ($this->container !== null && $this->container->has($middlewareClass)) {
                $middleware = $this->container->make($middlewareClass);
            } else {
                $middleware = new $middlewareClass();
            }
            
            // Execute the middleware handle method
            if (method_exists($middleware, 'handle')) {
                $middleware->handle($method);
            }
        }
    }

    /**
     * Converts a route URI with placeholders (e.g., {id}) into a Route object
     * and adds the route definition to the internal collection.
     *
     * @param string $method The HTTP method (GET, POST, etc.).
     * @param string $uri The URI pattern.
     * @param array $action The controller and method array.
     * @return Route The newly created route instance
     */
    protected function addRoute(string $method, string $uri, array $action): Route
    {
        // Apply group attributes if we're inside a group
        $uri = $this->applyGroupPrefix($uri);
        $route = new Route($method, $uri, $action);
        
        // Apply group middleware if any
        $groupMiddleware = $this->getGroupMiddleware();
        if (!empty($groupMiddleware)) {
            $route->middleware($groupMiddleware);
        }
        
        // Store the route
        $this->routes[] = $route;
        
        return $route;
    }

    /**
     * Apply the group prefix to the given URI.
     *
     * @param string $uri The URI pattern
     * @return string The URI with group prefix applied
     */
    protected function applyGroupPrefix(string $uri): string
    {
        $prefix = '';
        foreach ($this->groupStack as $group) {
            if (isset($group['prefix'])) {
                $prefix .= '/' . trim($group['prefix'], '/');
            }
        }
        
        if ($prefix !== '') {
            $uri = rtrim($prefix, '/') . '/' . ltrim($uri, '/');
        }
        
        return $uri;
    }

    /**
     * Get all middleware from the group stack.
     *
     * @return array
     */
    protected function getGroupMiddleware(): array
    {
        $middleware = [];
        foreach ($this->groupStack as $group) {
            if (isset($group['middleware'])) {
                $groupMiddleware = is_array($group['middleware']) 
                    ? $group['middleware'] 
                    : [$group['middleware']];
                $middleware = array_merge($middleware, $groupMiddleware);
            }
        }
        return $middleware;
    }

    /**
     * Load routes from cache file.
     * 
     * @param string $cachePath Path to the cache file
     * @return bool True if cache was loaded successfully
     */
    public function loadFromCache(string $cachePath): bool
    {
        if (!file_exists($cachePath)) {
            return false;
        }
        
        $cached = require $cachePath;
        
        if (!is_array($cached) || !isset($cached['routes'])) {
            return false;
        }
        
        // Reconstruct Route objects from cached data
        foreach ($cached['routes'] as $routeData) {
            $route = new Route(
                $routeData['method'],
                $routeData['uri'],
                $routeData['action']
            );
            
            if (isset($routeData['name'])) {
                $route->name($routeData['name']);
            }
            
            if (!empty($routeData['constraints'])) {
                $route->where($routeData['constraints']);
            }
            
            if (!empty($routeData['middleware'])) {
                $route->middleware($routeData['middleware']);
            }
            
            $this->routes[] = $route;
        }
        
        return true;
    }

    /**
     * Save current routes to cache file.
     * 
     * @param string $cachePath Path to save the cache file
     * @return bool True if cache was saved successfully
     */
    public function saveToCache(string $cachePath): bool
    {
        $routesData = array_map(function (Route $route) {
            return $route->toArray();
        }, $this->routes);
        
        $cacheData = [
            'routes' => $routesData,
            'timestamp' => time(),
        ];
        
        $content = '<?php return ' . var_export($cacheData, true) . ';';
        
        // Ensure directory exists
        $dir = dirname($cachePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        return file_put_contents($cachePath, $content) !== false;
    }

    /**
     * Instantiate the controller and call the action method, passing route parameters.
     *
     * @param Application $app The application instance (for dependency injection).
     * @param string $controller The controller class name (short name).
     * @param string $method The method name on the controller.
     * @param array $params The parameters extracted from the URI, to be passed to the method.
     * @return void
     * @throws ControllerNotFoundException|MethodNotFoundException
     */
    protected function callAction(Application $app, string $controller, string $method, array $params = []): void
    {
        // Construct the fully qualified controller class name.
        $fullControllerName = $this->controllerNamespace . $controller;

        // Check if the controller class exists.
        if (!class_exists($fullControllerName)) {
            throw new ControllerNotFoundException("Controller class {$fullControllerName} not found.");
        }

        // Try to instantiate the controller using the container if available
        if ($this->container !== null) {
            try {
                // First, bind Application class to the container if not already bound
                if (!$this->container->has(Application::class)) {
                    $this->container->instance(Application::class, $app);
                }

                // Try to auto-wire the controller using the container
                $controllerInstance = $this->container->make($fullControllerName);
            } catch (\ReflectionException $e) {
                // Fallback to the old way if container instantiation fails
                $controllerInstance = new $fullControllerName($app);
            }
        } else {
            // Fallback: Instantiate the controller, passing the application instance (dependency injection).
            $controllerInstance = new $fullControllerName($app);
        }

        // Check if the action method exists on the controller instance.
        if (!method_exists($controllerInstance, $method)) {
            throw new MethodNotFoundException("Method {$method} not found on controller {$fullControllerName}.");
        }

        // Call the action method on the controller instance, passing the extracted parameters.
        call_user_func_array([$controllerInstance, $method], $params);
    }
}