<?php

declare(strict_types=1);

namespace PhpLiteCore\Routing;

use Exception;
use PhpLiteCore\Bootstrap\Application;
use PhpLiteCore\Http\Response;
use PhpLiteCore\Routing\Exceptions\ControllerNotFoundException;
use PhpLiteCore\Routing\Exceptions\MethodNotFoundException;

class Router
{
    /**
     * The array of registered routes.
     * @var array
     */
    protected array $routes = [];

    /**
     * The namespace for the controllers.
     * @var string
     */
    protected string $controllerNamespace = 'App\\Controllers\\';

    public function get(string $uri, array $action): void
    {
        $this->addRoute('GET', $uri, $action);
    }

    public function post(string $uri, array $action): void
    {
        $this->addRoute('POST', $uri, $action);
    }

    /**
     * Resolve the current request and dispatch to the appropriate action.
     *
     * @param Application $app The main application instance.
     * @return void
     * @throws Exception
     */
    public function dispatch(Application $app): void
    {
        $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        foreach ($this->routes as $route) {
            // Check if the request method matches
            if ($route['method'] !== $requestMethod) {
                continue;
            }

            // Check if the URI matches the route's regex pattern
            if (preg_match($route['regex'], $requestUri, $matches)) {
                // Remove the full match from the beginning of the array
                array_shift($matches);

                // Combine the extracted parameter values with their names
                $params = array_combine($route['params'], $matches);

                $this->callAction($app, $route['action'][0], $route['action'][1], $params);
                return;
            }
        }

        // If no route was matched, send a 404 response.
        Response::notFound('Page Not Found');
    }

    /**
     * Converts a route URI with placeholders into a regex pattern
     * and adds it to the routes' collection.
     *
     * @param string $method
     * @param string $uri e.g., /users/{id}
     * @param array $action
     * @return void
     */
    protected function addRoute(string $method, string $uri, array $action): void
    {
        $params = [];
        // Find all {param} occurrences in the URI
        preg_match_all('/\{([a-zA-Z][a-zA-Z0-9_]*)\}/', $uri, $paramMatches);
        if (!empty($paramMatches[1])) {
            $params = $paramMatches[1];
        }

        // Convert the URI into a valid regex pattern
        // This replaces {param} with a capturing group ([^/]+)
        $regex = preg_replace('/\{[a-zA-Z][a-zA-Z0-9_]*\}/', '([^/]+)', $uri);
        $regex = '#^' . $regex . '$#';

        $this->routes[] = [
            'method' => strtoupper($method),
            'uri'    => $uri,
            'action' => $action,
            'regex'  => $regex,
            'params' => $params,
        ];
    }

    /**
     * Instantiate the controller and call the action method, passing route parameters.
     *
     * @param Application $app
     * @param string $controller
     * @param string $method
     * @param array $params The parameters extracted from the URI.
     * @return void
     * @throws ControllerNotFoundException If the controller class does not exist.
     * @throws MethodNotFoundException If the method does not exist on the controller.
     */
    protected function callAction(Application $app, string $controller, string $method, array $params = []): void
    {
        $fullControllerName = $this->controllerNamespace . $controller;

        if (!class_exists($fullControllerName)) {
            // Throw a specific, descriptive exception.
            throw new ControllerNotFoundException("Controller class {$fullControllerName} not found.");
        }

        $controllerInstance = new $fullControllerName($app);

        if (!method_exists($controllerInstance, $method)) {
            // Throw another specific, descriptive exception.
            throw new MethodNotFoundException("Method {$method} not found on controller {$fullControllerName}.");
        }

        // Pass the extracted parameters as arguments to the controller method.
        call_user_func_array([$controllerInstance, $method], $params);
    }
}