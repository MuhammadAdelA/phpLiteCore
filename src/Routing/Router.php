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
        // Create Request object from globals
        $request = \PhpLiteCore\Http\Request::createFromGlobals();
        
        $requestMethod = $request->getMethod();
        $requestUri = $request->getPath();

        // Execute global middleware before routing
        $this->runGlobalMiddleware($request);

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
                $this->runRouteMiddleware($route, $request);

                // Call the controller action.
                $this->callAction($app, $route->getAction()[0], $route->getAction()[1], $params, $request);
                return; // Stop processing routes once a match is found and dispatched.
            }
        }

        // If no route was matched after checking all registered routes, send a 404 response.
        \PhpLiteCore\Http\Response::notFound('Page Not Found');
    }

    /**
     * Execute all registered global middleware.
     * 
     * @param \PhpLiteCore\Http\Request $request The HTTP request object
     * @return void
     */
    protected function runGlobalMiddleware(\PhpLiteCore\Http\Request $request): void
    {
        foreach ($this->middleware as $middleware) {
            if (method_exists($middleware, 'handle')) {
                // Check if middleware accepts Request object as parameter
                $reflection = new \ReflectionMethod($middleware, 'handle');
                $params = $reflection->getParameters();
                
                if (!empty($params) && $params[0]->getType() && $params[0]->getType()->getName() === \PhpLiteCore\Http\Request::class) {
                    $middleware->handle($request);
                } else {
                    // Legacy support: pass method string
                    $middleware->handle($request->getMethod());
                }
            }
        }
    }

    /**
     * Execute route-specific middleware.
     * 
     * @param Route $route The route instance
     * @param \PhpLiteCore\Http\Request $request The HTTP request object
     * @return void
     */
    protected function runRouteMiddleware(Route $route, \PhpLiteCore\Http\Request $request): void
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
                // Check if middleware accepts Request object as parameter
                $reflection = new \ReflectionMethod($middleware, 'handle');
                $params = $reflection->getParameters();
                
                if (!empty($params) && $params[0]->getType()) {
                    $type = $params[0]->getType();
                    $acceptsRequest = false;
                    if ($type instanceof \ReflectionNamedType) {
                        $acceptsRequest = $type->getName() === \PhpLiteCore\Http\Request::class;
                    } elseif ($type instanceof \ReflectionUnionType) {
                        foreach ($type->getTypes() as $unionType) {
                            if ($unionType instanceof \ReflectionNamedType && $unionType->getName() === \PhpLiteCore\Http\Request::class) {
                                $acceptsRequest = true;
                                break;
                            }
                        }
                    }
                    if ($acceptsRequest) {
                        $middleware->handle($request);
                    } else {
                        // Legacy support: pass method string
                        $middleware->handle($request->getMethod());
                    }
                } else {
                    // Legacy support: pass method string
                    $middleware->handle($request->getMethod());
                }
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
        
        // Set the router reference so route can register itself when named
        $route->setRouter($this);
        
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
     * Register a named route for lookup.
     * This is called internally by Route::name() to track named routes.
     * 
     * @param string $name The route name
     * @param Route $route The route instance
     * @return void
     */
    public function registerNamedRoute(string $name, Route $route): void
    {
        $this->namedRoutes[$name] = $route;
    }

    /**
     * Get a route by its name.
     * 
     * @param string $name The route name
     * @return Route|null The route instance or null if not found
     */
    public function getNamedRoute(string $name): ?Route
    {
        return $this->namedRoutes[$name] ?? null;
    }

    /**
     * Generate a URL for a named route with optional parameters.
     * 
     * @param string $name The route name
     * @param array $params The route parameters
     * @return string The generated URL
     * @throws \InvalidArgumentException If route not found or parameters are missing
     */
    public function route(string $name, array $params = []): string
    {
        $route = $this->getNamedRoute($name);
        
        if ($route === null) {
            throw new \InvalidArgumentException("Route [{$name}] not found.");
        }
        
        $uri = $route->getUri();
        $routeParams = $route->getParams();
        
        // Check if all required parameters are provided
        foreach ($routeParams as $param) {
            if (!isset($params[$param])) {
                throw new \InvalidArgumentException("Missing required parameter [{$param}] for route [{$name}].");
            }
        }
        
        // Replace placeholders with actual values
        foreach ($params as $key => $value) {
            $uri = preg_replace('/\{' . preg_quote($key, '/') . '\}/', (string)$value, $uri, 1);
        }
        
        return $uri;
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
            
            // Set router reference so the route can register itself when named
            $route->setRouter($this);
            
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
     * @param \PhpLiteCore\Http\Request $request The HTTP request object.
     * @return void
     * @throws ControllerNotFoundException|MethodNotFoundException
     */
    protected function callAction(Application $app, string $controller, string $method, array $params = [], \PhpLiteCore\Http\Request $request = null): void
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

                // Bind Request instance if available
                if ($request !== null && !$this->container->has(\PhpLiteCore\Http\Request::class)) {
                    $this->container->instance(\PhpLiteCore\Http\Request::class, $request);
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

        // Determine if the controller method expects a Request object
        $reflection = new \ReflectionMethod($controllerInstance, $method);
        $methodParams = $reflection->getParameters();
        
        // Check if any parameter accepts Request object
        $acceptsRequest = false;
        foreach ($methodParams as $param) {
            $type = $param->getType();
            if ($type && !$type->isBuiltin()) {
                if ($type instanceof \ReflectionUnionType) {
                    foreach ($type->getTypes() as $unionType) {
                        if ($unionType instanceof \ReflectionNamedType && $unionType->getName() === \PhpLiteCore\Http\Request::class) {
                            $acceptsRequest = true;
                            break 2;
                        }
                    }
                } elseif ($type instanceof \ReflectionNamedType && $type->getName() === \PhpLiteCore\Http\Request::class) {
                    $acceptsRequest = true;
                    break;
                }
            }
        }
        
        // If method accepts Request object, prepend it to params
        if ($acceptsRequest && $request !== null) {
            array_unshift($params, $request);
        }

        // Call the action method on the controller instance, passing the extracted parameters.
        call_user_func_array([$controllerInstance, $method], $params);
    }
}