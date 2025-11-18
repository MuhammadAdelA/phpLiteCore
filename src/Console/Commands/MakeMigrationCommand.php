<?php

declare(strict_types=1);

namespace PhpLiteCore\Console\Commands;

use PhpLiteCore\Bootstrap\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class MakeMigrationCommand extends Command
{
    public function __construct(private readonly Application $app)
    {
        parent::__construct('make:migration');
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Create a new migration file')
            ->addArgument('name', InputArgument::REQUIRED, 'Migration name (e.g., AddUsersIndexes)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = preg_replace('/[^A-Za-z0-9_]/', '', (string)$input->getArgument('name'));
        $ts = date('YmdHis');
        $dir = PHPLITECORE_ROOT . 'database/migrations';
        if (! is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $file = "{$dir}/{$ts}_{$name}.php";

        $template = <<<PHP
<?php
declare(strict_types=1);

use PhpLiteCore\\Database\\Migrations\\Migration;

/**
 * Migration: {$ts}_{$name}
 * Return an instance of an anonymous class extending Migration.
 */
return new class(\$db) extends Migration {
    public function up(): void
    {
        // Example: \$this->db->raw("ALTER TABLE `users` ADD INDEX `idx_users_status` (`status`)");
    }

    public function down(): void
    {
        // Example: \$this->db->raw("ALTER TABLE `users` DROP INDEX `idx_users_status`");
    }
};
PHP;

        if (file_put_contents($file, $template) === false) {
            $output->writeln("<error>Failed to create migration: {$file}</error>");

            return Command::FAILURE;
        }

        $output->writeln("<info>Created migration:</info> {$file}");

        return Command::SUCCESS;
    }
}
