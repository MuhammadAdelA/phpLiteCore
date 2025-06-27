<?php

declare(strict_types=1);

namespace PhpLiteCore\Database\Grammar;

use PhpLiteCore\Database\QueryBuilder\BaseQueryBuilder;

/**
 * MySqlGrammar compiles BaseQueryBuilder instances into MySQL-compatible SQL.
 */
class MySqlGrammar implements GrammarInterface
{
    public function compileSelect(BaseQueryBuilder $builder): string
    {
        $sql = 'SELECT ' . implode(', ', $builder->getColumns())
            . ' FROM ' . $builder->getTable()
            . ($builder->getAlias() ? ' AS ' . $builder->getAlias() : '');

        foreach ($builder->getJoins() as $join) {
            $sql .= ' ' . strtoupper($join['type']) . ' JOIN ' . $join['table']
                . ' ON ' . $join['first'] . ' ' . $join['operator'] . ' ' . $join['second'];
        }

        if ($builder->getWheres()) {
            $sql .= ' WHERE ' . $this->compileWheres($builder);
        }

        if ($builder->getGroups()) {
            $sql .= ' GROUP BY ' . implode(', ', $builder->getGroups());
        }

        if ($builder->getOrders()) {
            $parts = array_map(fn($o) => $o['column'] . ' ' . $o['direction'], $builder->getOrders());
            $sql .= ' ORDER BY ' . implode(', ', $parts);
        }

        if (! is_null($builder->getLimit())) {
            $sql .= ' LIMIT ' . $builder->getLimit();
            if (! is_null($builder->getOffset())) {
                $sql .= ' OFFSET ' . $builder->getOffset();
            }
        }

        return $sql;
    }

    public function compileInsert(BaseQueryBuilder $builder): string
    {
        $columns      = array_keys($builder->getInsertData());
        $placeholders = array_fill(0, count($columns), '?');

        return sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $builder->getTable(),
            implode(', ', $columns),
            implode(', ', $placeholders)
        );
    }

    public function compileUpdate(BaseQueryBuilder $builder): string
    {
        $sets = [];
        foreach ($builder->getUpdateData() as $column => $value) {
            $sets[] = "$column = ?";
        }

        $sql = sprintf('UPDATE %s SET %s', $builder->getTable(), implode(', ', $sets));

        if ($builder->getWheres()) {
            $sql .= ' WHERE ' . $this->compileWheres($builder);
        }

        return $sql;
    }

    public function compileDelete(BaseQueryBuilder $builder): string
    {
        $sql = 'DELETE FROM ' . $builder->getTable();

        if ($builder->getWheres()) {
            $sql .= ' WHERE ' . $this->compileWheres($builder);
        }

        return $sql;
    }

    /**
     * Compile the where clauses into SQL.
     */
    protected function compileWheres(BaseQueryBuilder $builder): string
    {
        $segments = [];
        foreach ($builder->getWheres() as $where) {
            if ($where['type'] === 'Basic') {
                $segments[] = "{$where['column']} {$where['operator']} ?";
            } else {
                $placeholders = implode(', ', array_fill(0, count($where['values']), '?'));
                $segments[] = "{$where['column']} IN ({$placeholders})";
            }
        }

        $sql = '';
        foreach ($builder->getWheres() as $i => $where) {
            $prefix  = $i === 0 ? '' : ' ' . $where['boolean'] . ' ';
            $segment = $segments[$i];
            $sql    .= $prefix . $segment;
        }

        return $sql;
    }
}
