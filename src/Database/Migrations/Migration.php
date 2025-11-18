<?php

declare(strict_types=1);

namespace PhpLiteCore\Database\Migrations;

use PhpLiteCore\Database\Database;

abstract class Migration
{
    public function __construct(protected Database $db)
    {
    }

    abstract public function up(): void;

    abstract public function down(): void;
}
