<?php
declare(strict_types=1);

namespace PhpLiteCore\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class RouteClearCommand extends Command
{
    public function __construct()
    {
        parent::__construct('route:clear');
    }

    protected function configure(): void
    {
        $this->setDescription('Remove the route cache file');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $cachePath = PHPLITECORE_ROOT . 'storage/cache/routes.php';

        if (!file_exists($cachePath)) {
            $output->writeln('<comment>Route cache does not exist.</comment>');
            return Command::SUCCESS;
        }

        if (unlink($cachePath)) {
            $output->writeln('<info>Route cache cleared successfully!</info>');
            return Command::SUCCESS;
        } else {
            $output->writeln('<error>Failed to clear route cache.</error>');
            return Command::FAILURE;
        }
    }
}
