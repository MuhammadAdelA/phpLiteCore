<?php
declare(strict_types=1);

namespace PhpLiteCore\Database\Grammar;

use PhpLiteCore\Database\QueryBuilder\BaseQueryBuilder;

/**
 * GrammarInterface defines methods for compiling queries into SQL strings.
 */
interface GrammarInterface
{
    public function compileSelect(BaseQueryBuilder $builder): string;
    public function compileInsert(BaseQueryBuilder $builder): string;
    public function compileUpdate(BaseQueryBuilder $builder): string;
    public function compileDelete(BaseQueryBuilder $builder): string;
}