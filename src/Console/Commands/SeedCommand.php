<?php

declare(strict_types=1);

namespace PhpLiteCore\Console\Commands;

use PhpLiteCore\Bootstrap\Application;
use PhpLiteCore\Database\Seeders\SeederRunner;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class SeedCommand extends Command
{
    public function __construct(private readonly Application $app)
    {
        parent::__construct('seed');
    }

    protected function configure(): void
    {
        $this->setDescription('Run all database seeders');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $runner = new SeederRunner($this->app->db);
        $executed = $runner->seed(PHPLITECORE_ROOT . 'database/seeders');

        foreach ($executed as $file) {
            $output->writeln("<info>Seeded:</info> {$file}");
        }

        if (empty($executed)) {
            $output->writeln('<comment>No seeders found.</comment>');
        }

        return Command::SUCCESS;
    }
}
