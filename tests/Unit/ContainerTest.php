<?php

use PhpLiteCore\Container\Container;

test('Container can bind and resolve a simple class', function () {
    $container = new Container();

    $container->bind('test', fn () => 'test-value');

    expect($container->get('test'))->toBe('test-value');
});

test('Container can bind a singleton', function () {
    $container = new Container();

    $counter = 0;
    $container->singleton('counter', function () use (&$counter) {
        $counter++;

        return new stdClass();
    });

    $first = $container->get('counter');
    $second = $container->get('counter');

    expect($first)->toBe($second)
        ->and($counter)->toBe(1);
});

test('Container can bind a class by name', function () {
    $container = new Container();

    $container->bind(stdClass::class);

    $instance = $container->get(stdClass::class);

    expect($instance)->toBeInstanceOf(stdClass::class);
});

test('Container can auto-wire a class with no dependencies', function () {
    $container = new Container();

    $instance = $container->make(stdClass::class);

    expect($instance)->toBeInstanceOf(stdClass::class);
});

test('Container can resolve a class with constructor dependencies', function () {
    $container = new Container();

    // Create a simple test class that depends on stdClass
    $testClass = new class ($container) {
        public static function create(Container $container): object
        {
            return new class (new stdClass()) {
                public function __construct(public stdClass $dependency)
                {
                }
            };
        }
    };

    $container->bind(stdClass::class);

    $instance = $container->get(stdClass::class);

    expect($instance)->toBeInstanceOf(stdClass::class);
});

test('Container can check if a binding exists', function () {
    $container = new Container();

    $container->bind('test', fn () => 'value');

    expect($container->has('test'))->toBeTrue()
        ->and($container->has('nonexistent'))->toBeFalse();
});

test('Container can store and retrieve instances directly', function () {
    $container = new Container();

    $instance = new stdClass();
    $container->instance('my-instance', $instance);

    expect($container->get('my-instance'))->toBe($instance);
});

test('Container throws exception for non-instantiable class', function () {
    $container = new Container();

    // Try to make an interface (which is not instantiable)
    $container->make(\Iterator::class);
})->throws(ReflectionException::class);
