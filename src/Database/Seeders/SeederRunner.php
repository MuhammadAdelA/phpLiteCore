<?php
declare(strict_types=1);

namespace PhpLiteCore\Database\Seeders;

use PhpLiteCore\Database\Database;

final class SeederRunner
{
    public function __construct(private readonly Database $db)
    {
    }

    /**
     * Run all seeders in a directory. Each seeder file should return a callable
     * with signature function (Database $db): void { ... }.
     *
     * @return string[] List of executed seeder filenames (basename).
     */
    public function seed(string $seedersPath): array
    {
        $files = glob(rtrim($seedersPath, '/\\') . DIRECTORY_SEPARATOR . '*.php') ?: [];
        sort($files, SORT_STRING);

        $executed = [];
        foreach ($files as $file) {
            $callable = require $file;
            if (!is_callable($callable)) {
                throw new \RuntimeException("Seeder file must return a callable: {$file}");
            }
            $callable($this->db);
            $executed[] = basename($file);
        }

        return $executed;
    }
}
