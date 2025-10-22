<?php

declare(strict_types=1);

namespace PhpLiteCore\Routing;

/**
 * The Route class represents a single route definition with its metadata.
 * Supports fluent interface for chaining name(), where(), and middleware() methods.
 */
class Route
{
    /**
     * The HTTP method for this route.
     * @var string
     */
    protected string $method;

    /**
     * The URI pattern for this route.
     * @var string
     */
    protected string $uri;

    /**
     * The controller and method array [ControllerName, 'methodName'].
     * @var array
     */
    protected array $action;

    /**
     * The name of this route (optional).
     * @var string|null
     */
    protected ?string $name = null;

    /**
     * Parameter constraints for this route.
     * Array of ['param' => 'regex'] pairs.
     * @var array
     */
    protected array $constraints = [];

    /**
     * Middleware specific to this route.
     * @var array
     */
    protected array $middleware = [];

    /**
     * The compiled regex pattern for matching.
     * @var string|null
     */
    protected ?string $regex = null;

    /**
     * The parameter names extracted from the URI.
     * @var array
     */
    protected array $params = [];

    /**
     * Create a new Route instance.
     *
     * @param string $method The HTTP method
     * @param string $uri The URI pattern
     * @param array $action The controller and method array
     */
    public function __construct(string $method, string $uri, array $action)
    {
        $this->method = strtoupper($method);
        $this->uri = $uri;
        $this->action = $action;
        
        // Extract parameter names
        preg_match_all('/\{([a-zA-Z][a-zA-Z0-9_]*)\}/', $uri, $paramMatches);
        if (!empty($paramMatches[1])) {
            $this->params = $paramMatches[1];
        }
    }

    /**
     * Set the name for this route.
     *
     * @param string $name The route name
     * @return $this
     */
    public function name(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Set parameter constraints for this route.
     *
     * @param array $constraints Array of ['param' => 'regex'] pairs
     * @return $this
     */
    public function where(array $constraints): self
    {
        $this->constraints = array_merge($this->constraints, $constraints);
        return $this;
    }

    /**
     * Set middleware for this route.
     *
     * @param array|string $middleware Middleware class name(s)
     * @return $this
     */
    public function middleware(array|string $middleware): self
    {
        if (is_string($middleware)) {
            $middleware = [$middleware];
        }
        $this->middleware = array_merge($this->middleware, $middleware);
        return $this;
    }

    /**
     * Get the HTTP method.
     *
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Get the URI pattern.
     *
     * @return string
     */
    public function getUri(): string
    {
        return $this->uri;
    }

    /**
     * Get the action (controller and method).
     *
     * @return array
     */
    public function getAction(): array
    {
        return $this->action;
    }

    /**
     * Get the route name.
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Get the parameter constraints.
     *
     * @return array
     */
    public function getConstraints(): array
    {
        return $this->constraints;
    }

    /**
     * Get the route-specific middleware.
     *
     * @return array
     */
    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    /**
     * Get the parameter names.
     *
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * Get the compiled regex pattern for matching.
     * Compiles the pattern on first access if not already compiled.
     *
     * @return string
     */
    public function getRegex(): string
    {
        if ($this->regex === null) {
            $this->regex = $this->compilePattern();
        }
        return $this->regex;
    }

    /**
     * Compile the URI pattern into a regex pattern.
     * Uses constraints if defined, otherwise defaults to [^/]+
     *
     * @return string
     */
    protected function compilePattern(): string
    {
        $pattern = $this->uri;
        
        // Replace each {param} with its constraint or default pattern
        foreach ($this->params as $param) {
            $constraint = $this->constraints[$param] ?? '[^/]+';
            $pattern = preg_replace(
                '/\{' . preg_quote($param, '/') . '\}/',
                '(' . $constraint . ')',
                $pattern,
                1
            );
        }
        
        // Add start and end anchors with delimiters
        return '#^' . $pattern . '$#';
    }

    /**
     * Convert the route to an array for storage/caching.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'method' => $this->method,
            'uri' => $this->uri,
            'action' => $this->action,
            'name' => $this->name,
            'constraints' => $this->constraints,
            'middleware' => $this->middleware,
            'regex' => $this->getRegex(),
            'params' => $this->params,
        ];
    }
}
