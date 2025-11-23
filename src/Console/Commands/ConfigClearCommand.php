<?php
declare(strict_types=1);

namespace PhpLiteCore\Console\Commands;

use PhpLiteCore\Config\Config;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ConfigClearCommand extends Command
{
    public function __construct()
    {
        parent::__construct('config:clear');
    }

    protected function configure(): void
    {
        $this->setDescription('Clear the cached configuration file');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $config = new Config();
        
        if ($config->clearCache()) {
            $output->writeln('<info>Configuration cache cleared successfully!</info>');
            return Command::SUCCESS;
        }

        $output->writeln('<error>Failed to clear configuration cache.</error>');
        return Command::FAILURE;
    }
}
