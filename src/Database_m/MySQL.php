<?php

namespace MyApp\Database;

use PDO;
use PDOException;
use PDOStatement;

class MySQL extends Database
{
    public static object $instance;                     // The current instance
    public string $rows_count;
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
        try {
            $stmt = $this->prepare($sql);
            $stmt->execute($this->bindings);
        } catch (PDOException $e) {
            echo $e->getMessage();
        }

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
        $sql = "UPDATE " . $this->confirm_tables($this->table);
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
    public function fetch (
        string|array $columns = "*",
        int $mode = PDO::FETCH_ASSOC
    ): object|bool|array
    {
        return $this->fetchAll($columns, limit:  1, mode: $mode);
    }

    /**
     * @param string|array $columns
     * @param int $mode
     * @return object|bool|array
     */
    public function fetchLast (
        string $order_by,
        string|array $columns = "*",
        int $mode = PDO::FETCH_ASSOC
    ): object|bool|array
    {
        return $this->fetchAll($columns, order_by: $order_by, order: 'DESC' ,limit:  1, mode: $mode);
    }
    /**
     * Fetch database results that match certain value
     *
     * @param string $column
     * @param string $search
     * @return array|bool|object
     */
    public function fetch_match(string $column, string $search): object|bool|array
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
    public function fetch_starts(string $column, string $search): object|bool|array
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
    public function fetch_ends(string $column, string $search): object|bool|array
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
    public function fetch_has(string $column, string $search): object|bool|array
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
    public function fetch_between(string $column, string $start, string $end): object|bool|array
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
    public function get_this(string $column): string|bool
    {
        $found = $this->fetch($column);
        return $found ? ($found[$column] == null ? "" : $found[$column]) : false;
    }

    /**
     * Find the highest database record depending on where clue for certain column and return its value
     *
     * @param string $column
     * @return string|bool
     */
    public function get_highest(string $column): bool|string
    {
        $found = $this->fetchAll($column, "CAST($column AS SIGNED) DESC", false);
        return ($found ? $found[$column] : false);
    }

    /**
     * Select from database
     * @param string|string[] $columns
     * @param string $order_by
     * @param string $order
     * @param string $limit
     * @param string $offset
     * @param int $mode
     * @return array|object|bool
     */
    public function fetchAll (
        string|array $columns = "*",
        string       $order_by = "",
        string       $order = "ASC",
        string       $limit = "",
        string       $offset = "",
        int          $mode = PDO::FETCH_ASSOC
    ): object|bool|array
    {
        $all = !($limit == 1);
        $sql = "SELECT {$this->prepare_columns($columns)} FROM $this->table";

        $this->where_clause !== ""   && $sql .= " WHERE"     .$this->where_clause;
        if($order_by !== "")
        {
            $sql .= " ORDER BY " . $this->protect_identifiers($order_by);
            $sql .= $order === "ASC" ? " ASC " : " DESC ";
        }
        $limit !== "" && $sql .= " LIMIT "    .$limit ;
        $offset !== "" && $sql .= ", "         .$offset;

        // Count results
        $this->rows_count = $this->cnt(reset: false);
        // Start fetching
        if ($this->rows_count > 0) {
            // Preparing the statement
            $stmt = $this->prepare($sql);
            // Apply the prepared query
            try {
                $stmt->execute($this->bindings);

                // Reset
                $this->where_clause = "";
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
                if (debug) {
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

    public function fetch_from_table(string|array $columns, string $value, string $operator="=", bool $bind = true): self
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
            if($i == 1) $this->where_clause .= "{$this->protect_identifiers($column)} LIKE ?";
            else $this->where_clause .= " OR {$this->protect_identifiers($column)} LIKE ?";
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
    public function column_exists(string $column): bool|array
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
    public function show_columns(): array|false
    {
        // MySQL query string and confirm that table is exists
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
    public function get_tables(): array
    {
        // select all tables from current database
        $sql = "SELECT * FROM `information_schema`.`tables` WHERE `table_schema` = '$this->db_name'";
        $tables = [];
        // let's select all the tables
        if ($stmt = $this->query($sql)) {
            $results = $stmt->fetchAll();
            foreach ($results as $result) {
                $tables[] = $this->protect_identifiers($result['TABLE_NAME']);
            }
        }
        return ($tables);
    }

    /**
     * Fetch JOIN Function collect data from multiple tables and columns depending on multiple statements "ON" & multiple "WHERE"
     * @ Param $tables (array) ex. array("table1", "table2", ....) table one is the master table;
     * @ Param $columns (multidimensional array) (two levels) ex. array(array("column1", "column2", ..), array("column3", "column4", .. ));
     * @ Param $on (array ex. array("column1", "column2", ....);
     * @ Param $where (string) ex. "`table`.`column` = 'something' AND ... ";
     * @ Param $order_by (string) ex. "ORDER BY `semicolon`";
     * @ Param $group_by (array) ex. array("GROUP BY `table`.`column`", "GROUP BY `table1`.`column1`" OR array("") for ignore grouping);
     * @ Param $limit (string) ex. "LIMIT 1" or "LIMIT 10, 15 <--(offset) ";
     * return associated array or false
     */
    /*public function fetch_join($tables, $columns, $on, $where, $order_by, $group_by = "", $type = "LEFT", $limit = "")
    {
        if ((count($tables) - 1 !== count($on)) || ((count($tables) !== count($columns)))) {
            die("Tables count not match with ON statement count or not match with columns count");
        }
        $sql = "SELECT " . $this->prepare_columns($columns[0]);
        $sql .= " FROM `" . $tables[0] . "`";
        for ($i = 0; $i < count($on); $i++) {
            $sql .= " " . $type . " JOIN (SELECT " . $this->prepare_columns($columns[intval($i + 1)]);
            $sql .= " FROM `" . $tables[intval($i + 1)] . "`" . $group_by[$i] . ") AS `table" . intval($i + 1) . "` ON " . $on[$i];
        }
        $sql .= " WHERE " . $where;
        $sql .= $group_by[0];
        $sql .= " ORDER BY " . $order_by;
        $sql .= $limit != "" ? " LIMIT " . $limit : "";
        //die($sql);
        $result_set = $this->query($sql);
        if ($this->num_rows($result_set) > 0) {
            if ($limit == "") {
                while ($row = $this->fetch_assoc($result_set)) {
                    $rows[] = $row;
                }
                $this->num_rows = count($rows);
                return ($rows);
            } else {
                return $this->fetch_assoc($result_set);
            }
        }
    }*/

    /*public function this_in(string $col, string $id): string
    {
        $in = "(";
        if (strpos($col, "`") === false) {
            $in .= "`{$col}` = '{$id}' OR ";
            $in .= "`{$col}` LIKE '{$id},%' OR ";
            $in .= "`{$col}` LIKE '%,{$id}' OR ";
            $in .= "`{$col}` LIKE '%,{$id},%'";
        } else {
            $in .= $col . " = '{$id}' OR ";
            $in .= $col . " LIKE '{$id},%' OR ";
            $in .= $col . " LIKE '%,{$id}' OR ";
            $in .= $col . " LIKE '%,{$id},%'";
        }
        $in .= ")";
        return ($in);
    }

    public function in_id($id, $col)
    {
        return "IN(" . $col . ")";
    }*/
}