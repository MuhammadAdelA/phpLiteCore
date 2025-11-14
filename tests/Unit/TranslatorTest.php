<?php

use PhpLiteCore\Lang\Translator;

beforeEach(function () {
    // Define PHPLITECORE_ROOT constant if not already defined
    if (!defined('PHPLITECORE_ROOT')) {
        define('PHPLITECORE_ROOT', dirname(__DIR__, 2) . DIRECTORY_SEPARATOR);
    }
});

describe('Translator Basic Functionality', function () {
    it('can retrieve a simple translation key', function () {
        $translator = new Translator('en');
        $result = $translator->get('welcome');
        expect($result)->toContain('Welcome');
    });

    it('can retrieve a nested translation key', function () {
        $translator = new Translator('en');
        $result = $translator->get('messages.home.page_title');
        expect($result)->toBe('Welcome to phpLiteCore');
    });

    it('returns the key itself if translation is not found', function () {
        $translator = new Translator('en');
        $result = $translator->get('messages.nonexistent.key');
        expect($result)->toBe('messages.nonexistent.key');
    });

    it('returns default value if translation is not found and default is provided', function () {
        $translator = new Translator('en');
        $result = $translator->get('messages.nonexistent.key', [], 'Default Value');
        expect($result)->toBe('Default Value');
    });

    it('replaces placeholders with colon syntax', function () {
        $translator = new Translator('en');
        $result = $translator->get('welcome', ['name' => 'John']);
        expect($result)->toBe('Welcome, John!');
    });

    it('replaces multiple placeholders', function () {
        $translator = new Translator('en');
        $result = $translator->get('messages.posts.not_found', ['id' => '123']);
        expect($result)->toBe('Post with ID 123 not found.');
    });

    it('can lazy-load translation files', function () {
        $translator = new Translator('en');
        $result = $translator->get('validation.required', ['field' => 'email']);
        expect($result)->toBe('The email field is required.');
    });
});

describe('Translator Fallback Locale', function () {
    it('falls back to fallback locale when key is not found in current locale', function () {
        // Create a test locale directory with missing keys
        $testLangPath = sys_get_temp_dir() . '/phplitecore_test_lang_' . uniqid();
        mkdir($testLangPath . '/fr', 0777, true);
        mkdir($testLangPath . '/en', 0777, true);
        
        // Create French file with missing key
        file_put_contents($testLangPath . '/fr/messages.php', '<?php return ["hello" => "Bonjour"];');
        
        // Create English file with the key
        file_put_contents($testLangPath . '/en/messages.php', '<?php return ["hello" => "Hello", "welcome" => "Welcome"];');
        
        $translator = new Translator('fr', $testLangPath, 'en');
        
        // This key exists only in fallback
        $result = $translator->get('welcome');
        expect($result)->toBe('Welcome');
        
        // This key exists in both, should use primary
        $result = $translator->get('hello');
        expect($result)->toBe('Bonjour');
        
        // Cleanup
        unlink($testLangPath . '/fr/messages.php');
        unlink($testLangPath . '/en/messages.php');
        rmdir($testLangPath . '/fr');
        rmdir($testLangPath . '/en');
        rmdir($testLangPath);
    });

    it('uses primary locale when key exists in both', function () {
        $translator = new Translator('ar', null, 'en');
        $result = $translator->get('guest');
        expect($result)->toBe('زائر'); // Should return Arabic, not English
    });

    it('does not try fallback when primary and fallback are the same', function () {
        $translator = new Translator('en', null, 'en');
        $result = $translator->get('messages.nonexistent.key');
        expect($result)->toBe('messages.nonexistent.key');
    });
});

describe('Translator Pluralization', function () {
    it('returns singular form when count is 1', function () {
        // Create a test locale with pluralization
        $testLangPath = sys_get_temp_dir() . '/phplitecore_test_lang_' . uniqid();
        mkdir($testLangPath . '/en', 0777, true);
        file_put_contents($testLangPath . '/en/messages.php', '<?php return ["files" => ":count file|:count files"];');
        
        $translator = new Translator('en', $testLangPath);
        $result = $translator->getChoice('files', 1);
        expect($result)->toBe('1 file');
        
        // Cleanup
        unlink($testLangPath . '/en/messages.php');
        rmdir($testLangPath . '/en');
        rmdir($testLangPath);
    });

    it('returns plural form when count is not 1', function () {
        $testLangPath = sys_get_temp_dir() . '/phplitecore_test_lang_' . uniqid();
        mkdir($testLangPath . '/en', 0777, true);
        file_put_contents($testLangPath . '/en/messages.php', '<?php return ["files" => ":count file|:count files"];');
        
        $translator = new Translator('en', $testLangPath);
        $result = $translator->getChoice('files', 5);
        expect($result)->toBe('5 files');
        
        // Cleanup
        unlink($testLangPath . '/en/messages.php');
        rmdir($testLangPath . '/en');
        rmdir($testLangPath);
    });

    it('returns plural form when count is 0', function () {
        $testLangPath = sys_get_temp_dir() . '/phplitecore_test_lang_' . uniqid();
        mkdir($testLangPath . '/en', 0777, true);
        file_put_contents($testLangPath . '/en/messages.php', '<?php return ["items" => ":count item|:count items"];');
        
        $translator = new Translator('en', $testLangPath);
        $result = $translator->getChoice('items', 0);
        expect($result)->toBe('0 items');
        
        // Cleanup
        unlink($testLangPath . '/en/messages.php');
        rmdir($testLangPath . '/en');
        rmdir($testLangPath);
    });

    it('replaces custom placeholders in pluralized strings', function () {
        $testLangPath = sys_get_temp_dir() . '/phplitecore_test_lang_' . uniqid();
        mkdir($testLangPath . '/en', 0777, true);
        file_put_contents($testLangPath . '/en/messages.php', '<?php return ["minutes" => ":count minute ago by :user|:count minutes ago by :user"];');
        
        $translator = new Translator('en', $testLangPath);
        $result = $translator->getChoice('minutes', 5, ['user' => 'John']);
        expect($result)->toBe('5 minutes ago by John');
        
        // Cleanup
        unlink($testLangPath . '/en/messages.php');
        rmdir($testLangPath . '/en');
        rmdir($testLangPath);
    });

    it('handles missing plural part gracefully', function () {
        $testLangPath = sys_get_temp_dir() . '/phplitecore_test_lang_' . uniqid();
        mkdir($testLangPath . '/en', 0777, true);
        file_put_contents($testLangPath . '/en/messages.php', '<?php return ["single" => "Only singular form"];');
        
        $translator = new Translator('en', $testLangPath);
        $result = $translator->getChoice('single', 5);
        expect($result)->toBe('Only singular form');
        
        // Cleanup
        unlink($testLangPath . '/en/messages.php');
        rmdir($testLangPath . '/en');
        rmdir($testLangPath);
    });
});

describe('Translator has() method', function () {
    it('returns true when key exists', function () {
        $translator = new Translator('en');
        expect($translator->has('welcome'))->toBeTrue();
        expect($translator->has('messages.home.page_title'))->toBeTrue();
    });

    it('returns false when key does not exist', function () {
        $translator = new Translator('en');
        expect($translator->has('messages.nonexistent.key'))->toBeFalse();
        expect($translator->has('nonexistent'))->toBeFalse();
    });

    it('returns true when key exists in fallback locale', function () {
        $testLangPath = sys_get_temp_dir() . '/phplitecore_test_lang_' . uniqid();
        mkdir($testLangPath . '/fr', 0777, true);
        mkdir($testLangPath . '/en', 0777, true);
        
        file_put_contents($testLangPath . '/fr/messages.php', '<?php return ["hello" => "Bonjour"];');
        file_put_contents($testLangPath . '/en/messages.php', '<?php return ["hello" => "Hello", "welcome" => "Welcome"];');
        
        $translator = new Translator('fr', $testLangPath, 'en');
        
        // Key exists only in fallback
        expect($translator->has('welcome'))->toBeTrue();
        
        // Cleanup
        unlink($testLangPath . '/fr/messages.php');
        unlink($testLangPath . '/en/messages.php');
        rmdir($testLangPath . '/fr');
        rmdir($testLangPath . '/en');
        rmdir($testLangPath);
    });
});

describe('Translator edge cases', function () {
    it('handles empty replacement arrays', function () {
        $translator = new Translator('en');
        $result = $translator->get('guest', []);
        expect($result)->toBe('Guest');
    });

    it('handles numeric replacement values', function () {
        $translator = new Translator('en');
        $result = $translator->get('messages.posts.not_found', ['id' => 42]);
        expect($result)->toBe('Post with ID 42 not found.');
    });

    it('handles float numbers in getChoice', function () {
        $testLangPath = sys_get_temp_dir() . '/phplitecore_test_lang_' . uniqid();
        mkdir($testLangPath . '/en', 0777, true);
        file_put_contents($testLangPath . '/en/messages.php', '<?php return ["hours" => ":count hour|:count hours"];');
        
        $translator = new Translator('en', $testLangPath);
        $result = $translator->getChoice('hours', 1.5);
        expect($result)->toBe('1.5 hours');
        
        // Cleanup
        unlink($testLangPath . '/en/messages.php');
        rmdir($testLangPath . '/en');
        rmdir($testLangPath);
    });

    it('handles keys with multiple dots', function () {
        $translator = new Translator('en');
        $result = $translator->get('messages.home.card_docs_title');
        expect($result)->toBe('Read the Docs');
    });

    it('auto-prepends messages file when no dot in key', function () {
        $translator = new Translator('en');
        $result = $translator->get('guest');
        expect($result)->toBe('Guest');
    });
});
