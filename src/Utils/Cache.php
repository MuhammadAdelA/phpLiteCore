<?php

declare(strict_types=1);

namespace PhpLiteCore\Utils;

/**
 * Simple File-based Cache
 * 
 * Provides basic caching functionality with file-based storage.
 * Supports TTL (time-to-live) for cache entries.
 */
class Cache
{
    /**
     * @var string The directory where cache files are stored
     */
    private string $cacheDirectory;

    /**
     * @var int Default TTL in seconds (1 hour)
     */
    private int $defaultTtl = 3600;

    /**
     * Constructor
     * 
     * @param string $cacheDirectory The directory where cache files will be stored
     * @param int $defaultTtl Default time-to-live in seconds
     */
    public function __construct(string $cacheDirectory = '', int $defaultTtl = 3600)
    {
        $this->cacheDirectory = $cacheDirectory ?: (defined('PHPLITECORE_ROOT') ? PHPLITECORE_ROOT . 'storage/cache' : __DIR__ . '/../../storage/cache');
        $this->defaultTtl = $defaultTtl;
        
        // Create cache directory if it doesn't exist
        if (!is_dir($this->cacheDirectory)) {
            mkdir($this->cacheDirectory, 0755, true);
        }
    }

    /**
     * Store an item in the cache
     *
     * @param string $key The cache key
     * @param mixed $value The value to cache
     * @param int|null $ttl Time-to-live in seconds (null = use default)
     * @return bool True on success, false on failure
     */
    public function set(string $key, mixed $value, ?int $ttl = null): bool
    {
        $ttl = $ttl ?? $this->defaultTtl;
        $filePath = $this->getFilePath($key);
        
        $data = [
            'value' => $value,
            'expires_at' => time() + $ttl,
        ];

        $serialized = serialize($data);
        
        return file_put_contents($filePath, $serialized, LOCK_EX) !== false;
    }

    /**
     * Retrieve an item from the cache
     *
     * @param string $key The cache key
     * @param mixed $default Default value if key doesn't exist or is expired
     * @return mixed The cached value or default
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $filePath = $this->getFilePath($key);
        
        if (!file_exists($filePath)) {
            return $default;
        }

        $content = file_get_contents($filePath);
        
        if ($content === false) {
            return $default;
        }

        $data = unserialize($content);
        
        // Check if expired
        if (time() > $data['expires_at']) {
            $this->forget($key);
            
            return $default;
        }

        return $data['value'];
    }

    /**
     * Check if an item exists in the cache and is not expired
     *
     * @param string $key The cache key
     * @return bool True if exists and not expired, false otherwise
     */
    public function has(string $key): bool
    {
        $filePath = $this->getFilePath($key);
        
        if (!file_exists($filePath)) {
            return false;
        }

        $content = file_get_contents($filePath);
        
        if ($content === false) {
            return false;
        }

        $data = unserialize($content);
        
        // Check if expired
        if (time() > $data['expires_at']) {
            $this->forget($key);
            
            return false;
        }

        return true;
    }

    /**
     * Remove an item from the cache
     *
     * @param string $key The cache key
     * @return bool True on success, false on failure
     */
    public function forget(string $key): bool
    {
        $filePath = $this->getFilePath($key);
        
        if (file_exists($filePath)) {
            return unlink($filePath);
        }

        return true;
    }

    /**
     * Store an item in the cache indefinitely
     *
     * @param string $key The cache key
     * @param mixed $value The value to cache
     * @return bool True on success, false on failure
     */
    public function forever(string $key, mixed $value): bool
    {
        // Set a very long TTL (10 years)
        return $this->set($key, $value, 315360000);
    }

    /**
     * Get an item from cache or store the default value
     *
     * @param string $key The cache key
     * @param callable $callback Callback to generate value if not cached
     * @param int|null $ttl Time-to-live in seconds
     * @return mixed The cached or generated value
     */
    public function remember(string $key, callable $callback, ?int $ttl = null): mixed
    {
        if ($this->has($key)) {
            return $this->get($key);
        }

        $value = $callback();
        $this->set($key, $value, $ttl);
        
        return $value;
    }

    /**
     * Clear all items from the cache
     *
     * @return bool True on success, false on failure
     */
    public function flush(): bool
    {
        $files = glob($this->cacheDirectory . '/*');
        
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }

        return true;
    }

    /**
     * Get the file path for a cache key
     *
     * @param string $key The cache key
     * @return string The file path
     */
    private function getFilePath(string $key): string
    {
        $hash = md5($key);
        
        return $this->cacheDirectory . '/' . $hash . '.cache';
    }

    /**
     * Clean up expired cache entries
     *
     * @return int Number of expired entries removed
     */
    public function cleanExpired(): int
    {
        $files = glob($this->cacheDirectory . '/*.cache');
        $removed = 0;
        
        foreach ($files as $file) {
            $content = file_get_contents($file);
            
            if ($content === false) {
                continue;
            }

            $data = unserialize($content);
            
            // Check if expired
            if (time() > $data['expires_at']) {
                unlink($file);
                $removed++;
            }
        }

        return $removed;
    }
}
