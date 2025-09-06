<?php

declare(strict_types=1);

namespace PhpLiteCore\Routing;

use PhpLiteCore\Http\Response;

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

    /**
     * Add a new GET route to the collection.
     *
     * @param string $uri The URI pattern.
     * @param array $action The controller and method to call [Controller::class, 'method'].
     * @return void
     */
    public function get(string $uri, array $action): void
    {
        $this->addRoute('GET', $uri, $action);
    }

    /**
     * Add a new POST route to the collection.
     *
     * @param string $uri The URI pattern.
     * @param array $action The controller and method to call [Controller::class, 'method'].
     * @return void
     */
    public function post(string $uri, array $action): void
    {
        $this->addRoute('POST', $uri, $action);
    }

    /**
     * Resolve the current request and dispatch to the appropriate action.
     *
     * @return void
     */
    public function dispatch(): void
    {
        $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        foreach ($this->routes as $route) {
            // Check if the method and URI match the registered route.
            // Note: This is a simple exact match. We will add support for parameters like /users/{id} later.
            if ($route['method'] === $requestMethod && $route['uri'] === $requestUri) {
                $this->callAction($route['action'][0], $route['action'][1]);
                return; // Stop processing once a match is found.
            }
        }

        // If no route was matched, send a 404 response.
        Response::notFound('Page Not Found');
    }

    /**
     * Add a route to the routes collection.
     *
     * @param string $method The HTTP method.
     * @param string $uri The URI pattern.
     * @param array $action The controller and method.
     * @return void
     */
    protected function addRoute(string $method, string $uri, array $action): void
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'uri'    => $uri,
            'action' => $action,
        ];
    }

    /**
     * Instantiate the controller and call the action method.
     *
     * @param string $controller The controller class name.
     * @param string $method The method name.
     * @return void
     */
    protected function callAction(string $controller, string $method): void
    {
        $fullControllerName = $this->controllerNamespace . $controller;

        if (!class_exists($fullControllerName)) {
            // In a real app, this should throw a more specific exception.
            throw new \Exception("Controller class {$fullControllerName} not found.");
        }

        $controllerInstance = new $fullControllerName();

        if (!method_exists($controllerInstance, $method)) {
            throw new \Exception("Method {$method} not found on controller {$fullControllerName}.");
        }

        // Call the controller method.
        $controllerInstance->{$method}();
    }
}