<?php
declare(strict_types=1);

namespace PhpLiteCore\Database\QueryBuilder;

use PDO;
use PhpLiteCore\Database\Grammar\GrammarInterface;
use PhpLiteCore\Database\QueryBuilder\Traits\QueryBuilderGetTraits;
use PhpLiteCore\Database\QueryBuilder\Traits\QueryBuilderLikeTraits;
use PhpLiteCore\Database\QueryBuilder\Traits\QueryBuilderWhereTraits;

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