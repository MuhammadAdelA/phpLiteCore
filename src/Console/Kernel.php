<?php
declare(strict_types=1);

namespace PhpLiteCore\Console;

use PhpLiteCore\Bootstrap\Application;
use PhpLiteCore\Console\Commands\MakeControllerCommand;
use PhpLiteCore\Console\Commands\MakeMigrationCommand;
use PhpLiteCore\Console\Commands\MakeModelCommand;
use PhpLiteCore\Console\Commands\MigrateCommand;
use PhpLiteCore\Console\Commands\MigrateRollbackCommand;
use PhpLiteCore\Console\Commands\SeedCommand;
use Symfony\Component\Console\Application as SymfonyApplication;

final class Kernel
{
    public function __construct(private readonly Application $app)
    {
    }

    public function run(): int
    {
        $console = new SymfonyApplication('phpLiteCore CLI', '0.1');

        $console->add(new MigrateCommand($this->app));
        $console->add(new MigrateRollbackCommand($this->app));
        $console->add(new SeedCommand($this->app));
        $console->add(new MakeMigrationCommand($this->app));
        $console->add(new MakeModelCommand($this->app));
        $console->add(new MakeControllerCommand($this->app));

        return $console->run();
    }
}
