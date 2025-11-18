<?php

declare(strict_types=1);

namespace PhpLiteCore\Database\QueryBuilder;

use PDO;
use PhpLiteCore\Database\Database;
use PhpLiteCore\Database\Grammar\GrammarInterface;
use PhpLiteCore\Database\Model\EagerLoader;
use PhpLiteCore\Database\QueryBuilder\Traits\QueryBuilderGetTraits;
use PhpLiteCore\Database\QueryBuilder\Traits\QueryBuilderLikeTraits;
use PhpLiteCore\Database\QueryBuilder\Traits\QueryBuilderWhereTraits;
use PhpLiteCore\Pagination\Paginator;

/**
 * BaseQueryBuilder provides a fluent interface to build and execute SQL queries.
 */
class BaseQueryBuilder implements QueryBuilderInterface
{
    // Ensure all necessary traits are being used by the class.
    use QueryBuilderWhereTraits;
    use QueryBuilderLikeTraits;
    use QueryBuilderGetTraits;

    /** @var PDO The active PDO connection instance. */
    protected PDO $pdo;

    /** @var Database The main database wrapper instance. */
    protected Database $db;

    /** @var GrammarInterface The SQL grammar compiler. */
    protected GrammarInterface $grammar;

    /** @var string The type of query being built (select, insert, update, delete). */
    protected string $type = 'select';

    /** @var array The columns to be selected. */
    protected array $columns = [];

    /** @var string|null The table the query is targeting. */
    protected ?string $table = null;

    /** @var string|null An alias for the target table. */
    protected ?string $alias = null;

    /** @var array Any JOIN clauses for the query. */
    protected array $joins = [];

    /** @var array The WHERE clauses for the query. */
    protected array $wheres = [];

    /** @var array The GROUP BY columns for the query. */
    protected array $groups = [];

    /** @var array The ORDER BY clauses for the query. */
    protected array $orders = [];

    /** @var int|null The maximum number of records to return. */
    protected ?int $limit = null;

    /** @var int|null The number of records to skip. */
    protected ?int $offset = null;

    /** @var array|null The aggregate function to apply (e.g., COUNT, SUM). */
    protected ?array $aggregate = null;

    /** @var array The bindings for the SET part of an insert/update query. */
    protected array $bindings = [];

    /**
     * Relations requested for eager loading.
     * @var string[]
     */
    protected array $with = [];

    /**
     * BaseQueryBuilder constructor.
     *
     * @param PDO $pdo The PDO connection instance.
     * @param GrammarInterface $grammar The SQL grammar compiler.
     * @param Database $db The database wrapper instance.
     */
    public function __construct(PDO $pdo, GrammarInterface $grammar, Database $db)
    {
        $this->pdo = $pdo;
        $this->grammar = $grammar;
        $this->db = $db;
    }

    /**
     * {@inheritDoc}
     */
    public function select(string ...$columns): static
    {
        $this->type = 'select';
        $this->columns = empty($columns) ? ['*'] : $columns;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function from(string $table, ?string $alias = null): static
    {
        $this->table = $table;
        $this->alias = $alias;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function insert(array $data): string|false
    {
        $this->type = 'insert';
        $this->columns = array_keys($data);
        $this->bindings = array_values($data);
        $sql = $this->toSql();

        return $this->db->insertAndGetId($sql, $this->bindings);
    }

    /**
     * {@inheritDoc}
     */
    public function update(array $data): int
    {
        $this->type = 'update';
        $this->columns = array_keys($data);
        $this->bindings = array_values($data);

        return $this->execute();
    }

    /**
     * {@inheritDoc}
     */
    public function delete(): int
    {
        $this->type = 'delete';
        $this->bindings = []; // DELETE statements have no SET bindings.

        return $this->execute();
    }

    /**
     * Execute the query for non-SELECT statements (INSERT, UPDATE, DELETE).
     *
     * @return int The number of affected rows.
     */
    public function execute(): int
    {
        $sql = $this->toSql();

        // For UPDATE and DELETE, we need to merge the SET bindings (if any)
        // with the WHERE bindings in the correct order.
        $bindings = in_array($this->type, ['update', 'delete'])
            ? array_merge($this->bindings, $this->getWhereBindings())
            // INSERT statements only use the main bindings array.
            : $this->getBindings();

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($bindings);

        return $stmt->rowCount();
    }

    /**
     * {@inheritDoc}
     */
    public function groupBy(string ...$columns): static
    {
        $this->groups = $columns;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function orderBy(string $column, string $direction = 'ASC'): static
    {
        $this->orders[] = [$column, $direction];

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function limit(int $limit): static
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function offset(int $offset): static
    {
        $this->offset = $offset;

        return $this;
    }

    /**
     * Execute a query as a "count" query using a safe subquery approach.
     *
     * This method builds: SELECT COUNT(*) AS aggregate FROM (<current select SQL>) AS sub
     * and reuses existing WHERE bindings via getBindings().
     *
     * @param string $columns The column to count, defaults to '*' (unused in subquery approach).
     * @return int The total number of matching records.
     */
    public function count(string $columns = '*'): int
    {
        // Build the current SELECT query SQL (without LIMIT/OFFSET for accurate count)
        $clone = clone $this;
        $clone->limit = null;
        $clone->offset = null;

        // Get the inner SQL and bindings
        $innerSql = $clone->toSql();
        $bindings = $clone->getWhereBindings();

        // Wrap in a COUNT subquery
        $sql = "SELECT COUNT(*) AS aggregate FROM ({$innerSql}) AS sub";

        // Execute with PDO and return the integer result
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($bindings);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return (int) ($result['aggregate'] ?? 0);
    }

    /**
     * Paginate the given query into a structured array of results.
     *
     * @param int $perPage The number of items to show per page.
     * @param int $currentPage The current page number.
     * @return array An array containing the Paginator instance and the items for the current page.
     */
    public function paginate(int $perPage, int $currentPage = 1): array
    {
        $totalItems = $this->count();
        $paginator = new Paginator($totalItems, $perPage, $currentPage);

        // The main query (not the count) should still hydrate models.
        $items = $this->limit($perPage)
            ->offset($paginator->getOffset())
            ->get();

        return [
            'paginator' => $paginator,
            'items' => $items,
        ];
    }

    /**
     * Get the aggregate function data for the Grammar.
     * @return array|null
     */
    public function getAggregate(): ?array
    {
        return $this->aggregate;
    }

    /**
     * {@inheritDoc}
     */
    public function with(string|array $relations): static
    {
        $rels = is_array($relations) ? $relations : [$relations];
        // normalize and de-duplicate
        $rels = array_values(array_unique(array_map('strval', $rels)));
        $this->with = array_values(array_unique(array_merge($this->with, $rels)));

        return $this;
    }

    /**
     * Hook to run after fetching rows in get().
     * Called by QueryBuilderGetTraits::get() before returning the results.
     *
     * @param array $rows
     * @return array
     */
    protected function afterFetch(array $rows): array
    {
        if (! empty($rows) && ! empty($this->with) && $this->modelClass) {
            EagerLoader::load($this->pdo, $this->modelClass, $rows, $this->with);
        }

        return $rows;
    }

    /**
     * {@inheritDoc}
     */
    public function toSql(): string
    {
        return match ($this->type) {
            'select' => $this->grammar->compileSelect($this),
            'insert' => $this->grammar->compileInsert($this),
            'update' => $this->grammar->compileUpdate($this),
            'delete' => $this->grammar->compileDelete($this),
            default => throw new \LogicException("Invalid query type [{$this->type}]"),
        };
    }
}
