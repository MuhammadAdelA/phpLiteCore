<?php
declare(strict_types=1);

namespace PhpLiteCore\Database\Grammar;

use InvalidArgumentException;
use PhpLiteCore\Database\QueryBuilder\BaseQueryBuilder;

/**
 * MySqlGrammar compiles BaseQueryBuilder instances into MySQL-compatible SQL,
 * safely wraps identifiers, and supports all WHERE clause types including
 * nested groups, IN, NOT IN, BETWEEN, and NOT BETWEEN.
 */
class MySqlGrammar implements GrammarInterface
{
    /**
     * The character used to open an identifier escape.
     *
     * @var string
     */
    protected string $opening = '`';

    /**
     * The character used to close an identifier escape.
     *
     * @var string
     */
    protected string $closing = '`';

    /**
     * Compile a SELECT query into SQL.
     *
     * @param BaseQueryBuilder $builder
     * @return string
     */
    public function compileSelect(BaseQueryBuilder $builder): string
    {
        // Wrap each selected column
        $columns = array_map([$this, 'wrapIdentifier'], $builder->getColumns());

        // Start building the SELECT clause
        $sql = 'SELECT ' . implode(', ', $columns)
            . ' FROM ' . $this->wrapIdentifier($builder->getTable());

        // Add table alias if present
        if ($alias = $builder->getAlias()) {
            $sql .= ' AS ' . $this->wrapIdentifier($alias);
        }

        // Add JOIN clauses
        if ($joins = $builder->getJoins()) {
            $sql .= ' ' . $this->compileJoins($joins);
        }

        // Add WHERE clauses
        if ($wheres = $builder->getWheres()) {
            $sql .= ' WHERE ' . $this->compileWheres($wheres);
        }

        // Add GROUP BY clause
        if ($groups = $builder->getGroups()) {
            $wrappedGroups = array_map([$this, 'wrapIdentifier'], $groups);
            $sql .= ' GROUP BY ' . implode(', ', $wrappedGroups);
        }

        // Add ORDER BY clause
        if ($orders = $builder->getOrders()) {
            $orderClauses = array_map(
                fn($o) => $this->wrapIdentifier($o[0]) . ' ' . strtoupper($o[1]),
                $orders
            );
            $sql .= ' ORDER BY ' . implode(', ', $orderClauses);
        }

        // Add LIMIT and OFFSET
        if (null !== $limit = $builder->getLimit()) {
            $sql .= ' LIMIT ' . $limit;
            if (null !== $offset = $builder->getOffset()) {
                $sql .= ' OFFSET ' . $offset;
            }
        }

        return $sql;
    }

    /**
     * Compile an INSERT query into SQL.
     *
     * @param BaseQueryBuilder $builder
     * @return string
     */
    public function compileInsert(BaseQueryBuilder $builder): string
    {
        // Wrap table name
        $table = $this->wrapIdentifier($builder->getTable());

        // Prepare columns and placeholders
        $data       = $builder->getInsertData();
        $columns    = array_map([$this, 'wrapIdentifier'], array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));

        return sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $table,
            implode(', ', $columns),
            $placeholders
        );
    }

    /**
     * Compile an UPDATE query into SQL.
     *
     * @param BaseQueryBuilder $builder
     * @return string
     */
    public function compileUpdate(BaseQueryBuilder $builder): string
    {
        // Wrap table name
        $table = $this->wrapIdentifier($builder->getTable());

        // Build SET clauses
        $data = $builder->getUpdateData();
        $sets = [];
        foreach ($data as $column => $_) {
            $sets[] = $this->wrapIdentifier($column) . ' = ?';
        }

        $sql = 'UPDATE ' . $table . ' SET ' . implode(', ', $sets);

        // Add WHERE clauses if any
        if ($wheres = $builder->getWheres()) {
            $sql .= ' WHERE ' . $this->compileWheres($wheres);
        }

        return $sql;
    }

    /**
     * Compile a DELETE query into SQL.
     *
     * @param BaseQueryBuilder $builder
     * @return string
     */
    public function compileDelete(BaseQueryBuilder $builder): string
    {
        // Wrap table name
        $sql = 'DELETE FROM ' . $this->wrapIdentifier($builder->getTable());

        // Add WHERE clauses if any
        if ($wheres = $builder->getWheres()) {
            $sql .= ' WHERE ' . $this->compileWheres($wheres);
        }

        return $sql;
    }

    /**
     * Escape (wrap) a table or column identifier safely.
     *
     * @param string $identifier
     * @return string
     */
    public function wrapIdentifier(string $identifier): string
    {
        // Remove existing escape characters
        $clean = str_replace([$this->opening, $this->closing], '', $identifier);

        // If identifier contains a function call or wildcard, do not wrap
        if (str_contains($clean, '(') || str_contains($clean, '*')) {
            return $identifier;
        }

        // Handle alias "AS" (case-insensitive)
        $alias = '';
        if (preg_match('/\s+AS\s+/i', $clean, $matches, PREG_OFFSET_CAPTURE)) {
            $pos   = (int) $matches[0][1];
            $alias = substr($clean, $pos);
            $clean = substr($clean, 0, $pos);
        }

        // Wrap each segment separated by dot
        $segments = explode('.', trim($clean));
        $wrapped  = $this->opening
            . implode($this->closing . '.' . $this->opening, $segments)
            . $this->closing;

        return $wrapped . $alias;
    }

    /**
     * Compile JOIN clauses.
     *
     * @param array $joins
     * @return string
     */
    protected function compileJoins(array $joins): string
    {
        $clauses = [];
        foreach ($joins as $join) {
            [$type, $table, $first, $operator, $second] = $join;
            $clauses[] = sprintf(
                '%s JOIN %s ON %s %s %s',
                strtoupper($type),
                $this->wrapIdentifier($table),
                $this->wrapIdentifier($first),
                $operator,
                $this->wrapIdentifier($second)
            );
        }
        return implode(' ', $clauses);
    }

    /**
     * Compile WHERE clauses, including Basic, In, NotIn, Between,
     * NotBetween, and Nested groups.
     *
     * @param array $wheres
     * @return string
     * @throws InvalidArgumentException
     */
    protected function compileWheres(array $wheres): string
    {
        $clauses = [];

        foreach ($wheres as $index => $where) {
            // Determine boolean connector (AND/OR)
            $boolean = $where['boolean'] ?? 'AND';
            $prefix  = $index === 0 ? '' : " {$boolean} ";

            switch ($where['type']) {
                case 'Basic':
                    // column = ?
                    $clauses[] = $prefix
                        . $this->wrapIdentifier($where['column'])
                        . ' ' . $where['operator'] . ' ?';
                    break;

                case 'In':
                    // column IN (?, ?, ...)
                    $placeholders = implode(
                        ', ',
                        array_fill(0, count($where['values']), '?')
                    );
                    $clauses[] = $prefix
                        . $this->wrapIdentifier($where['column'])
                        . " IN ({$placeholders})";
                    break;

                case 'NotIn':
                    // column NOT IN (?, ?, ...)
                    $placeholders = implode(
                        ', ',
                        array_fill(0, count($where['values']), '?')
                    );
                    $clauses[] = $prefix
                        . $this->wrapIdentifier($where['column'])
                        . " NOT IN ({$placeholders})";
                    break;

                case 'Between':
                    // column BETWEEN ? AND ?
                    $clauses[] = $prefix
                        . $this->wrapIdentifier($where['column'])
                        . ' BETWEEN ? AND ?';
                    break;

                case 'NotBetween':
                    // column NOT BETWEEN ? AND ?
                    $clauses[] = $prefix
                        . $this->wrapIdentifier($where['column'])
                        . ' NOT BETWEEN ? AND ?';
                    break;

                case 'Nested':
                    // ( nested_conditions )
                    $nestedSql = $this->compileWheres($where['wheres']);
                    $clauses[] = $prefix . '(' . $nestedSql . ')';
                    break;

                default:
                    throw new InvalidArgumentException(
                        "Unknown where type [{$where['type']}]"
                    );
            }
        }

        return implode('', $clauses);
    }
}
