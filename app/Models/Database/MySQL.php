<?php

namespace MyApp\app\Models\Database;

use PDO;
use PDOException;
use const MyApp\Database\debug;

class MySQL extends Database
{
    public static object $instance;                     // The current instance
    public string $rowsCount;
    protected PDO $dbh;
    protected string $db_host     = mysql_db_host;      // Database host
    protected string $db_port     = mysql_db_port;      // Database port
    protected string $db_name     = mysql_db_name;      // Database name
    protected string $db_user     = mysql_db_user;      // Database member
    protected string $db_pass     = mysql_db_pass;      // Database password
    protected string $db_driver     = 'mysql';          // Database password
    protected string $charset = "utf8mb4";
    protected array $identifier_limiter = ['`','`'];
    protected bool $alias_supported = true;

    protected array $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ];
    protected string $distinct = '';
    protected string $order_by = '';
    protected int $limit;
    protected int $offset;

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
     * Opening mysql connection
     */
    private function connect(): void
    {
        $dsn = "$this->db_driver:host=$this->db_host;port=$this->db_port;dbname=$this->db_name;charset=$this->charset";
        try {
            $this->dbh = new PDO($dsn, $this->db_user, $this->db_pass, $this->options);
        } catch (PDOException $e) {
            $this->error = "Database connection failed";
            exit($this->debug_error($e));
        }
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
        $sql .= " SET " . $this->set($columns, $values, $bind);

        $stmt = $this->prepare($sql);
        $stmt->execute($this->bindings);

        // Reset bindings
        $this->bindings = [];

        return $this->last_insert_id = $this->dbh->lastInsertId();
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
     * @param string|array $columns
     * @param int $mode
     * @return object|bool|array
     */
    public function fetch (string|array $columns = "*", int $mode = PDO::FETCH_ASSOC): object|bool|array{
        return $this->limit(1)->fetchAll($columns, mode: $mode);
    }

    /**
     * @param string $order_by
     * @param string|array $columns
     * @param int $mode
     * @return object|bool|array
     */
    public function fetchLast(string $order_by, string|array $columns = "*", int $mode = PDO::FETCH_ASSOC): object|bool|array
    {
        return $this->limit(1)->orderBy($order_by, 'DESC')->fetchAll($columns, mode: $mode);
    }
    /**
     * Fetch database results that match certain value
     *
     * @param string $column
     * @param string $search
     * @return array|bool|object
     */
    public function fetchMatch(string $column, string $search): object|bool|array
    {
        $this->where_group($column, $search);
        return $this->fetchAll();
    }

    /**
     * Fetch database results that starts with certain value
     *
     * @param string $column
     * @param string $search
     * @return array|bool|object
     */
    public function fetchStarts(string $column, string $search): object|bool|array
    {
        $this->where_starts($column, $search);
        return $this->fetchAll();
    }

    /**
     * Fetch database results that ends with certain value
     *
     * @param string $column
     * @param string $search
     * @return array|bool|object
     */
    public function fetchEnds(string $column, string $search): object|bool|array
    {
        $this->where_ends($column, $search);
        return $this->fetchAll();
    }

    /**
     * Fetch database results that contains with certain value
     *
     * @param string $column
     * @param string $search
     * @return array|bool|object
     */
    public function fetchHas(string $column, string $search): object|bool|array
    {
        $this->where_has($column, $search);
        return $this->fetchAll();
    }

    /**
     * Fetch database results that ends between range
     *
     * @param string $column
     * @param string $start
     * @param string $end
     * @return array|bool|object
     */
    public function fetchBetween(string $column, string $start, string $end): object|bool|array
    {
        $this->where_between($column, $start, $end);
        return $this->fetchAll();
    }

    /**
     * Check if database record exists and return the row as an array
     *
     * @param string $search_column
     * @param string $search
     * @param string $operator
     * @return object|bool|array
     */
    public function found(string $search_column, string $search, string $operator="="): object|bool|array
    {
        $this->where_group($search_column, $search, $operator);
        return $this->fetch();
    }

    /**
     * Return value for certain column
     *
     * @param string $column
     * @return string|bool
     */
    public function getThis(string $column): string|bool
    {
        $found = $this->fetch($column);
        return $found ? ($found[$column] == null ? "" : $found[$column]) : false;
    }

    /**
     * Select from database
     * @param string|string[] $columns
     * @param int $mode
     * @return array|object|bool
     */
    public function fetchAll (string|array $columns = "*", int $mode = PDO::FETCH_ASSOC): object|bool|array
    {
        $all = !((isset($this->limit) && $this->limit === 1) && !isset($this->offset));

        $sql = "SELECT $this->distinct {$this->prepareColumns($columns)} FROM $this->table";

        if($this->where_clause !== "") {
            $sql .= " WHERE" . $this->where_clause;
        }

        if($this->order_by !== '') {
            $sql .= ' ORDER BY';
            $sql .= rtrim($this->order_by, ', ');
        }

        if(isset($this->limit)) {
            $sql .= " LIMIT " . $this->limit;
        }

        if(isset($this->offset)) {
            $sql .= ", " . $this->offset;
        }

        // Count results
        $this->rowsCount = $this->cnt(reset: false);
        // Start fetching
        if ($this->rowsCount > 0) {
            // Preparing the statement
            $stmt = $this->prepare($sql);
            // Apply the prepared query
            try {
                $stmt->execute($this->bindings);

                // Reset
                unset($this->limit, $this->offset);
                $this->where_clause = '';
                $this->order_by = '';
                $this->distinct = '';
                $this->bindings = [];

            } catch (PDOException $e) {
                $this->error = "Database query failed";
                exit($this->debug_error($e));
            }
            // Now trying to fetch
            try {
                if ($all) {
                    return $stmt->fetchAll($mode);
                } else {
                    return $stmt->fetch($mode);
                }

            } catch (PDOException $e) {
                if (debug == 'Development') {
                    echo "<b>Last query:</b> $this->last_query<br>";
                    echo "<b>PDO said:</b> {$e->getMessage()}<br>";
                    exit();
                } else {
                    exit("Error while fetching from database");
                }
            }
        } else {
            // Reset
            $this->where_clause = "";
            $this->bindings = [];
            return false;
        }
    }

    public function fetchFromTable(string|array $columns, string $value, string $operator="=", bool $bind = true): self
    {
        /**
        SELECT * FROM `f_exchange`.`member` WHERE (
        CONVERT(`id` USING utf8) LIKE '%m%'
        OR CONVERT(`full_name` USING utf8) LIKE '%m%'
        OR CONVERT(`email` USING utf8) LIKE '%m%'
        OR CONVERT(`phone` USING utf8) LIKE '%m%'
        )
         */

        // trying to convert string $columns into array
        if (is_string($columns)) {
            $columns = str_contains($columns, ",")
                ? explode(",", $columns)
                : array($columns);
        }

        $i = 1;
        foreach ($columns as $column){
            $this->bindings[] = "%$value%";
            if($i == 1) $this->where_clause .= "{$this->protectIdentifiers($column)} LIKE ?";
            else $this->where_clause .= " OR {$this->protectIdentifiers($column)} LIKE ?";
            $i++;
        }

        return $this;
    }

    /**
     * Check if the column exists in table
     *
     * @param string $column column name
     * @return array|bool
     */
    public function columnExists(string $column): bool|array
    {
        return (
            $this->query("SHOW COLUMNS FROM " . $this->table . " LIKE '" . $column . "'")
                ->fetch(PDO::FETCH_ASSOC)
        );
    }

    /**
     * Get all columns in table
     *
     * @return array|false
     */
    public function showColumns(): array|false
    {
        // MySQL query string and confirm that table exists
        $sql = "SHOW COLUMNS FROM `" . $this->db_name . "`." . $this->table;

        $stmt = $this->query($sql);
        while ($cols = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // let's store in rows
            foreach ($cols as $array_key => $values) {
                // Only Fields
                if (strtolower($array_key) == "field") {
                    $fields[] = $values;
                }
            }
        }
        return $fields ?? false;
    }

    /**
     * Prepare columns and values for a query
     *
     * @param string|string[] $columns
     * @param string|string[] $values
     * @param bool $bind
     * @return string
     */


    /**
     * Getting all tables in the current database
     * @return array
     */
    public function getTables(): array
    {
        // select all tables from current database
        $sql = "SELECT * FROM `information_schema`.`tables` WHERE `table_schema` = '$this->db_name'";
        $tables = [];
        // let's select all the tables
        if ($stmt = $this->query($sql)) {
            $results = $stmt->fetchAll();
            foreach ($results as $result) {
                $tables[] = $this->protectIdentifiers($result['TABLE_NAME']);
            }
        }
        return ($tables);
    }

    /**
     * Decides whether we need all or just the uniques
     * @return MySQL
     */
    public function distinct(): MySQL
    {
        $this->distinct = 'DISTINCT';
        return $this;
    }

    /**
     * Apply Singular order by or multiple
     *
     * @param string $order_by
     * @param string $order @default ASC
     * @return $this
     */
    public function orderBy(string $order_by, string $order = "ASC"): MySQL
    {
        $this->order_by .= " {$this->protectIdentifiers($order_by)} $order, " ;
        return $this;
    }

    /**
     * Limit the fetched results
     *
     * @param int $limit
     * @return $this
     */
    public function limit(int $limit): MySQL
    {
        $this->limit = $limit ;
        return $this;
    }

    /**
     * Set the offset for fetching results
     *
     * @param int $offset
     * @return $this
     */
    public function offset(int $offset): MySQL
    {
        $this->offset = $offset ;
        return $this;
    }
}