<?php

declare(strict_types=1);

namespace PhpLiteCore\Container;

use Closure;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;

/**
 * A minimal Service Container (IoC) for dependency injection.
 * Supports bind, singleton, get, and simple auto-wiring via Reflection.
 */
class Container
{
    /**
     * The container's bindings.
     * @var array
     */
    protected array $bindings = [];

    /**
     * The container's singleton instances.
     * @var array
     */
    protected array $instances = [];

    /**
     * Register a binding in the container.
     *
     * @param string $abstract The abstract type or identifier.
     * @param Closure|string|null $concrete The concrete implementation or factory.
     * @param bool $shared Whether this binding is a singleton.
     * @return void
     */
    public function bind(string $abstract, Closure|string|null $concrete = null, bool $shared = false): void
    {
        // If no concrete is provided, use the abstract as concrete
        if ($concrete === null) {
            $concrete = $abstract;
        }

        $this->bindings[$abstract] = [
            'concrete' => $concrete,
            'shared' => $shared,
        ];
    }

    /**
     * Register a singleton binding in the container.
     *
     * @param string $abstract The abstract type or identifier.
     * @param Closure|string|null $concrete The concrete implementation or factory.
     * @return void
     */
    public function singleton(string $abstract, Closure|string|null $concrete = null): void
    {
        $this->bind($abstract, $concrete, true);
    }

    /**
     * Resolve the given type from the container.
     *
     * @param string $abstract The abstract type or identifier to resolve.
     * @return mixed
     * @throws ReflectionException
     */
    public function get(string $abstract): mixed
    {
        // If we have a singleton instance, return it
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        // If we have a binding, use it
        if (isset($this->bindings[$abstract])) {
            $binding = $this->bindings[$abstract];
            $concrete = $binding['concrete'];

            // Build the concrete
            $object = $this->build($concrete);

            // If it's shared (singleton), store the instance
            if ($binding['shared']) {
                $this->instances[$abstract] = $object;
            }

            return $object;
        }

        // No binding found, try to auto-wire the abstract directly
        return $this->make($abstract);
    }

    /**
     * Instantiate a concrete instance using auto-wiring.
     *
     * @param string $concrete The concrete class to instantiate.
     * @return mixed
     * @throws ReflectionException
     */
    public function make(string $concrete): mixed
    {
        return $this->build($concrete);
    }

    /**
     * Build an instance of the given concrete type.
     *
     * @param Closure|string $concrete The concrete type or factory.
     * @return mixed
     * @throws ReflectionException
     */
    protected function build(Closure|string $concrete): mixed
    {
        // If concrete is a Closure, invoke it with the container
        if ($concrete instanceof Closure) {
            return $concrete($this);
        }

        // Otherwise, use reflection to auto-wire the class
        $reflector = new ReflectionClass($concrete);

        // Check if the class is instantiable
        if (! $reflector->isInstantiable()) {
            throw new ReflectionException("Class {$concrete} is not instantiable.");
        }

        $constructor = $reflector->getConstructor();

        // If there's no constructor, just instantiate the class
        if ($constructor === null) {
            return new $concrete();
        }

        // Get constructor parameters and resolve dependencies
        $parameters = $constructor->getParameters();
        $dependencies = [];

        foreach ($parameters as $parameter) {
            $type = $parameter->getType();

            // If parameter has no type or is not a class, we can't auto-wire it
            if (! $type instanceof ReflectionNamedType || $type->isBuiltin()) {
                // Check if it has a default value
                if ($parameter->isDefaultValueAvailable()) {
                    $dependencies[] = $parameter->getDefaultValue();
                } else {
                    throw new ReflectionException(
                        "Cannot resolve parameter \${$parameter->getName()} in {$concrete}"
                    );
                }
            } else {
                // Resolve the dependency from the container
                $dependencies[] = $this->get($type->getName());
            }
        }

        return $reflector->newInstanceArgs($dependencies);
    }

    /**
     * Determine if the given abstract type has been bound or resolved as a singleton.
     *
     * @param string $abstract The abstract type to check.
     * @return bool
     */
    public function has(string $abstract): bool
    {
        return isset($this->bindings[$abstract]) || isset($this->instances[$abstract]);
    }

    /**
     * Set an instance directly in the container (useful for existing instances).
     *
     * @param string $abstract The abstract type or identifier.
     * @param mixed $instance The instance to store.
     * @return void
     */
    public function instance(string $abstract, mixed $instance): void
    {
        $this->instances[$abstract] = $instance;
    }
}
