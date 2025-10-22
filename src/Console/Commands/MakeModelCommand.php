<?php
declare(strict_types=1);

namespace PhpLiteCore\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class MakeModelCommand extends Command
{
    public function __construct()
    {
        parent::__construct('make:model');
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Create a new model class in app/Models')
            ->addArgument('name', InputArgument::REQUIRED, 'Model class name (e.g., Post)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = preg_replace('/[^A-Za-z0-9_]/', '', (string)$input->getArgument('name'));
        $dir = PHPLITECORE_ROOT . 'app/Models';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $file = "{$dir}/{$name}.php";

        $template = <<<PHP
<?php
declare(strict_types=1);

namespace App\\Models;

use PhpLiteCore\\Database\\Model\\BaseModel;

final class {$name} extends BaseModel
{
    // protected string \$table = '...'; // optionally override
}
PHP;

        if (file_put_contents($file, $template) === false) {
            $output->writeln("<error>Failed to create model: {$file}</error>");
            return Command::FAILURE;
        }

        $output->writeln("<info>Created model:</info> {$file}");
        return Command::SUCCESS;
    }
}
