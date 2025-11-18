<?php

use PhpLiteCore\Console\Commands\ConfigCacheCommand;
use PhpLiteCore\Console\Commands\ConfigClearCommand;
use Symfony\Component\Console\Tester\CommandTester;

describe('ConfigCacheCommand', function () {
    afterEach(function () {
        // Clean up cache file after each test
        $cachePath = PHPLITECORE_ROOT . 'storage/cache/config.php';
        if (file_exists($cachePath)) {
            unlink($cachePath);
        }
    });

    test('fails when .env file does not exist', function () {
        // Backup and remove .env temporarily
        $envPath = PHPLITECORE_ROOT . '.env';
        $backupPath = PHPLITECORE_ROOT . '.env.backup.test';
        
        if (file_exists($envPath)) {
            rename($envPath, $backupPath);
        }
        
        $command = new ConfigCacheCommand();
        $tester = new CommandTester($command);
        $tester->execute([]);
        
        // Restore .env
        if (file_exists($backupPath)) {
            rename($backupPath, $envPath);
        }
        
        expect($tester->getDisplay())->toContain('.env file not found');
        expect($tester->getStatusCode())->toBe(1);
    });

    test('successfully caches configuration from .env', function () {
        $command = new ConfigCacheCommand();
        $tester = new CommandTester($command);
        $tester->execute([]);
        
        $cachePath = PHPLITECORE_ROOT . 'storage/cache/config.php';
        expect(file_exists($cachePath))->toBeTrue();
        expect($tester->getDisplay())->toContain('Configuration cached successfully');
        expect($tester->getStatusCode())->toBe(0);
    });

    test('cached file contains configuration data', function () {
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

    test('parses environment variables correctly', function () {
        // Create a temporary .env file
        $envPath = PHPLITECORE_ROOT . '.env';
        $backupPath = PHPLITECORE_ROOT . '.env.backup.test';
        
        if (file_exists($envPath)) {
            rename($envPath, $backupPath);
        }
        
        $testEnv = <<<ENV
APP_ENV=test
APP_DEBUG=true
DB_HOST=localhost
# This is a comment
EMPTY_LINE_BELOW=value

QUOTED="some value"
SINGLE_QUOTED='another value'
ENV;
        file_put_contents($envPath, $testEnv);
        
        $command = new ConfigCacheCommand();
        $tester = new CommandTester($command);
        $tester->execute([]);
        
        $cachePath = PHPLITECORE_ROOT . 'storage/cache/config.php';
        $cached = require $cachePath;
        
        expect($cached['config']['APP_ENV'])->toBe('test');
        expect($cached['config']['APP_DEBUG'])->toBe('true');
        expect($cached['config']['DB_HOST'])->toBe('localhost');
        expect($cached['config']['QUOTED'])->toBe('some value');
        expect($cached['config']['SINGLE_QUOTED'])->toBe('another value');
        
        // Restore original .env
        unlink($envPath);
        if (file_exists($backupPath)) {
            rename($backupPath, $envPath);
        }
    });
});

describe('ConfigClearCommand', function () {
    test('displays message when cache does not exist', function () {
        $cachePath = PHPLITECORE_ROOT . 'storage/cache/config.php';
        if (file_exists($cachePath)) {
            unlink($cachePath);
        }
        
        $command = new ConfigClearCommand();
        $tester = new CommandTester($command);
        $tester->execute([]);
        
        expect($tester->getDisplay())->toContain('Configuration cache does not exist');
        expect($tester->getStatusCode())->toBe(0);
    });

    test('successfully clears configuration cache', function () {
        // Create a dummy cache file
        $cachePath = PHPLITECORE_ROOT . 'storage/cache/config.php';
        $dir = dirname($cachePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($cachePath, '<?php return [];');
        
        expect(file_exists($cachePath))->toBeTrue();
        
        $command = new ConfigClearCommand();
        $tester = new CommandTester($command);
        $tester->execute([]);
        
        expect(file_exists($cachePath))->toBeFalse();
        expect($tester->getDisplay())->toContain('Configuration cache cleared successfully');
        expect($tester->getStatusCode())->toBe(0);
    });
});
