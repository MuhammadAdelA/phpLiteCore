<?php
declare(strict_types=1);

namespace PhpLiteCore\Database\QueryBuilder;

/**
 * QueryBuilderInterface defines methods for building SQL queries with a fluent API.
 */
interface QueryBuilderInterface
{
    /**
     * Add SELECT clause.
     *
     * @param string ...$columns Columns to select.
     * @return static
     */
    public function select(string ...$columns): static;

    /**
     * Add FROM clause.
     *
     * @param string      $table Table name.
     * @param string|null $alias Optional table alias.
     * @return static
     */
    public function from(string $table, ?string $alias = null): static;

    /**
     * Add INSERT clause and execute the query.
     *
     * @param array $data Column-value pairs to insert.
     * @return string|false The last inserted ID or false on failure.
     */
    public function insert(array $data): string|false;

    /**
     * Add UPDATE clause and execute the query.
     *
     * @param array $data Column-value pairs to update.
     * @return int The number of affected rows.
     */
    public function update(array $data): int;

    /**
     * Add a DELETE clause and execute the query.
     *
     * @return int The number of affected rows.
     */
    public function delete(): int;

    /**
     * Add a basic WHERE clause, or handle more complex conditions.
     *
     * @param string|callable|array  $column   Column name, closure, or array of conditions.
     * @param string|array|null      $operator Operator string, or array of values.
     * @param mixed|null             $value    Value to bind.
     * @param string                 $boolean  'AND' or 'OR'.
     * @return static
     */
    public function where(callable|string|array $column, string|array|null $operator = null, mixed $value = null, string $boolean = 'AND'): static;

    /**
     * Add a basic OR WHERE clause.
     *
     * @param string|callable|array  $column   Column name, closure, or array of conditions.
     * @param string|array|null      $operator Operator string, or array of values.
     * @param mixed|null             $value    Value to bind.
     * @return static
     */
    public function orWhere(callable|string|array $column, string|array|null $operator = null, mixed $value = null): static;

    /**
     * Add a WHERE ... IN (...) clause with AND or OR boolean.
     *
     * @param string $column Column name.
     * @param array $values List of values.
     * @param string $type 'In' or 'NotIn'.
     * @param string $boolean 'AND' or 'OR'.
     * @return static
     */
    public function whereIn(string $column, array $values, string $type, string $boolean): static;

    /**
     * Add a WHERE ... IN (...) clause with OR boolean.
     *
     * @param string $column Column name.
     * @param array  $values List of values.
     * @return static
     */
    public function orWhereIn(string $column, array $values): static;

    /**
     * Add a WHERE ... NOT IN (...) clause with AND boolean.
     *
     * @param string $column Column name.
     * @param array  $values List of values.
     * @return static
     */
    public function whereNotIn(string $column, array $values): static;

    /**
     * Add a WHERE ... NOT IN (...) clause with OR boolean.
     *
     * @param string $column Column name.
     * @param array  $values List of values.
     * @return static
     */
    public function orWhereNotIn(string $column, array $values): static;

    /**
     * Add a WHERE ... BETWEEN ... AND ... clause with AND boolean.
     *
     * @param string $column Column name.
     * @param mixed $start Lower bound.
     * @param mixed $end Upper bound.
     * @param string $boolean 'AND' or 'OR'.
     * @return static
     */
    public function whereBetween(string $column, mixed $start, mixed $end, string $boolean): static;

    /**
     * Add a WHERE ... BETWEEN ... AND ... clause with OR boolean.
     *
     * @param string $column Column name.
     * @param mixed  $start  Lower bound.
     * @param mixed  $end    Upper bound.
     * @return static
     */
    public function orWhereBetween(string $column, mixed $start, mixed $end): static;

    /**
     * Add a nested WHERE group.
     *
     * @param callable $callback Callback that receives a new query builder instance.
     * @param string   $boolean  Boolean connector for the group, 'AND' or 'OR'.
     * @return static
     */
    public function whereGroup(callable $callback, string $boolean): static;

    /**
     * Add a nested OR WHERE group.
     *
     * @param callable $callback Callback that receives a new query builder instance.
     * @param string   $boolean  Boolean connector for the group, 'OR'.
     * @return static
     */
    public function orWhereGroup(callable $callback, string $boolean): static;


    /**
     * Add a GROUP BY clause.
     *
     * @param string ...$columns Columns to group by.
     * @return static
     */
    public function groupBy(string ...$columns): static;

    /**
     * Add ORDER BY clause.
     *
     * @param string $column    Column to order by.
     * @param string $direction Direction (ASC or DESC).
     * @return static
     */
    public function orderBy(string $column, string $direction = 'ASC'): static;

    /**
     * Set the LIMIT clause.
     *
     * @param int $limit Number of records to limit.
     * @return static
     */
    public function limit(int $limit): static;

    /**
     * Set the OFFSET clause.
     *
     * @param int $offset Number of records to skip.
     * @return static
     */
    public function offset(int $offset): static;

    /**
     * Get the compiled SQL query.
     *
     * @return string
     */
    public function toSql(): string;

    /**
     * Get the bindings for the prepared statement.
     *
     * @return array
     */
    public function getBindings(): array;

    /**
     * Execute the built SELECT query, fetch all rows, and store rowCount.
     *
     * @return array
     */
    public function get(): array;

    /**
     * Get the first record matching the query, or null if none.
     *
     * @return object|null A model instance, or a generic object, or null.
     */
    public function first(): ?object;

    /**
     * Determine if any record exists matching the query.
     *
     * @return bool
     */
    public function exists(): bool;

    /**
     * Count how many records match the query.
     *
     * @return int
     */
    public function count(): int;

    /**
     * Count how many records match the query.
     *
     * @deprecated Use count() instead. This method will be removed in a future release.
     * @return int
     */
    public function found(): int;

    /**
     * Specify relations to eager load by name (matching relation methods on the model).
     *
     * @param string|array $relations
     * @return static
     */
    public function with(string|array $relations): static;

    /*public function having(string $column, string $operator, mixed $value): static;
    public function orHaving(string $column, string $operator, mixed $value): static;
    public function havingIn(string $column, array $values, string $type): static;
    public function orHavingIn(string $column, array $values): static;
    public function havingNotIn(string $column, array $values): static;
    public function orHavingNotIn(string $column, array $values): static;
    public function havingBetween(string $column, mixed $start, mixed $end): static;
    public function orHavingBetween(string $column, mixed $start, mixed $end): static;
    public function havingGroup(callable $callback, string $boolean): static;
    public function orHavingGroup(callable $callback, string $boolean): static;
    public function havingRaw(string $sql, array $bindings = []): static;
    public function orHavingRaw(string $sql, array $bindings = []): static;
    public function havingExists(callable $callback, string $boolean, string $operator = 'AND', bool $not = false): static;
    public function orHavingExists(callable $callback, string $operator = 'AND', bool $not = false): static;
    public function havingNotExists(callable $callback, string $operator = 'AND', bool $not = false): static;
    public function orHavingNotExists(callable $callback, string $operator = 'AND', bool $not = false): static;
    public function havingNull(string $column, string $boolean, string $operator = 'AND', bool $not = false): static;
    public function orHavingNull(string $column, string $operator = 'AND', bool $not = false): static;
    public function havingNotNull(string $column, string $boolean, string $operator = 'AND', bool $not = false): static;
    public function orHavingNotNull(string $column, string $operator = 'AND', bool $not = false): static;
    public function havingDate(string $column, string $operator, string $value): static;
    public function orHavingDate(string $column, string $operator, string $value): static;
    public function havingTime(string $column, string $operator, string $value): static;
    public function orHavingTime(string $column, string $operator, string $value): static;
    public function havingDateTime(string $column, string $operator, string $value): static;
    public function orHavingDateTime(string $column, string $operator, string $value): static;
    public function havingYear(string $column, string $operator, string $value): static;
    public function orHavingYear(string $column, string $operator, string $value): static;
    public function havingMonth(string $column, string $operator, string $value): static;
    public function orHavingMonth(string $column, string $operator, string $value): static;
    public function havingDay(string $column, string $operator, string $value): static;
    public function orHavingDay(string $column, string $operator, string $value): static;
    public function havingWeek(string $column, string $operator, string $value): static;
    public function orHavingWeek(string $column, string $operator, string $value): static;
    public function havingWeekDay(string $column, string $operator, string $value): static;
    public function orHavingWeekDay(string $column, string $operator, string $value): static;
    public function havingHour(string $column, string $operator, string $value): static;
    public function orHavingHour(string $column, string $operator, string $value): static;
    public function havingMinute(string $column, string $operator, string $value): static;
    public function orHavingMinute(string $column, string $operator, string $value): static;
    public function havingSecond(string $column, string $operator, string $value): static;
    public function orHavingSecond(string $column, string $operator, string $value): static;

    public function join(string $table, string $first, string $operator, string $second, string $type = 'inner', bool $where = false): static;
    public function leftJoin(string $table, string $first, string $operator, string $second, bool $where = false): static;
    public function rightJoin(string $table, string $first, string $operator, string $second, bool $where = false): static;
    public function fullJoin(string $table, string $first, string $operator, string $second, bool $where = false): static;
    public function crossJoin(string $table, string $first, string $operator, string $second, bool $where = false): static;
    public function naturalJoin(string $table, string $first, string $operator, string $second, bool $where = false): static;
    public function on(string $first, string $operator, string $second): static;
    public function orOn(string $first, string $operator, string $second): static;

    public function union(callable $callback, bool $all = false): static;
    public function unionAll(callable $callback): static;
    public function intersect(callable $callback): static;
    public function intersectAll(callable $callback): static;
    public function except(callable $callback): static;
    public function exceptAll(callable $callback): static;
    public function subQuery(callable $callback, string $as): static;
    public function exists(callable $callback, string $boolean = 'AND', string $operator = 'AND', bool $not = false): static;
    public function notExists(callable $callback, string $operator = 'AND', bool $not = false): static;
    public function null(string $column, string $boolean, string $operator = 'AND', bool $not = false): static;
    public function notNull(string $column, string $boolean, string $operator = 'AND', bool $not = false): static;
    public function date(string $column, string $operator, string $value): static;
    public function time(string $column, string $operator, string $value): static;
    public function dateTime(string $column, string $operator, string $value): static;
    public function year(string $column, string $operator, string $value): static;
    public function month(string $column, string $operator, string $value): static;
    public function day(string $column, string $operator, string $value): static;
    public function week(string $column, string $operator, string $value): static;
    public function weekDay(string $column, string $operator, string $value): static;
    public function hour(string $column, string $operator, string $value): static;
    public function minute(string $column, string $operator, string $value): static;
    public function second(string $column, string $operator, string $value): static;
    public function raw(string $sql, array $bindings = []): static;
    public function insertGetId(string $table, array $data, string $sequence = null): static;
    public function insertOrIgnore(string $table, array $data): static;
    public function insertOrReplace(string $table, array $data): static;
    public function insertUsing(string $table, array $columns, array $values, string $update = null): static;
    public function updateOrInsert(array $columns, array $values, array $update = null): static;
    public function updateOrIgnore(array $columns, array $values): static;
    public function updateOrReplace(array $columns, array $values): static;
    public function truncate(string $table): static;
    public function lock(string $value = 'FOR UPDATE'): static;
    public function sharedLock(string $value = 'LOCK IN SHARE MODE'): static;
    public function noLock(): static;
    public function forUpdate(): static;
    public function forShare(): static;
    public function noWait(): static;
    public function skipLocked(): static;
    public function of(string $table): static;
    public function crossJoinSub(callable $callback, string $as): static;
    public function joinSub(callable $callback, string $table, string $first, string $operator, string $second, string $type = 'inner', bool $where = false): static;
    public function leftJoinSub(callable $callback, string $table, string $first, string $operator, string $second, bool $where = false): static;
    public function rightJoinSub(callable $callback, string $table, string $first, string $operator, string $second, bool $where = false): static;*/

}


