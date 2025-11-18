<?php
declare(strict_types=1);

namespace PhpLiteCore\Console\Commands;

use PhpLiteCore\Bootstrap\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class RouteListCommand extends Command
{
    public function __construct(private readonly Application $app)
    {
        parent::__construct('route:list');
    }

    protected function configure(): void
    {
        $this->setDescription('Display all registered routes with their details');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Load routes - pass router in scope
        $router = $this->app->router;
        require PHPLITECORE_ROOT . 'routes/web.php';

        // Get all routes using reflection
        $reflection = new \ReflectionClass($this->app->router);
        $property = $reflection->getProperty('routes');
        $property->setAccessible(true);
        $routes = $property->getValue($this->app->router);

        if (empty($routes)) {
            $output->writeln('<comment>No routes registered.</comment>');
            return Command::SUCCESS;
        }

        // Prepare table
        $table = new Table($output);
        $table->setHeaders(['Method', 'URI', 'Name', 'Action', 'Middleware']);

        foreach ($routes as $route) {
            $action = implode('@', [
                basename(str_replace('\\', '/', $route->getAction()[0])),
                $route->getAction()[1]
            ]);
            
            $middleware = $route->getMiddleware();
            $middlewareNames = array_map(function ($mw) {
                return basename(str_replace('\\', '/', $mw));
            }, $middleware);
            
            $table->addRow([
                $route->getMethod(),
                $route->getUri(),
                $route->getName() ?? '-',
                $action,
                implode(', ', $middlewareNames) ?: '-'
            ]);
        }

        $table->render();
        $output->writeln('');
        $output->writeln(sprintf('<info>Total routes:</info> %d', count($routes)));

        return Command::SUCCESS;
    }
}
