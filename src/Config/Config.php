<?php
declare(strict_types=1);

namespace PhpLiteCore\Config;

/**
 * Configuration management service
 * Handles loading, caching, and retrieving configuration values
 */
final class Config
{
    private array $config = [];
    private bool $cached = false;

    public function __construct()
    {
        $this->loadConfiguration();
    }

    /**
     * Load configuration from cache or config files
     */
    private function loadConfiguration(): void
    {
        $cachePath = PHPLITECORE_ROOT . 'storage/cache/config.php';
        
        // Try loading from cache first
        if (file_exists($cachePath)) {
            $cached = require $cachePath;
            if (is_array($cached) && isset($cached['config'])) {
                $this->config = $cached['config'];
                $this->cached = true;
                return;
            }
        }

        // Load from config files
        $this->loadConfigFiles();
    }

    /**
     * Load all configuration files from config directory
     */
    private function loadConfigFiles(): void
    {
        $configDir = PHPLITECORE_ROOT . 'config';
        
        if (!is_dir($configDir)) {
            return;
        }

        $files = glob($configDir . '/*.php');
        if ($files === false) {
            error_log("Failed to read config directory: {$configDir}");
            return;
        }

        foreach ($files as $file) {
            $key = basename($file, '.php');
            $value = require $file;
            if (is_array($value)) {
                $this->config[$key] = $value;
            }
        }
    }

    /**
     * Get a configuration value using dot notation
     * 
     * @param string $key Configuration key (e.g., 'app.name' or 'database.host')
     * @param mixed $default Default value if key not found
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        $keys = explode('.', $key);
        $value = $this->config;

        foreach ($keys as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }

        return $value;
    }

    /**
     * Get all configuration
     * 
     * @return array
     */
    public function all(): array
    {
        return $this->config;
    }

    /**
     * Cache all configuration to a single file
     * 
     * Note: Uses var_export for caching. This is safe because configuration
     * files are developer-controlled, not user input.
     * 
     * @return bool
     */
    public function cache(): bool
    {
        // Load fresh config from files
        $this->config = [];
        $this->loadConfigFiles();

        $cachePath = PHPLITECORE_ROOT . 'storage/cache/config.php';
        $cacheDir = dirname($cachePath);

        // Ensure cache directory exists
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        $data = [
            'config' => $this->config,
            'timestamp' => time(),
        ];

        $content = '<?php' . PHP_EOL . PHP_EOL . 'return ' . var_export($data, true) . ';' . PHP_EOL;
        
        $result = file_put_contents($cachePath, $content);
        
        if ($result !== false) {
            $this->cached = true;
            return true;
        }

        return false;
    }

    /**
     * Clear cached configuration
     * 
     * @return bool
     */
    public function clearCache(): bool
    {
        $cachePath = PHPLITECORE_ROOT . 'storage/cache/config.php';
        
        if (file_exists($cachePath)) {
            $result = unlink($cachePath);
            if ($result) {
                $this->cached = false;
                // Reload from files
                $this->config = [];
                $this->loadConfigFiles();
                return true;
            }
            return false;
        }

        return true; // Already cleared
    }

    /**
     * Check if configuration is cached
     * 
     * @return bool
     */
    public function isCached(): bool
    {
        return $this->cached;
    }
}
