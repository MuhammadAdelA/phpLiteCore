<?php

namespace MyApp\app\Database;

use JetBrains\PhpStorm\Pure;
use PDO;
use PDOException;
use const MyApp\Database\debug;
use const MyApp\Database\sqlsrv_db_name;
use const MyApp\Database\sqlsrv_db_pass;
use const MyApp\Database\sqlsrv_db_user;

class SQLSRV extends Database
{

    public static object $instance; // The current instance
    public string $rowsCount;

    private $connection;
    const DB_HOST     = sqlsrv_db_host;      // Database host
    const DB_PORT     = sqlsrv_db_port;      // Database port
    const DB_NAME     = sqlsrv_db_name;      // Database name
    const DB_USER     = sqlsrv_db_user;      // Database login
    const DB_PASS     = sqlsrv_db_pass;      // Database login
    protected string $db_driver     = 'sqlsrv';      // Database password
    const DB_CHARSET = "utf-8";     // Database Character Set

    private string $error_message;

    protected array $identifier_limiter = array("[", "]");
    protected array $db_tables;
    protected bool $alias_supported = true;

    public array $bindings = [];
    /**
     * @var false|resource
     */
    private $stmt;
    public int $num_rows;

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
     * Establishing Database connection
     * @return void
     */
    function connect(): void
    {
        $connection_info = [
            "Database"                  => sqlsrv_db_name,
            "UID"                       => sqlsrv_db_user,
            "PWD"                       => sqlsrv_db_pass,
            "Encrypt"                   => true,
            "TrustServerCertificate"    => true,
            "CharacterSet"              => "utf-8"
        ];

        if(!$this->connection = sqlsrv_connect(static::DB_HOST, $connection_info)){
            $this->error_message = "Database connection failed";
            die($this->debug_errors(sqlsrv_errors()));
        }
    }

    /**
     * Closing connection
     */
    public function disconnect(): void
    {
        if (isset($this->connection)) {
            sqlsrv_close($this->connection);
            unset($this->connection);
        }
    }

    /**
     * Preparing database value to preventing sql injections
     * @param string $value
     * @return string Prepared value
     */
    function prep(string $value): string
    {
        return str_replace("'", "''", $value);
    }

    /**
     * Apply a normal database query
     * @param string $sql
     * @return false|resource|void
     */
    public function sqlquery(string $sql)
    {
        $this->last_query = $sql;
        $this->stmt = sqlsrv_query($this->connection, $sql);
        $this->error_message = "Database query failed";
        if(!$this->stmt) {
            die(json_encode(["status"=>"error", "message"=>$this->debug_errors(sqlsrv_errors())]));
        }
        return $this->stmt;
    }

    /**
     * Perform an INSERT query to the database
     * @param string $table
     * @param string|array $columns
     * @param string|array $values
     * @param bool $bind
     * @return int|bool if success it returns "inserted id" and if failed "false"
     */
    public function insert(string $table, string|array $columns, array|string $values, bool $bind=true): int|bool
    {
        // Insert into table
        $sql = "INSERT INTO " . $this->protectIdentifiers($table);

        $values = is_string($values) ? [$values] : $values;

        $vls = [];

        foreach ($values as $value) {
            $vls[] = "'".$value."'";
        }

        // Now insert values into columns
        $sql .= ' ('. implode(', ', $columns) . ') VALUES (' . implode(', ', $vls) . ')';


        $stmt = sqlsrv_query( $this->connection, $sql, $values);
        if( $stmt === false ) {
            die( print_r( sqlsrv_errors(), true));
        } else {
            return true;
        }
    }

    /**
     * Update database record
     *
     * @param string $table
     * @param string|string[] $columns
     * @param string|string[] $values
     * @param bool $bind
     * @return int
     */
    public function update(string $table, array|string $columns, array|string $values, bool $bind=true): int
    {
        $sql = "UPDATE " . $this->protectIdentifiers($table);

        $sql .= " SET " . $this->set($columns, $values, $bind);

        if($this->where_clause != "") {
            $sql .= " WHERE" . $this->where_clause;
        }

        // Preparing the statement
        $stmt = $this->prepare($sql);

        // Apply the prepared query
        try {
            $stmt->execute($this->bindings);

            // Reset bindings & where condition
            $this->bindings = [];
            $this->where_clause = "";

            return $stmt->rowCount();

        } catch (PDOException $e) {
            $this->error = "Database query failed";
            exit($this->debug_errors($e));
        }
    }

    /**
     * Delete database records
     *
     * @param string $table
     * @return int
     */
    function delete(string $table): int
    {
        $sql = "DELETE FROM {$this->protectIdentifiers($table)}  WHERE $this->where_clause";

        // Preparing the statement
        $stmt = $this->prepare($sql);
        // Apply the prepared query
        try {
            $stmt->execute($this->bindings);

            // Reset bindings & where condition
            $this->bindings = [];
            $this->where_clause = "";

            return $stmt->rowCount();
        } catch (PDOException $e) {
            $this->error = "Database query failed";
            exit($this->debug_errors($e));
        }
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
        $this->where($search_column, $search, $operator);
        return $this->fetch(limit: 1);
    }

    /**
     * Return value for certain column
     *
     * @param string $column
     * @return string|bool
     */
    public function get_this(string $column): string|bool
    {
        $found = $this->fetch($column,1);
        return $found ? ($found[$column] == null ? "" : $found[$column]) : false;
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
        $this->where($column, $search);
        return $this->fetch();
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
        return $this->fetch();
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
        return $this->fetch();
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
        return $this->fetch();
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
        return $this->fetch();
    }

    /**
     * Select from database
     * @param string|string[] $columns
     * @param string $limit
     * @param string $offset
     * @param string $order
     * @return object|bool|array|null
     */
    public function fetch (
        string|array $columns = "*",
        string $limit = "20",
        string $offset = "",
        string $order = "",
    ): object|bool|array|null
    {
        $all = $limit > 1;

        $sql = '';
        
        // Pagination process it must has $offset && $limit.
        if($offset !== '' && $limit !== ''){
            if ($order === '')
                trigger_error('Please specify an order for sorting', E_USER_ERROR);

            $sql .= "SELECT  * FROM ( SELECT ROW_NUMBER() OVER ( ORDER BY [$order]) AS RowNum,";
        } else {
            $sql .= "SELECT ";
            $sql .= "TOP (" . ($all ? $limit : "1") . ") ";
        }
        $sql .= " {$this->prepareColumns($columns)} FROM [$this->table]";

        $this->where_clause !== ""  && $sql .= " WHERE "     . $this->where_clause;

        if($offset !== '' && $limit !== '') {
            $sql .= ") AS RowConstrainedResult WHERE RowNum >= $offset AND RowNum < $limit ORDER BY RowNum";
        } else {
            $order !== ""               && $sql .= " ORDER BY " . $order;
        }

        $rows = [];
        $stmt = $this->sqlquery($sql);

        $this->bindings = [];
        $this->where_clause = '';

        if ($all)
        {
            while ($row = $this->fetch_assoc($stmt)) $rows[] = $row;
            $this->num_rows = count($rows);

            return ($rows);
        }
        else return $this->fetch_assoc($stmt);

    }

    /**
     * Prepare where clues
     */
    public function where(
        string $column,
        array|string $values,
        string $operator="=",
        bool $bind=false
    ): self
    {
        $this->where_group($column, $values, $operator, $bind);
        return $this;
        /*// Return the where clues immediately if $whereClue is string
        if (is_string($whereClue)) {
            return $whereClue;
        }
        // Default error message
        $e = "<b>Fatal Error</b>: syntax error in " . (debug ? "<i>" . __METHOD__ . "()</i> " : "");
        foreach ($whereClue as $column => $arguments) {
            //
            if (!is_string($column)) {
                !is_array($arguments) or die($e . "unexpected array given as column name, it is expected to be a string");
                die($e . "'=>' was missing after '" . $arguments . "' or it not defined as array key");
            }
            if (is_array($arguments) && count($arguments) > 2)
                die("unexpected arguments count for <i>" . $column . "</i> expects max 2 elements for each column");
            if (!is_array($arguments))
                $arguments = [$arguments];
            $where .= $this->prepare_where($column, $arguments[0], (isset($arguments[1]) ? $arguments[1] : self::MATCHES));
        }
        return $where;*/
    }

    /**
     * Prepare WHERE group to search for one column with one or multiple values.
     *
     * @param string $column
     * @param string|array $values one value or different values for the column
     * @param string $operator
     * @param bool $bind
     * @return self
     */
    public function where_group(
        string $column,
        array|string $values,
        string $operator="LIKE",
        bool $bind=true
    ): self
    {

        // Trying to convert string $values to array
        is_string($values) &&
        // If it is a string array format
        $values = str_contains($values, ",") ?
            // Let's break it down to an array
            explode(",", $values) :
            // Or make it an array
            array($values);

        // For safety, we should add extra white space at the beginning of where clause
        $condition = " ";

        // Lets count values
        $count = count($values);

        // Is it a group ?
        ($count > 1) &&
        // so lets open a group
        $condition .= "(";

        // iterating MySQL statements for each value
        foreach ($values as $index => $value) {

            // Trim unnecessary white space if value is a string
            $value = is_string($value) ? trim($value) : $value;

            // Place holder for binding
            $place_holder = ":where_{$column}_$index";

            // Place the column with the provided operator
            $condition .= $this->protectIdentifiers($column) . " $operator ";

            // Is it between two values
            if (str_contains(strtoupper(trim($operator)), "BETWEEN"))
            {
                // Check if the between value looks like (val1|val2) to split it
                !preg_match("/^\(.+\|.+\)$/", $value) && die ("<b>Syntax error: </b>between syntax error.");

                // Split and trim clean from the parentheses
                list($val1, $val2) = explode("|", ltrim(rtrim($value, ")"), "("));

                if ($bind) {
                    $condition .= "{$place_holder}_start AND {$place_holder}_end";
                    $this->bindings["{$place_holder}_start"] = $val1;
                    $this->bindings["{$place_holder}_end"] = $val2;
                } else {
                    $condition .= "'$val1' AND '$val2'";
                }

            } elseif (str_contains(strtoupper(trim($operator)), "IN")) {

                if(!is_array($value)) {
                    $value = [$value];
                }
                $in_count = count($value);

                if ($bind) {
                    $condition .= "(";
                    foreach ($value as $i => $v) {
                        $condition .= "{$place_holder}_{$i},";
                        $this->bindings["{$place_holder}_{$i}"] = $v;

                        if ($i == $in_count-1) {
                            $condition = rtrim($condition, ",");
                            $condition .= ")";
                            break;
                        }
                    }
                }

            } else {

                if ($bind) {
                    $condition .= $place_holder;
                    if (array_key_exists($place_holder, $this->bindings)){
                        trigger_error("`$column` column is already used in another WHERE group, this will cause it to be overwritten and will cause unexpected results, if you want to use same column name, use it in the same WHERE group", E_USER_WARNING);
                    }
                    $this->bindings[$place_holder] = $value;
                } else {
                    $condition .= "'".$value."'";
                }
            }

            // Last condition there is no need to add "OR"
            if ($index == $count-1) {
                if ($count > 1) {
                    $condition .= ")";
                }
                break;
            }
            // keep adding "OR" each value
            $condition .= " OR ";
        }
        $this->where_clause .= $condition;

        return $this;
    }

    /**
     * Prepare WHERE group to search for one column with one or multiple values,
     * starting with "AND" this is a supplement for full WHERE CLAUSE statement
     * and can't be valid alone unless you concatenate it with another (true)
     * WHERE CLAUSE such as "1=1 " to do not affect the results.
     *
     * @param string $column
     * @param array|string $values
     * @param string $operator
     * @param bool $bind
     * @return $this
     */
    public function and_where(string $column, array|string $values, string $operator = "=", bool $bind=false): self
    {
        if ($this->where_clause === "") trigger_error("You can't use `".__FUNCTION__."` function unless you use `where_group`", E_USER_ERROR);
        $this->where_clause .= " AND ";
        $this->where($column, $values, $operator, $bind);
        return $this;
    }

    /**
     * Prepare WHERE group to search for one column with one or multiple values,
     * starting with "OR" this is a supplement for full WHERE CLAUSE statement
     * and can't be valid alone unless you concatenate it with another (false)
     * WHERE CLAUSE such as "1=2 " to do not affect the results.
     *
     * @param string $column
     * @param array|string $values
     * @param string $operator
     * @param bool $bind
     * @return $this
     */
    public function or_where(string $column, array|string $values, string $operator = "=", bool $bind=true): self
    {
        if ($this->where_clause === "") trigger_error("You can't use `".__FUNCTION__."` function unless you use `where_group`", E_USER_ERROR);
        $this->where_clause .= " OR ";
        $this->where($column, $values, $operator, $bind);
        return $this;
    }

    /**
     * Prepare WHERE group to search for one column with one or multiple values,
     * starting with "OR" this is a supplement for full WHERE CLAUSE statement
     * and can't be valid alone unless you concatenate it with another (false)
     * WHERE CLAUSE such as "1=2 " to do not affect the results.
     *
     * @param string $column
     * @param array|string $values
     * @param bool $bind
     * @return $this
     */
    public function where_starts(string $column, array|string $values, bool $bind=true): self
    {
        $this->where($column, "$values%", "LIKE", $bind);
        return $this;
    }

    /**
     * Prepare WHERE group to search for one column with one or multiple values,
     * starting with "OR" this is a supplement for full WHERE CLAUSE statement
     * and can't be valid alone unless you concatenate it with another (false)
     * WHERE CLAUSE such as "1=2 " to do not affect the results.
     *
     * @param string $column
     * @param array|string $values
     * @param bool $bind
     * @return $this
     */
    public function where_has(string $column, array|string $values, bool $bind=true): self
    {
        $this->where($column, "%$values%", "LIKE", $bind);
        return $this;
    }

    /**
     * Prepare WHERE group to search for one column with one or multiple values,
     * starting with "OR" this is a supplement for full WHERE CLAUSE statement
     * and can't be valid alone unless you concatenate it with another (false)
     * WHERE CLAUSE such as "1=2 " to do not affect the results.
     *
     * @param string $column
     * @param array|string $values
     * @param bool $bind
     * @return $this
     */
    public function where_ends(string $column, array|string $values, bool $bind=true): self
    {
        $this->where($column, "%$values", "LIKE", $bind);
        return $this;
    }

    /**
     * Prepare WHERE group to search for one column with one or multiple values,
     * starting with "OR" this is a supplement for full WHERE CLAUSE statement
     * and can't be valid alone unless you concatenate it with another (false)
     * WHERE CLAUSE such as "1=2 " to do not affect the results.
     *
     * @param string $column
     * @param string $value1
     * @param string $value2
     * @param bool $bind
     * @return $this
     */
    public function where_between(string $column, string $value1, string $value2, bool $bind=true): self
    {
        $values = "($value1|$value2)";
        $this->where($column, $values, 'BETWEEN', $bind);
        return $this;
    }

    /**
     * Prepare WHERE group to search for one column with one or multiple values,
     * starting with "OR" this is a supplement for full WHERE CLAUSE statement
     * and can't be valid alone unless you concatenate it with another (false)
     * WHERE CLAUSE such as "1=2 " to do not affect the results.
     *
     * @param string $column
     * @param string $value1
     * @param string $value2
     * @param bool $bind
     * @return $this
     */
    public function and_where_between(string $column, string $value1, string $value2, bool $bind=true): self
    {
        $values = "($value1|$value2)";
        $this->and_where($column, $values, 'BETWEEN', $bind);
        return $this;
    }

    /**
     * @param string $column
     * @param array $value
     * @param bool $bind
     * @return $this
     */
    public function where_in(string $column, array $value, bool $bind=true): self
    {
        $this->where($column, [$value], 'IN', $bind);
        return $this;
    }

    /**
     * @param string $column
     * @param array $value
     * @param bool $bind
     * @return $this
     */
    public function and_where_in(string $column, array $value, bool $bind=true): self
    {
        $this->and_where($column, [$value], 'IN', $bind);
        return $this;
    }

    /**
     * @param string $column
     * @param array $value
     * @param bool $bind
     * @return $this
     */
    public function where_not_in(string $column, array $value, bool $bind=true): self
    {
        $this->where($column, [$value], 'NOT IN', $bind);
        return $this;
    }

    public function and_where_has(string $column, string $value, bool $bind=true): static
    {
        $this->and_where($column, "%$value%", "LIKE", $bind);
        return $this;
    }

    /**
     * @param string $column
     * @param array $value
     * @param bool $bind
     * @return $this
     */
    public function and_where_not_in(string $column, array $value, bool $bind=true): self
    {
        $this->and_where($column, [$value], 'NOT IN', $bind);
        return $this;
    }


    /**
     * Check if the column exists in table
     *
     * @param string table name
     * @param string column name
     * @return array|bool
     */
    public function column_exists(string $table, string $column): bool|array
    {
        return ($this->sqlquery("SHOW COLUMNS FROM " . $this->protectIdentifiers($table) . " LIKE '" . $column . "'")->fetch(PDO::FETCH_ASSOC));
    }

    /**
     * Get all columns in table
     *
     * @return array|false
     */
    public function show_columns(): array|false
    {
        // MySQL query string and confirm that table is exists
        $sql = "SHOW COLUMNS FROM `" . static::DB_NAME . "`." . $this->protectIdentifiers($this->table);

        $stmt = $this->sqlquery($sql);
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
     * Count rows from table
     *
     * @param string $column
     * @param bool $reset
     * @return int
     */
    public function cnt(string $column = "*", bool $reset = true): int
    {
        $sql = "SELECT COUNT({$this->prepareColumns($column)})";
        $sql .= ($this->alias_supported ? " as " : " ");
        $sql .= $this->protectIdentifiers("cnt");
        $sql .= " FROM " . $this->protectIdentifiers($this->table);

        if($this->where_clause !== "") {
            $sql .= " WHERE " . $this->where_clause;
        }

        $stmt = $this->sqlquery($sql);
        if ($reset) {
            $this->where_clause = "";
            $this->bindings = [];
        }
        return $this->fetch_assoc($stmt)['cnt'];
    }

    /**
     * This is helping tool to output database errors friendly looking
     *
     * @param array $errors
     * @return string
     */
    #[Pure] protected function debug_errors(array $errors): string
    {
        $output = "<section lang='en' dir='ltr' class='d-flex flex-column justify-content-center align-content-center vh-100'>";
        $output .= "<div class='m-auto alert alert-danger'>";
        $output .= "<div class='text-center fw-bold'>$this->error_message</div>";

        if (debug == 'Development') {
            if($errors != null) {
                foreach ($errors as $error){
                    $output .= "<div class='mb-3'><b>SQLSTATE: </b> {$error['SQLSTATE']}";
                    $output .= "<br><b>Error code: </b> {$error['code']}";
                    $output .= "<br><b>SQL Said: </b> {$error['message']}";
                    $output .= "</div>";
                }

            } else {
                $output .= "<b>Unknown error</b>";
            }

            if ($this->last_query !== "") {
                $output .= "<p><b>Last query: </b> $this->last_query</p>";
            }

        }
        $output .= "</div></section>";
        return ($output);
    }

    /**
     * Store all tables in the current database into the current instance
     */
    protected function setTables(): void
    {
        if (!$this->db_tables = $this->getTables()) {
            die("Could not be fined any tables in database '<i>".static::DB_NAME."</i>'");
        }
    }


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

    /************** Group Of Helpers ****************/
    public function fetch_array($stmt): bool|array|null
    {
        return sqlsrv_fetch_array($stmt);
    }

    public function fetch_assoc($stmt): bool|array|null
    {
        return sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    }

    public function num_rows($stmt): int|string
    {
        return sqlsrv_num_rows($stmt);
    }

    public function insert_id()
    {
        return mysqli_insert_id($this->connection);
    }

    public function affected_rows()
    {
        return mysqli_affected_rows($this->connection);
    }

    /**
     * Getting all tables in the current database
     *
     * @return array if no tables then false or return array of table names
     */
    public function getTables(): array
    {
        // select all tables from current database
        $this->last_query = "SELECT name FROM sys.tables";
        $tables = [];
        // let's select all the tables
        if ($stmt = $this->sqlquery($this->last_query)) {
            while ($row = $this->fetch_assoc($stmt)) {
                $tables[] = $this->prepareColumns($row['name']);
            }
        }
        return ($tables);
    }

}