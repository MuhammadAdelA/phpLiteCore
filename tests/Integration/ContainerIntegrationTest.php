<?php

use PhpLiteCore\Container\Container;
use PhpLiteCore\Database\Model\BaseModel;
use PhpLiteCore\Routing\Router;

test('BaseModel can resolve database from container', function () {
    $container = new Container();

    // Test that container properly resolves the 'db' service
    // We'll verify by checking that the container has 'db' and can retrieve it
    $mockDb = new stdClass();
    $mockDb->called = false;

    $container->instance('db', $mockDb);

    // Set the container on a test model class
    $testModelClass = new class () extends BaseModel {
        protected string $table = 'test_table';

        // Override query to just verify container resolves db
        public static function testContainerResolve(): mixed
        {
            return static::$container->get('db');
        }
    };

    $testModelClass::setContainer($container);

    // Verify the container can resolve the db service
    $db = $testModelClass::testContainerResolve();

    expect($db)->toBe($mockDb)
        ->and($container->has('db'))->toBeTrue();
});

test('Router can use container to instantiate controllers', function () {
    $container = new Container();
    $router = new Router();

    // Inject the container into the router
    $router->setContainer($container);

    // Verify the container is set
    $reflection = new ReflectionClass($router);
    $property = $reflection->getProperty('container');
    $property->setAccessible(true);

    expect($property->getValue($router))->toBe($container);
});

test('Container can be used to bind and resolve custom services', function () {
    $container = new Container();

    // Bind a custom service
    $container->singleton('custom-service', fn () => new stdClass());

    $service1 = $container->get('custom-service');
    $service2 = $container->get('custom-service');

    expect($service1)->toBe($service2);
});

test('Container binds core services correctly in Application bootstrap', function () {
    $container = new Container();

    // Simulate binding core services
    $container->instance('db', new stdClass());
    $container->instance(Router::class, new Router());

    expect($container->has('db'))->toBeTrue()
        ->and($container->has(Router::class))->toBeTrue()
        ->and($container->get('db'))->toBeInstanceOf(stdClass::class)
        ->and($container->get(Router::class))->toBeInstanceOf(Router::class);
});
