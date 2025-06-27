<?php
declare(strict_types=1);

namespace PhpLiteCore\Database\QueryBuilder;

/**
 * QueryBuilderInterface defines the methods for building SQL queries.
 */
interface QueryBuilderInterface
{
    // SELECT
    public function select(string ...$columns): static;

    public function from(string $table, ?string $alias = null): static;

    // INSERT
    public function insert(string $table, array $data): static;

    // UPDATE
    public function update(string $table, array $data): static;

    // DELETE
    public function delete(): static;

    // WHERE clauses
    public function where(string $column, string $operator, mixed $value): static;
    public function orWhere(string $column, string $operator, mixed $value): static;

    // WHERE IN
    public function whereIn(string $column, array $values): static;

    // JOINs
    public function join(string $table, string $first, string $operator, string $second, string $type = 'INNER'): static;

    // GROUP BY
    public function groupBy(string ...$columns): static;

    // ORDER BY
    public function orderBy(string $column, string $direction = 'ASC'): static;

    // LIMIT & OFFSET
    public function limit(int $limit): static;
    public function offset(int $offset): static;

    // Build SQL & get bindings
    public function toSql(): string;
    public function getBindings(): array;
}
