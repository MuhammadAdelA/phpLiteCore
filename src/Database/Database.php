<?php

declare(strict_types=1);

namespace PhpLiteCore\Database;

use PDO;
use PDOStatement;
use PhpLiteCore\Database\Grammar\GrammarInterface;
use PhpLiteCore\Database\Grammar\MySqlGrammar;
use PhpLiteCore\Database\QueryBuilder\BaseQueryBuilder;

/**
 * The Database class manages the PDO connection and acts as a factory for the QueryBuilder.
 * It provides methods for raw queries, transactions, and starting fluent queries.
 */
class Database
{
    /** @var PDO The active PDO connection instance. */
    protected PDO $pdo;

    /** @var GrammarInterface The SQL grammar compiler. */
    protected GrammarInterface $grammar;

    /**
     * Database constructor.
     * Establishes the database connection using PDO.
     *
     * @param array $config The database connection configuration.
     */
    public function __construct(array $config)
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $config['host'],
            $config['port'],
            $config['database'],
            $config['charset']
        );

        $this->pdo = new PDO(
            $dsn,
            $config['username'],
            $config['password'],
            // Set error mode to exception for robust error handling.
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );

        // Use MySqlGrammar by default; this can be replaced via the setter.
        $this->grammar = new MySqlGrammar();
    }

    /**
     * Begin a fluent query builder instance for a specific table.
     *
     * @param string $table The name of the table to query.
     * @return BaseQueryBuilder
     */
    public function table(string $table): BaseQueryBuilder
    {
        return $this->queryBuilder()->from($table);
    }

    /**
     * Get a fresh QueryBuilder instance.
     *
     * @return BaseQueryBuilder
     */
    public function queryBuilder(): BaseQueryBuilder
    {
        // Pass the PDO connection, grammar, and this Database instance to the builder.
        // This allows the builder to perform actions like insertAndGetId.
        return new BaseQueryBuilder($this->pdo, $this->grammar, $this);
    }

    /**
     * Execute a raw SQL query with bindings.
     *
     * @param string $sql The raw SQL query string.
     * @param array $bindings The parameters to bind to the query.
     * @return PDOStatement The prepared statement after execution.
     */
    public function raw(string $sql, array $bindings = []): PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($bindings);
        return $stmt;
    }

    /**
     * Execute an insert statement and return the last inserted ID.
     *
     * @param string $sql The raw SQL INSERT statement.
     * @param array $bindings The parameters to bind to the query.
     * @return string|false The ID of the last inserted row or false on failure.
     */
    public function insertAndGetId(string $sql, array $bindings = []): string|false
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($bindings);
        return $this->pdo->lastInsertId();
    }

    /**
     * Begin a new database transaction.
     * @return bool
     */
    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    /**
     * Commit the active database transaction.
     * @return bool
     */
    public function commit(): bool
    {
        return $this->pdo->commit();
    }

    /**
     * Roll back the active database transaction.
     * @return bool
     */
    public function rollBack(): bool
    {
        return $this->pdo->rollBack();
    }

    /**
     * Set a custom SQL grammar instance.
     * @param GrammarInterface $grammar The grammar instance.
     */
    public function setGrammar(GrammarInterface $grammar): void
    {
        $this->grammar = $grammar;
    }

    /**
     * Get the underlying PDO connection instance.
     * @return PDO
     */
    public function getPdo(): PDO
    {
        return $this->pdo;
    }
}