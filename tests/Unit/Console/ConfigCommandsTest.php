<?php

use PhpLiteCore\Console\Commands\ConfigCacheCommand;
use PhpLiteCore\Console\Commands\ConfigClearCommand;
use Symfony\Component\Console\Tester\CommandTester;

if (!defined('PHPLITECORE_ROOT')) {
    define('PHPLITECORE_ROOT', __DIR__ . '/../../../');
}

describe('ConfigCacheCommand', function () {
    beforeEach(function () {
        // Clean up any existing cache
        $cachePath = PHPLITECORE_ROOT . 'storage/cache/config.php';
        if (file_exists($cachePath)) {
            unlink($cachePath);
        }
    });

    afterEach(function () {
        // Clean up after tests
        $cachePath = PHPLITECORE_ROOT . 'storage/cache/config.php';
        if (file_exists($cachePath)) {
            unlink($cachePath);
        }
    });

    it('successfully caches configuration', function () {
        $command = new ConfigCacheCommand();
        $tester = new CommandTester($command);
        $tester->execute([]);
        
        $cachePath = PHPLITECORE_ROOT . 'storage/cache/config.php';
        expect(file_exists($cachePath))->toBeTrue();
        expect($tester->getDisplay())->toContain('Configuration cached successfully');
        expect($tester->getStatusCode())->toBe(0);
    });

    it('creates cache file with correct structure', function () {
        $command = new ConfigCacheCommand();
        $tester = new CommandTester($command);
        $tester->execute([]);
        
        $cachePath = PHPLITECORE_ROOT . 'storage/cache/config.php';
        $cached = require $cachePath;
        
        expect($cached)->toHaveKey('config');
        expect($cached)->toHaveKey('timestamp');
        expect($cached['config'])->toBeArray();
        expect($cached['timestamp'])->toBeInt();
    });

    it('caches configuration from config directory', function () {
        $command = new ConfigCacheCommand();
        $tester = new CommandTester($command);
        $tester->execute([]);
        
        $cachePath = PHPLITECORE_ROOT . 'storage/cache/config.php';
        $cached = require $cachePath;
        
        // Check if app config is cached
        expect($cached['config'])->toHaveKey('app');
        expect($cached['config']['app'])->toBeArray();
    });
});

describe('ConfigClearCommand', function () {
    beforeEach(function () {
        // Create a cache file to be cleared
        $cachePath = PHPLITECORE_ROOT . 'storage/cache/config.php';
        $cacheDir = dirname($cachePath);
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }
        
        $data = [
            'config' => ['test' => 'value'],
            'timestamp' => time(),
        ];
        
        file_put_contents($cachePath, '<?php' . PHP_EOL . PHP_EOL . 'return ' . var_export($data, true) . ';');
    });

    it('successfully clears cached configuration', function () {
        $cachePath = PHPLITECORE_ROOT . 'storage/cache/config.php';
        expect(file_exists($cachePath))->toBeTrue();
        
        $command = new ConfigClearCommand();
        $tester = new CommandTester($command);
        $tester->execute([]);
        
        expect(file_exists($cachePath))->toBeFalse();
        expect($tester->getDisplay())->toContain('Configuration cache cleared successfully');
        expect($tester->getStatusCode())->toBe(0);
    });

    it('handles clearing when cache does not exist', function () {
        // Clear the cache first
        $cachePath = PHPLITECORE_ROOT . 'storage/cache/config.php';
        if (file_exists($cachePath)) {
            unlink($cachePath);
        }
        
        $command = new ConfigClearCommand();
        $tester = new CommandTester($command);
        $tester->execute([]);
        
        expect($tester->getDisplay())->toContain('Configuration cache cleared successfully');
        expect($tester->getStatusCode())->toBe(0);
    });
});
