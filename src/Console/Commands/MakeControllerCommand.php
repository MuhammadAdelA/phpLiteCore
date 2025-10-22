<?php
declare(strict_types=1);

namespace PhpLiteCore\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class MakeControllerCommand extends Command
{
    protected static $defaultName = 'make:controller';
    protected static $defaultDescription = 'Create a new controller class in app/Controllers';

    protected function configure(): void
    {
        $this->addArgument('name', InputArgument::REQUIRED, 'Controller class name (e.g., PostController)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = preg_replace('/[^A-Za-z0-9_]/', '', (string)$input->getArgument('name'));
        $dir = PHPLITECORE_ROOT . 'app/Controllers';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $file = "{$dir}/{$name}.php";

        $template = <<<PHP
<?php
declare(strict_types=1);

namespace App\\Controllers;

use PhpLiteCore\\Bootstrap\\Application;

final class {$name} extends BaseController
{
    public function index(): void
    {
        echo '<h1>{$name} works</h1>';
    }
}
PHP;

        if (file_put_contents($file, $template) === false) {
            $output->writeln("<error>Failed to create controller: {$file}</error>");
            return Command::FAILURE;
        }

        $output->writeln("<info>Created controller:</info> {$file}");
        return Command::SUCCESS;
    }
}
