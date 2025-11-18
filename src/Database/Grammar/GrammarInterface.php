<?php

declare(strict_types=1);

namespace PhpLiteCore\Database\Grammar;

use PhpLiteCore\Database\QueryBuilder\BaseQueryBuilder;

/**
 * GrammarInterface defines methods for compiling queries into SQL strings
 * and for escaping identifiers (tables, columns).
 */
interface GrammarInterface
{
    /**
     * Compile a SELECT query into SQL.
     *
     * @param BaseQueryBuilder $builder The query builder instance.
     * @return string The compiled SQL SELECT statement.
     */
    public function compileSelect(BaseQueryBuilder $builder): string;

    /**
     * Compile an INSERT query into SQL.
     *
     * @param BaseQueryBuilder $builder The query builder instance.
     * @return string The compiled SQL INSERT statement.
     */
    public function compileInsert(BaseQueryBuilder $builder): string;

    /**
     * Compile an UPDATE query into SQL.
     *
     * @param BaseQueryBuilder $builder The query builder instance.
     * @return string The compiled SQL UPDATE statement.
     */
    public function compileUpdate(BaseQueryBuilder $builder): string;

    /**
     * Compile a DELETE query into SQL.
     *
     * @param BaseQueryBuilder $builder The query builder instance.
     * @return string The compiled SQL DELETE statement.
     */
    public function compileDelete(BaseQueryBuilder $builder): string;

    /**
     * Escape (wrap) a table or column identifier safely.
     *
     * @param string $identifier The identifier to wrap (e.g., table or column name).
     * @return string The safely wrapped identifier.
     */
    public function wrapIdentifier(string $identifier): string;
}
