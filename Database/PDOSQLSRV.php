<?php

namespace MyApp\Database;

use PDO;
use PDOException;
use PDOStatement;

class PDOSQLSRV extends Database
{
    public static object $instance;                     // The current instance
    public string $rowsCount;
    protected PDO $dbh;
    protected string $db_host     = mysql_db_host;      // Database host
    protected string $db_port     = mysql_db_port;      // Database port
    protected string $db_name     = mysql_db_name;      // Database name
    protected string $db_user     = mysql_db_user;      // Database member
    protected string $db_pass     = mysql_db_pass;      // Database password
    protected string $db_driver     = 'sqlsrv';         // Database driver
    protected string $charset = "utf-8";
    protected array $identifier_limiter = ['[',']'];
    protected bool $alias_supported = true;

    protected array $options = [];

    protected function __construct()
    {
        $this->connect();
    }

    /**
     * Returning the current instance
     * @return static
     */
    public static function get_instance(): object
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Opening mssql connection
     */
    public function connect() {
        try {

            $dsn = "$this->db_driver:server=".sqlsrv_db_host."; Database=".sqlsrv_db_name;
            $this->dbh = new PDO(
                $dsn,
                sqlsrv_db_user,
                sqlsrv_db_pass,
                [
                    'Encrypt' => true,
                    'TrustServerCertificate' => true,
                    'CharacterSet' => $this->charset,
                ]
            );
            $this->dbh->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            $this->dbh->setAttribute( PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC );
        } catch (\PDOException $e) {
            $this->error = "Database connection failed";
            exit($this->debug_error($e));
        }
    }

    /**
     * Getting all tables in the current database
     *
     * @return array if no tables then false or return array of table names
     */
    public function getTables(): array
    {
        // select all tables from current database
        $sql = "SELECT [name] FROM [sys].[tables]";
        $tables = [];
        // let's select all the tables
        if ($stmt = $this->query($sql)) {
            $results = $stmt->fetchAll();
            foreach ($results as $result) {
                $tables[] = $this->protectIdentifiers($result['name']);
            }
        }
        return ($tables);
    }

    /**
     * Perform an INSERT query to the database
     * @param string|array $columns
     * @param string|array $values
     * @param bool $bind
     * @return int if success it returns "inserted id" and if failed "false"
     */
    public function insert(string|array $columns, array|string $values, bool $bind=true): int
    {
        $sql = "INSERT INTO " . $this->table;
        $sql .= $this->set($columns, $values, $bind, false);

        $stmt = $this->prepare($sql);
        $stmt->execute($this->bindings);

        // Reset bindings
        $this->bindings = [];

        return $this->dbh->lastInsertId();
    }

    /**
     * Update database record
     *
     * @param string|string[] $columns
     * @param string|string[] $values
     * @param bool $bind
     * @return int
     */
    public function update(array|string $columns, array|string $values, bool $bind=true): int
    {
        $sql = "UPDATE " . $this->confirmTables($this->table);
        $sql .= " SET " . $this->set($columns, $values, $bind);

        if($this->where_clause != "") {
            $sql .= " WHERE" . $this->where_clause;
        }

        // Preparing the statement
        $stmt = $this->prepare($sql);

        // Apply the prepared query
        $stmt->execute($this->bindings);

        // Reset bindings & where condition
        $this->bindings = [];
        $this->where_clause = "";

        return $stmt->rowCount();
    }

    /**
     * Delete database records
     * @return int
     */
    function delete(): int
    {
        $sql = "DELETE FROM $this->table";

        if($this->where_clause !== "") {
            $sql .= " WHERE  $this->where_clause";
        }

        // Preparing the statement
        $stmt = $this->prepare($sql);
        try {
            $stmt->execute($this->bindings);

            // Reset bindings & where condition
            $this->bindings = [];
            $this->where_clause = "";

            return $stmt->rowCount();
        } catch (PDOException $e) {
            $this->error = "Database query failed";
            exit($this->debug_error($e));
        }
    }

    /**
     * Check if the column exists in table
     *
     * @param string $column column name
     * @return array|bool
     */
    public function column_exists(string $column): bool|array
    {
        return (
        $this->query("SHOW COLUMNS FROM " . $this->table . " LIKE '" . $column . "'")
            ->fetch(PDO::FETCH_ASSOC)
        );
    }
}