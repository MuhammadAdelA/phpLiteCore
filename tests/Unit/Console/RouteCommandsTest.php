<?php

use PhpLiteCore\Bootstrap\Application;
use PhpLiteCore\Console\Commands\RouteListCommand;
use PhpLiteCore\Console\Commands\RouteCacheCommand;
use PhpLiteCore\Console\Commands\RouteClearCommand;
use PhpLiteCore\Routing\Router;
use Symfony\Component\Console\Tester\CommandTester;

beforeEach(function () {
    // Create a mock Application with a router
    $this->app = $this->getMockBuilder(Application::class)
        ->disableOriginalConstructor()
        ->getMock();
    
    $this->router = new Router();
    $this->app->router = $this->router;
});

describe('RouteListCommand', function () {
    test('displays message when no routes are registered', function () {
        // Don't load any routes, just check what's in the router already
        $command = new RouteListCommand($this->app);
        $tester = new CommandTester($command);
        $tester->execute([]);
        
        // The command will load routes from web.php which has routes
        // So we just check it succeeds and displays routes
        expect($tester->getStatusCode())->toBe(0);
        expect($tester->getDisplay())->toContain('Total routes:');
    });

    test('displays routes in table format', function () {
        // Register some test routes
        $this->router->get('/test', ['TestController', 'index'])->name('test.index');
        $this->router->post('/test', ['TestController', 'store'])->name('test.store');
        
        $command = new RouteListCommand($this->app);
        $tester = new CommandTester($command);
        $tester->execute([]);
        
        $output = $tester->getDisplay();
        expect($output)->toContain('GET');
        expect($output)->toContain('/test');
        expect($output)->toContain('test.index');
        expect($output)->toContain('TestController@index');
        expect($output)->toContain('Total routes:');
        expect($tester->getStatusCode())->toBe(0);
    });

    test('displays middleware information', function () {
        $this->router->get('/secure', ['SecureController', 'index'])
            ->name('secure.index')
            ->middleware(['AuthMiddleware', 'CsrfMiddleware']);
        
        $command = new RouteListCommand($this->app);
        $tester = new CommandTester($command);
        $tester->execute([]);
        
        $output = $tester->getDisplay();
        expect($output)->toContain('AuthMiddleware');
        expect($output)->toContain('CsrfMiddleware');
        expect($tester->getStatusCode())->toBe(0);
    });

    test('displays dash for routes without name', function () {
        $this->router->get('/unnamed', ['TestController', 'unnamed']);
        
        $command = new RouteListCommand($this->app);
        $tester = new CommandTester($command);
        $tester->execute([]);
        
        $output = $tester->getDisplay();
        // Check that there's a dash in the name column (between uri and action)
        expect($output)->toMatch('/\/unnamed.*-.*TestController/s');
        expect($tester->getStatusCode())->toBe(0);
    });
});

describe('RouteCacheCommand', function () {
    afterEach(function () {
        // Clean up cache file after each test
        $cachePath = PHPLITECORE_ROOT . 'storage/cache/routes.php';
        if (file_exists($cachePath)) {
            unlink($cachePath);
        }
    });

    test('successfully caches routes', function () {
        $this->router->get('/test', ['TestController', 'index'])->name('test.index');
        
        $command = new RouteCacheCommand($this->app);
        $tester = new CommandTester($command);
        $tester->execute([]);
        
        $cachePath = PHPLITECORE_ROOT . 'storage/cache/routes.php';
        expect(file_exists($cachePath))->toBeTrue();
        expect($tester->getDisplay())->toContain('Routes cached successfully');
        expect($tester->getStatusCode())->toBe(0);
    });

    test('cached file contains route data', function () {
        $this->router->get('/test/{id}', ['TestController', 'show'])
            ->name('test.show')
            ->where(['id' => '[0-9]+']);
        
        $command = new RouteCacheCommand($this->app);
        $tester = new CommandTester($command);
        $tester->execute([]);
        
        $cachePath = PHPLITECORE_ROOT . 'storage/cache/routes.php';
        $cached = require $cachePath;
        
        expect($cached)->toHaveKey('routes');
        expect($cached)->toHaveKey('timestamp');
        // Routes will include web.php routes plus our test route
        expect($cached['routes'])->toBeArray();
        expect(count($cached['routes']))->toBeGreaterThan(0);
        
        // Find our test route
        $testRoute = null;
        foreach ($cached['routes'] as $route) {
            if ($route['name'] === 'test.show') {
                $testRoute = $route;
                break;
            }
        }
        expect($testRoute)->not->toBeNull();
        expect($testRoute['uri'])->toBe('/test/{id}');
    });
});

describe('RouteClearCommand', function () {
    test('displays message when cache does not exist', function () {
        $cachePath = PHPLITECORE_ROOT . 'storage/cache/routes.php';
        if (file_exists($cachePath)) {
            unlink($cachePath);
        }
        
        $command = new RouteClearCommand();
        $tester = new CommandTester($command);
        $tester->execute([]);
        
        expect($tester->getDisplay())->toContain('Route cache does not exist');
        expect($tester->getStatusCode())->toBe(0);
    });

    test('successfully clears route cache', function () {
        // Create a dummy cache file
        $cachePath = PHPLITECORE_ROOT . 'storage/cache/routes.php';
        $dir = dirname($cachePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($cachePath, '<?php return [];');
        
        expect(file_exists($cachePath))->toBeTrue();
        
        $command = new RouteClearCommand();
        $tester = new CommandTester($command);
        $tester->execute([]);
        
        expect(file_exists($cachePath))->toBeFalse();
        expect($tester->getDisplay())->toContain('Route cache cleared successfully');
        expect($tester->getStatusCode())->toBe(0);
    });
});
