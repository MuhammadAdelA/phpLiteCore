<?php
declare(strict_types=1);

namespace PhpLiteCore\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ConfigCacheCommand extends Command
{
    public function __construct()
    {
        parent::__construct('config:cache');
    }

    protected function configure(): void
    {
        $this->setDescription('Create a cache file for faster configuration loading');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $cachePath = PHPLITECORE_ROOT . 'storage/cache/config.php';
        
        // Load environment variables
        $envPath = PHPLITECORE_ROOT . '.env';
        if (!file_exists($envPath)) {
            $output->writeln('<error>.env file not found.</error>');
            return Command::FAILURE;
        }

        // Parse .env file
        $envContent = file_get_contents($envPath);
        if ($envContent === false) {
            $output->writeln('<error>Failed to read .env file.</error>');
            return Command::FAILURE;
        }

        $config = [];
        $lines = explode("\n", $envContent);
        foreach ($lines as $line) {
            $line = trim($line);
            // Skip empty lines and comments
            if (empty($line) || str_starts_with($line, '#')) {
                continue;
            }
            
            // Parse KEY=VALUE
            if (str_contains($line, '=')) {
                [$key, $value] = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                
                // Remove quotes if present
                if ((str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                    (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
                    $value = substr($value, 1, -1);
                }
                
                $config[$key] = $value;
            }
        }

        // Create cache content
        $cacheData = [
            'config' => $config,
            'timestamp' => time(),
        ];

        $content = '<?php return ' . var_export($cacheData, true) . ';';

        // Ensure directory exists
        $dir = dirname($cachePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        if (file_put_contents($cachePath, $content) !== false) {
            $output->writeln('<info>Configuration cached successfully!</info>');
            $output->writeln("Cache file: {$cachePath}");
            return Command::SUCCESS;
        } else {
            $output->writeln('<error>Failed to cache configuration.</error>');
            return Command::FAILURE;
        }
    }
}
