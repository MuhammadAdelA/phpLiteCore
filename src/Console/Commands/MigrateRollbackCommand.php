<?php
declare(strict_types=1);

namespace PhpLiteCore\Console\Commands;

use PhpLiteCore\Bootstrap\Application;
use PhpLiteCore\Database\Migrations\MigrationRunner;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class MigrateRollbackCommand extends Command
{
    protected static $defaultName = 'migrate:rollback';
    protected static $defaultDescription = 'Roll back the latest database migration';

    public function __construct(private readonly Application $app)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $runner = new MigrationRunner($this->app->db);
        $rolledBack = $runner->rollback(PHPLITECORE_ROOT . 'database/migrations');

        if ($rolledBack) {
            $output->writeln("<info>Rolled back:</info> {$rolledBack}");
        } else {
            $output->writeln('<comment>No migrations to roll back.</comment>');
        }

        return Command::SUCCESS;
    }
}
