<?php
declare(strict_types=1);

namespace PhpLiteCore\Console\Commands;

use PhpLiteCore\Bootstrap\Application;
use PhpLiteCore\Database\Migrations\MigrationRunner;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class MigrateCommand extends Command
{
    protected static $defaultName = 'migrate';
    protected static $defaultDescription = 'Apply pending database migrations';

    public function __construct(private readonly Application $app)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $runner = new MigrationRunner($this->app->db);
        $applied = $runner->migrate(PHPLITECORE_ROOT . 'database/migrations');

        foreach ($applied as $version) {
            $output->writeln("<info>Applied:</info> {$version}");
        }

        if (empty($applied)) {
            $output->writeln('<comment>No pending migrations.</comment>');
        }

        return Command::SUCCESS;
    }
}
