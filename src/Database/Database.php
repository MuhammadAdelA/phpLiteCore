<?php

declare(strict_types=1);

namespace PhpLiteCore\Database;

use PDO;
use PDOStatement;
use PhpLiteCore\Database\Grammar\GrammarInterface;
use PhpLiteCore\Database\Grammar\MySqlGrammar;
use PhpLiteCore\Database\QueryBuilder\BaseQueryBuilder;

/**
 * Database class manages PDO connection and provides QueryBuilder integration.
 */
class Database
{
    protected PDO $pdo;
    protected GrammarInterface $grammar;

    /**
     * Initialize connection and default grammar (MySQL).
     * @param array $config  [
     *     'host' => string,
     *     'port' => int,
     *     'database' => string,
     *     'username' => string,
     *     'password' => string,
     *     'charset' => string,
     * ]
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
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        // Use MySqlGrammar by default; can be replaced via setter
        $this->grammar = new MySqlGrammar();
    }

    /**
     * Begin a fluent query on a table.
     */
    public function table(string $table): BaseQueryBuilder
    {
        return (new BaseQueryBuilder($this->pdo, $this->grammar))
            ->select('*')
            ->from($table);
    }

    /**
     * Get a fresh QueryBuilder instance (for custom queries).
     */
    public function queryBuilder(): BaseQueryBuilder
    {
        return new BaseQueryBuilder($this->pdo, $this->grammar);
    }

    /**
     * Execute raw SQL with bindings.
     * @param string $sql
     * @param array $bindings
     * @return PDOStatement
     */
    public function raw(string $sql, array $bindings = []): PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($bindings);
        return $stmt;
    }

    /**
     * Transaction helpers.
     */
    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->pdo->commit();
    }

    public function rollBack(): bool
    {
        return $this->pdo->rollBack();
    }

    /**
     * Allow swapping the grammar (e.g. for PostgreSQL).
     */
    public function setGrammar(GrammarInterface $grammar): void
    {
        $this->grammar = $grammar;
    }

    /**
     * Expose underlying PDO for advanced operations.
     */
    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    /**
     * Get the ID of the last inserted row.
     *
     * @return string
     */
    public function lastInsertId(): string
    {
        return $this->pdo->lastInsertId();
    }

    /**
     * Get the number of rows affected by a statement.
     *
     * @param PDOStatement $stmt
     * @return int
     */
    public function rowCount(PDOStatement $stmt): int
    {
        return $stmt->rowCount() ?? 0;
    }
}
