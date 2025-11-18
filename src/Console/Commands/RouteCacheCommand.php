<?php
declare(strict_types=1);

namespace PhpLiteCore\Console\Commands;

use PhpLiteCore\Bootstrap\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class RouteCacheCommand extends Command
{
    public function __construct(private readonly Application $app)
    {
        parent::__construct('route:cache');
    }

    protected function configure(): void
    {
        $this->setDescription('Create a route cache file for faster route registration');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Load routes first - pass router in scope
        $router = $this->app->router;
        require PHPLITECORE_ROOT . 'routes/web.php';

        $cachePath = PHPLITECORE_ROOT . 'storage/cache/routes.php';

        try {
            if ($this->app->router->saveToCache($cachePath)) {
                $output->writeln('<info>Routes cached successfully!</info>');
                $output->writeln("Cache file: {$cachePath}");
                return Command::SUCCESS;
            } else {
                $output->writeln('<error>Failed to cache routes.</error>');
                return Command::FAILURE;
            }
        } catch (\Exception $e) {
            $output->writeln('<error>Error caching routes: ' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }
    }
}
