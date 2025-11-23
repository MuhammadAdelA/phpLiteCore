<?php
declare(strict_types=1);

namespace PhpLiteCore\Console\Commands;

use PhpLiteCore\Config\Config;
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
        $this->setDescription('Cache all configuration files into a single file for better performance');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $config = new Config();
        
        if ($config->cache()) {
            $output->writeln('<info>Configuration cached successfully!</info>');
            return Command::SUCCESS;
        }

        $output->writeln('<error>Failed to cache configuration.</error>');
        return Command::FAILURE;
    }
}
