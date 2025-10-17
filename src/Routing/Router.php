<?php

declare(strict_types=1);

namespace PhpLiteCore\Routing;

use mysql_xdevapi\Exception;
use PhpLiteCore\Bootstrap\Application;
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
     * Each route contains method, uri, action, regex pattern, and parameter names.
     * @var array
     */
    protected array $routes = [];

    /**
     * The namespace prefix for controller classes.
     * @var string
     */
    protected string $controllerNamespace = 'App\\Controllers\\';

    /**
     * Add a new GET route to the collection.
     * @param string $uri The URI pattern (e.g., '/users', '/posts/{id}').
     * @param array $action The controller and method array [ControllerName::class, 'methodName'].
     */
    public function get(string $uri, array $action): void
    {
        $this->addRoute('GET', $uri, $action);
    }

    /**
     * Add a new POST route to the collection.
     * @param string $uri The URI pattern.
     * @param array $action The controller and method array.
     */
    public function post(string $uri, array $action): void
    {
        $this->addRoute('POST', $uri, $action);
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

        foreach ($this->routes as $route) {
            // Check if the request method matches the route's method.
            if ($route['method'] !== $requestMethod) {
                continue;
            }

            // Check if the request URI matches the route's regex pattern.
            if (preg_match($route['regex'], $requestUri, $matches)) {
                // Remove the full matched string (index 0) from the results.
                array_shift($matches);

                // Combine the extracted parameter values with their names if parameters exist.
                $params = !empty($route['params']) ? array_combine($route['params'], $matches) : [];

                // Call the controller action. This might throw specific exceptions handled above.
                $this->callAction($app, $route['action'][0], $route['action'][1], $params);
                return; // Stop processing routes once a match is found and dispatched.
            }
        }

        // If no route was matched after checking all registered routes, send a 404 response.
        Response::notFound('Page Not Found');
    }

    /**
     * Converts a route URI with placeholders (e.g., {id}) into a regex pattern
     * and adds the route definition to the internal collection.
     *
     * @param string $method The HTTP method (GET, POST, etc.).
     * @param string $uri The URI pattern.
     * @param array $action The controller and method array.
     * @return void
     */
    protected function addRoute(string $method, string $uri, array $action): void
    {
        $params = [];
        // Find all {param} occurrences in the URI, expecting alphanumeric names.
        preg_match_all('/\{([a-zA-Z][a-zA-Z0-9_]*)\}/', $uri, $paramMatches);
        if (!empty($paramMatches[1])) {
            $params = $paramMatches[1]; // Store the names of the parameters.
        }

        // Convert the URI pattern into a valid regex pattern for matching.
        // Replace {param} placeholders with a capturing group that matches any character except '/'.
        $regex = preg_replace('/\{[a-zA-Z][a-zA-Z0-9_]*\}/', '([^/]+)', $uri);
        // Add start (^) and end ($) anchors and delimiters (#).
        $regex = '#^' . $regex . '$#';

        // Store the complete route definition.
        $this->routes[] = [
            'method' => strtoupper($method),
            'uri'    => $uri,          // Original URI pattern
            'action' => $action,       // Controller and method
            'regex'  => $regex,        // Compiled regex pattern
            'params' => $params,       // Names of parameters in order
        ];
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

        // Instantiate the controller, passing the application instance (dependency injection).
        $controllerInstance = new $fullControllerName($app);

        // Check if the action method exists on the controller instance.
        if (!method_exists($controllerInstance, $method)) {
            throw new MethodNotFoundException("Method {$method} not found on controller {$fullControllerName}.");
        }

        // Call the action method on the controller instance, passing the extracted parameters.
        call_user_func_array([$controllerInstance, $method], $params);
    }
}