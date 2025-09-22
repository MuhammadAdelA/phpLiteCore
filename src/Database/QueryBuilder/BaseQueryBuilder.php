<?php
declare(strict_types=1);

namespace PhpLiteCore\Database\QueryBuilder;

use PDO;
use PhpLiteCore\Database\Grammar\GrammarInterface;
use PhpLiteCore\Database\QueryBuilder\Traits\QueryBuilderGetTraits;
use PhpLiteCore\Database\QueryBuilder\Traits\QueryBuilderLikeTraits;
use PhpLiteCore\Database\QueryBuilder\Traits\QueryBuilderWhereTraits;
use PhpLiteCore\Pagination\Paginator;

/**
 * BaseQueryBuilder provides a fluent interface to build SQL queries.
 */
class BaseQueryBuilder implements QueryBuilderInterface
{
    use QueryBuilderWhereTraits;
    use QueryBuilderLikeTraits;
    use QueryBuilderGetTraits;

    /** @var PDO The PDO connection instance */
    protected PDO $pdo;

    /** @var GrammarInterface The SQL grammar compiler */
    protected GrammarInterface $grammar;

    /** @var string Query type: select, insert, update, delete */
    protected string $type = 'select';

    /** @var array Selected columns */
    protected array $columns = [];

    /** @var string|null Table name */
    protected ?string $table = null;

    /** @var string|null Table alias */
    protected ?string $alias = null;

    /** @var array JOIN clauses */
    protected array $joins = [];

    /** @var array WHERE clauses data */
    protected array $wheres = [];

    /** @var array GROUP BY columns */
    protected array $groups = [];

    /** @var array ORDER BY clauses */
    protected array $orders = [];

    /** @var int|null LIMIT */
    protected ?int $limit = null;

    /** @var int|null OFFSET */
    protected ?int $offset = null;

    /** @var array|null The aggregate function to apply (e.g., COUNT, SUM) */
    protected ?array $aggregate = null;

    /** @var array Bindings for a prepared statement */
    protected array $bindings = [];
    private array $pattern;
    public int $rowCount;

    /**
     * Constructor.
     *
     * @param PDO               $pdo     PDO instance.
     * @param GrammarInterface  $grammar Grammar for SQL compilation.
     */
    public function __construct(PDO $pdo, GrammarInterface $grammar)
    {
        $this->pdo     = $pdo;
        $this->grammar = $grammar;
    }

    /**
     * {@inheritDoc}
     */
    public function select(string ...$columns): static
    {
        // Set the query type and store selected columns
        $this->type    = 'select';
        $this->columns = $columns;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function from(string $table, ?string $alias = null): static
    {
        // Store table and optional alias
        $this->table = $table;
        $this->alias = $alias;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function insert(string $table, array $data): static
    {
        // Switch to insert mode, set table and data bindings
        $this->type     = 'insert';
        $this->table    = $table;
        $this->bindings = array_values($data);
        $this->columns  = array_keys($data);
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function update(string $table, array $data): static
    {
        // Switch to update mode, set table, and data bindings
        $this->type     = 'update';
        $this->table    = $table;
        $this->bindings = array_values($data);
        $this->columns  = array_keys($data);
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function delete(): static
    {
        // Switch to delete mode
        $this->type = 'delete';
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function groupBy(string ...$columns): static
    {
        // Store GROUP BY columns
        $this->groups = $columns;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function orderBy(string $column, string $direction = 'ASC'): static
    {
        // Add ORDER BY clause
        $this->orders[] = [$column, $direction];
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function limit(int $limit): static
    {
        // Set LIMIT
        $this->limit = $limit;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function offset(int $offset): static
    {
        // Set OFFSET
        $this->offset = $offset;
        return $this;
    }

    /**
     * Execute a query as a "count" query.
     *
     * @param string $columns
     * @return int
     */
    public function count(string $columns = '*'): int
    {
        // Clone the builder to not affect the original query
        $clone = clone $this;

        // Set the aggregate property
        $clone->aggregate = ['function' => 'COUNT', 'columns' => [$columns]];

        // The columns for the main query are not needed for a count.
        $clone->columns = [];

        // Execute the aggregate query
        $result = $clone->get();

        // The result of a COUNT query will be in the first row and first column.
        return (int) ($result[0]['aggregate'] ?? 0);
    }

    /**
     * Paginate the given query.
     *
     * @param int $perPage
     * @param int $currentPage
     * @return array An array containing the Paginator instance and the items.
     */
    public function paginate(int $perPage, int $currentPage = 1): array
    {
        // Get the total number of records by executing a count query.
        $totalItems = $this->count();

        // Create the Paginator instance, which will also validate the current page.
        $paginator = new Paginator($totalItems, $perPage, $currentPage);

        // Apply the limit and offset to the original query to get the items for the current page.
        $items = $this->limit($perPage)->offset($paginator->getOffset())->get();

        return [
            'paginator' => $paginator,
            'items'     => $items,
        ];
    }

    public function getAggregate(): ?array { return $this->aggregate; }

    /**
     * {@inheritDoc}
     */
    public function toSql(): string
    {
        // Delegate SQL compilation to Grammar
        return match ($this->type) {
            'select' => $this->grammar->compileSelect($this),
            'insert' => $this->grammar->compileInsert($this),
            'update' => $this->grammar->compileUpdate($this),
            'delete' => $this->grammar->compileDelete($this),
            default  => throw new \LogicException("Invalid query type [{$this->type}]"),
        };
    }

}