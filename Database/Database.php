<?php

namespace MyApp\Database;

use JetBrains\PhpStorm\Pure;
use PDO;
use PDOException;
use PDOStatement;

abstract class Database
{
    protected PDO $dbh;
    protected static object $instance;
    protected string $db_host;
    protected string $db_port;
    protected string $db_name;
    protected string $db_user;
    protected string $db_pass;
    protected string $db_driver;
    protected string $charset;
    protected bool $alias_supported;
    protected array $identifier_limiter = [];
    protected array $bindings = [];
    protected array $db_tables;
    protected string $table;
    protected array $options = [];
    public string $where_clause = "";
    public int $last_insert_id;
    public string $last_query = "";
    public string $error;
    public string $rowsCount;

    /**
     * Closing mssql connection
     */
    public function disconnect(): void
    {
        if (isset($this->dbh)) {
            unset($this->dbh);
        }
    }

    /**
     * Apply a normal database query
     *
     * @param string $sql
     * @param bool $exit
     * @return PDOStatement|bool
     */
    public function query(string $sql, bool $exit = true): PDOStatement|bool
    {
        $this->last_query = $sql;
        try {
            return $this->dbh->query($sql);
        } catch (PDOException $e) {
            if($exit) {
                $this->error = "Database query failed";
                exit($this->debug_error($e));
            }
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * bind value to SQL statement
     *
     * @param PDOStatement $query
     * @param $parameter
     * @param $value
     * @param null $type
     */
    public function bindValue(PDOStatement $query, $parameter, $value, $type=null): void
    {
        $query->bindValue($parameter, $value, $type);
    }

    /**
     * Begin pdo database transaction
     * @return bool
     */
    public function beginTransaction(): bool
    {
        return $this->dbh->beginTransaction();
    }

    /**
     * Begin pdo database commit transaction
     * @return bool
     */
    public function commit(): bool
    {
        return $this->dbh->commit();
    }

    /**
     * Begin pdo database rollback transaction
     * @return self
     */
    public function rollBack(): self
    {
        $this->dbh->rollBack();
        return $this;
    }

    /**
     * Apply a safe database query
     *
     * @param string $sql
     * @return PDOStatement
     */
    public function prepare(string $sql): PDOStatement
    {
        $this->last_query = $sql;
        return $this->dbh->prepare($sql);
    }

    /**
     * Execute PDO statement
     * @param string $sql
     * @return PDOStatement
     */
    public function execute(string $sql): PDOStatement
    {
        // Preparing the statement
        $stmt = $this->prepare($sql);
        $stmt->execute($this->bindings);
        $stmt->fetch();

        // reset bindings
        $this->bindings = [];
        return $stmt;
    }

    /**
     * Count rows from table
     * @param string $column
     * @param bool $reset
     * @return int
     */
    public function cnt(string $column = "*", bool $reset = true): int
    {
        $sql = "SELECT COUNT({$this->prepareColumns($column)})";
        $sql .= ($this->alias_supported ? " as " : " ");
        $sql .= $this->protectIdentifiers("cnt");
        $sql .= " FROM " . $this->confirmTables($this->table);

        if($this->where_clause !== "") {
            $sql .= " WHERE" . $this->where_clause;
        }

        $stmt = $this->prepare($sql);

        try {
            $stmt->execute($this->bindings);

            // reset
            if ($reset) {
                $this->where_clause = "";
                $this->bindings = [];
            }
        } catch (PDOException $e) {
            exit($e->getMessage());
        }


        return $stmt->fetchColumn();
    }

    /**
     * Protect table or field name(s)
     *
     * @param string $identifier String to be protected
     * @return string
     * @internal
     */
    protected function protectIdentifiers(string $identifier): string
    {
        $id_limiter = $this->identifier_limiter;

        // No escaping character
        if (!$id_limiter) {
            return $identifier;
        }

        $opening = $id_limiter[0];
        $closing = $id_limiter[1];

        // make sure removing any additional limiters
        $identifier = str_replace($id_limiter, "", $identifier);

        // Dealing with a function or other expression? Just return immediately
        if (str_contains($identifier, "(") || str_contains($identifier, "*")) {
            return $identifier;
        }

        // Find if our identifier has an alias, so we don't escape that
        if (str_contains($identifier, " as ")) {
            $alias = strstr($identifier, " as ");
            $identifier = substr($identifier, 0, -strlen($alias));
        } else {
            // Going to be operating on the spaces in strings, to simplify the white-space
            $identifier = trim($identifier);
            $alias = "";
        }

        // Dealing with dots
        $temp = explode(".", $identifier);
        return $opening . implode($closing . "." . $opening, $temp) . $closing . $alias;
    }

    /**
     * Check if values are unique
     * @param array $columns
     * @return void
     */
    protected function isUniqueColumns(array $columns): void
    {
        // make sure all column names are unique
        $cnt = array_count_values($columns);

        foreach ($cnt as $item) {
            if ($item > 1) {
                trigger_error("Column names need to be unique", E_USER_ERROR);
            }
        }
    }

    /**
     * Check if columns equal values
     * @param array $columns
     * @param array $values
     * @return void
     */
    protected function columnsEqualValues(array $columns, array $values): void
    {
        if (count($columns) !== count($values)) {
            trigger_error("Columns count doesn't match the values", E_USER_ERROR);
        }
    }

    /**
     * Set columns = values and bind
     * @param string|array $columns
     * @param string|array $values
     * @param bool $bind
     * @param bool $set
     * @return string
     */
    protected function set(
        string|array $columns,
        string|array $values,
        bool $bind = true,
        bool $set = true
    ): string
    {
        $sql = [];

        if (is_array($values) && is_array($columns))
        {
            $this->columnsEqualValues($columns, $values);
            $this->isUniqueColumns($columns);
            $pairs = array_combine($columns, $values);
        } elseif (is_string($values) && is_string($columns)) {
            $pairs = array($columns => $values);
        } else {
            trigger_error("Both columns & values must be arrays or strings you provided <b>"
                . gettype($columns) . "</b> for columns and <b>"
                . gettype($values) . "</b> for values.", E_USER_ERROR);
        }

        $i = 0;
        $cols = [];
        $vals = [];

        foreach ($pairs as $column => $value) {

            // Real white space trim
            $value = trim($value, " \n\r\t\v\x00");

            // Place columns
            $place_holder = ":{$column}_$i";

            // Making  NULL instead of ""
            //$value == "" && $value = "NULL";

            $cols[$i] = $this->protectIdentifiers($column);

            // don't put single quote with functions and NULL
            if (str_contains($value, "(") || strtoupper($value) == "NULL") {
                $vals[$i] = $this->protectIdentifiers($value);
                $sql[$i] = $cols[$i] . " = " . $vals[$i];
            } else {
                // We need single quote with strings of course
                $vals[$i] = $bind ? $place_holder : '\'' . $value . '\'';
                $sql[] = $cols[$i] . " = " . ($bind ? $place_holder : $vals[$i]);

                if ($bind) {
                    $this->bindings[$place_holder] = $value;
                }
            }
            $i++;
        }

        $sql_result = $set
            ? implode(', ', $sql)
            : ' ('. implode(', ', $cols) .') VALUES (' . implode(', ', $vals) .') ';
        return ($sql_result);
    }

    /**
     * Prepare and return SQL columns for database query
     *
     * @param string|string[] $columns
     * @return string
     */

    protected function prepareColumns(array|string $columns): string
    {
        // Default tables in one line
        $columns_in_line = "";
        // trying to convert any string to tables array
        if (is_string($columns)) {
            //$columns = preg_replace("/[\t ]+/", "", $columns);
            $columns = str_contains($columns, ",") ? explode(",", $columns) : array($columns);
        }
        foreach ($columns as $column) {
            // protect tables with limiter
            $column = $this->protectIdentifiers($column);
            // Now lets collect all in one line
            $columns_in_line .= trim($column) . ", ";
        }
        // Give the final results and remove unnecessary comma
        return (rtrim($columns_in_line, ", "));
    }

    /**
     * This is helping tool to output database errors friendly looking
     *
     * @param PDOException $e
     * @return string
     */
    #[Pure] protected function debug_error(PDOException $e): string
    {
        $output = "<b>System:</b> $this->error.<br>";
        if (debug == 'Development') {

            if(is_array($e->errorInfo) && count($e->errorInfo) >= 3) {
                $output .= "$this->db_driver Database Error<br>";
                $error_info = $e->errorInfo;
                $output .= "<br><b>SQLSTATE error code:</b> $error_info[0]";
                $output .= "<br><b>$this->db_driver error code:</b> $error_info[1]";
                $output .= "<br><b>$this->db_driver said:</b> $error_info[2]";
                $output .= "<br>";
            } else {
                $output .= "<br><b>PDO said:</b> {$e->getMessage()}";
            }

            if (!empty($this->last_query)) {
                $output .= "<br><br><b>Last query:</b> $this->last_query";
            }

        }
        return ($output);
    }

    /**
     * Set table to apply query
     * @param string $table
     * @return $this
     */
    public function table(string $table): self
    {
        $this->table = $table;
        $this->confirmTables($this->table);
        return $this;
    }

    /**
     * Getting all tables in the current database
     * @return array if no tables then false or return array of table names
     */
    abstract protected function getTables(): array;

    /**
     * Store all tables in the current database into the current instance
     */
    protected function setTables(): void
    {
        $this->db_tables = $this->getTables();

        if (count($this->db_tables) === 0) {
            trigger_error("Could not be fined any tables in database '<i>$this->db_name</i>'", E_USER_ERROR);
        }
    }

    /**
     * Confirm database table and make sure removing injections
     *
     * @param string|string[] $tables
     * @param bool $check to check if table exists or not check
     * @return string
     */
    protected function confirmTables(string|array $tables, bool $check = true): string
    {
        // Set the actual tables
        $this->setTables();
        // Default tables in one line
        $tables_inline = "";
        // trying to convert any string to tables array
        if (is_string($tables)) {
            $tables = str_contains($tables, ",") ? explode(",", $tables) : array($tables);
        }

        foreach ($tables as $table) {
            // protect tables with limiter
            $table = $this->protectIdentifiers($table);

            // if checking id on let's check if table exists
            if ($check && !in_array($table, $this->db_tables)) {
                trigger_error("Table <b>'$table'</b> doesn't exist.", E_USER_ERROR);
            }

            // Now lets collect all in one line
            $tables_inline .= "$table, ";
        }
        // Give the final results and remove unnecessary comma
        return rtrim($tables_inline, ", ");
    }

    /**
     * Prepare where clues
     */
    public function where(
        string $column,
        array|string $values,
        string $operator="=",
        bool $bind=true
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
     * This method is used to generate a SQL WHERE clause based on the provided parameters.
     * Here’s a brief explanation of its functionality:
     * The method accepts four parameters: $column (the column name), $values (the values to be compared with),
     * $operator (the comparison operator, default is “LIKE”), and $bind (a boolean indicating whether to bind the values,
     * default is true).
     * If $values is a string, it’s converted into an array.
     * The method then iterates over each value in the $values array and constructs the SQL condition based on the operator.
     * If the operator is “BETWEEN”, it expects the value to be in the format (val1|val2).
     * It then binds these values to placeholders if $bind is true.
     * If the operator is “IN”, it expects $value to be an array.
     * It then constructs an IN clause and binds each value to a placeholder if $bind is true.
     * For other operators, it simply binds the value to a placeholder if $bind is true.
     * If more than one value is provided, it constructs an OR condition combining all the conditions.
     * Finally, it appends the constructed condition to the where_clause property of the object and returns the object
     * itself for method chaining.
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
        string $operator = "LIKE",
        bool $bind = true
    ): self {
        if (is_string($values)) {
            $values = [$values];
        }

        $conditions = [];
        foreach ($values as $index => $value) {
            $value = is_string($value) ? trim($value) : $value;
            $place_holder = ":where_{$column}_$index";

            if (str_contains(strtoupper(trim($operator)), "BETWEEN")) {
                if (!preg_match("/^\(.+\|.+\)$/", $value)){
                    die ("<b>Syntax error: </b>between syntax error.");
                } else {
                    list($val1, $val2) = explode("|", ltrim(rtrim($value, ")"), "("));
                }

                if ($bind) {
                    $conditions[] = "{$this->protectIdentifiers($column)} $operator {$place_holder}_start AND {$place_holder}_end";
                    $this->bindings["{$place_holder}_start"] = $val1;
                    $this->bindings["{$place_holder}_end"] = $val2;
                } else {
                    $conditions[] = "{$this->protectIdentifiers($column)} $operator $val1 AND $val2";
                }
            } elseif (str_contains(strtoupper(trim($operator)), "IN")) {
                if(!is_array($value)) {
                    $value = [$value];
                }

                if ($bind) {
                    foreach ($value as $i => $v) {
                        $conditions[] = "{$this->protectIdentifiers($column)} IN ({$place_holder}_$i)";
                        $this->bindings["{$place_holder}_$i"] = $v;
                    }
                }
            } else {
                if ($bind) {
                    if (array_key_exists($place_holder, $this->bindings)){
                        trigger_error("`$column` column is already used in another WHERE group, this will cause it to be overwritten and will cause unexpected results, if you want to use same column name, use it in the same WHERE group", E_USER_WARNING);
                    }
                    $conditions[] = "{$this->protectIdentifiers($column)} $operator {$place_holder}";
                    $this->bindings[$place_holder] = $value;
                } else {
                    $conditions[] = "{$this->protectIdentifiers($column)} {$operator} {$value}";
                }
            }
        }

        // Join all conditions with OR
        if (count($conditions) > 1) {
            $this->where_clause .= "(" . implode(" OR ", $conditions) . ")";
        } else {
            // If there's only one condition, no need for parentheses
            $this->where_clause .= implode(" OR ", $conditions);
        }

        return $this;
    }

    /**
     * @param string $column
     * @param array|string $values
     * @param bool $bind
     * @return $this
     */
    public function where_starts(string $column, array|string $values, bool $bind=true): self
    {
        $this->where_group($column, "$values%", "LIKE", $bind);
        return $this;
    }

    /**
     * @param string $column
     * @param array|string $values
     * @param bool $bind
     * @return $this
     */
    public function and_where_starts(string $column, array|string $values, bool $bind=true): self
    {
        $this->and_where($column, "$values%", "LIKE", $bind);
        return $this;
    }

    /**
     * @param string $column
     * @param array|string $values
     * @param bool $bind
     * @return $this
     */
    public function or_where_starts(string $column, array|string $values, bool $bind=true): self
    {
        $this->or_where($column, "$values%", "LIKE", $bind);
        return $this;
    }

    /**
     * @param string $column
     * @param array|string $values
     * @param bool $bind
     * @return $this
     */
    public function where_has(string $column, array|string $values, bool $bind=true): self
    {
        $this->where_group($column, "%$values%", "LIKE", $bind);
        return $this;
    }

    /**
     * @param string $column
     * @param array|string $values
     * @param bool $bind
     * @return $this
     */
    public function and_where_has(string $column, array|string $values, bool $bind=true): self
    {
        $this->and_where($column, "%$values%", "LIKE", $bind);
        return $this;
    }

    /**
     * @param string $column
     * @param array|string $values
     * @param bool $bind
     * @return $this
     */
    public function or_where_has(string $column, array|string $values, bool $bind=true): self
    {
        $this->or_where($column, "%$values%", "LIKE", $bind);
        return $this;
    }


    /**
     * @param string $column
     * @param array|string $values
     * @param bool $bind
     * @return $this
     */
    public function where_ends(string $column, array|string $values, bool $bind=true): self
    {
        $this->where_group($column, "%$values", "LIKE", $bind);
        return $this;
    }

    /**
     * @param string $column
     * @param array|string $values
     * @param bool $bind
     * @return $this
     */
    public function and_where_ends(string $column, array|string $values, bool $bind=true): self
    {
        $this->and_where($column, "%$values", "LIKE", $bind);
        return $this;
    }

    /**
     * @param string $column
     * @param array|string $values
     * @param bool $bind
     * @return $this
     */
    public function or_where_ends(string $column, array|string $values, bool $bind=true): self
    {
        $this->or_where($column, "%$values", "LIKE", $bind);
        return $this;
    }

    /**
     * @param string $column
     * @param string $value1
     * @param string $value2
     * @param bool $bind
     * @return $this
     */
    public function where_between(string $column, string $value1, string $value2, bool $bind=true): self
    {
        $values = "($value1|$value2)";
        $this->where_group($column, $values, 'BETWEEN', $bind);
        return $this;
    }

    /**
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
     * @param string $value1
     * @param string $value2
     * @param bool $bind
     * @return $this
     */
    public function or_where_between(string $column, string $value1, string $value2, bool $bind=true): self
    {
        $values = "($value1|$value2)";
        $this->or_where($column, $values, 'BETWEEN', $bind);
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
    public function and_where(string $column, array|string $values, string $operator = "=", bool $bind=true): self
    {
        if ($this->where_clause === "")
            trigger_error("You can't use `" . __FUNCTION__ . "` function unless you use `where_group`", E_USER_ERROR);
        $this->where_clause .= " AND ";
        $this->where_group($column, $values, $operator, $bind);
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
        if ($this->where_clause === "") {
            trigger_error("You can't use `".__FUNCTION__."` function unless you use `where_group`", E_USER_ERROR);
        }
        $this->where_clause .= " OR ";
        $this->where_group($column, $values, $operator, $bind);
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
        $this->where_group($column, [$value], 'IN', $bind);
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
    public function or_where_in(string $column, array $value, bool $bind=true): self
    {
        $this->or_where($column, [$value], 'IN', $bind);
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
        $this->where_group($column, [$value], 'NOT IN', $bind);
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
     * @param string $column
     * @param array $value
     * @param bool $bind
     * @return $this
     */
    public function or_where_not_in(string $column, array $value, bool $bind=true): self
    {
        $this->or_where($column, [$value], 'NOT IN', $bind);
        return $this;
    }

    /**
     * Search for value in whole table
     *
     * query example: SELECT * FROM `f_exchange`.`member` WHERE (
     *  CONVERT(`id` USING utf8) LIKE '%m%'
     *  OR CONVERT(`full_name` USING utf8) LIKE '%m%'
     *  OR CONVERT(`email` USING utf8) LIKE '%m%'
     *  OR CONVERT(`phone` USING utf8) LIKE '%m%'
     * )
     * @param string|array $columns
     * @param string $value
     * @return self
     */
    public function search_in_table(string|array $columns, string $value): self
    {

        // trying to convert string $columns into array
        is_string($columns) && $columns = str_contains($columns, ",")
            ? explode(",", $columns)
            : array($columns);

        $i = 1;
        foreach ($columns as $column){
            $this->bindings[] = "%$value%";
            $this->where_clause .= ($i == 1 ? "" : " OR ") . $this->protectIdentifiers($column) . ' LIKE ?';
            $i++;
        }

        return $this;
    }


    /*public function where_group(
        string $column,
        array|string $values,
        string $operator="LIKE",
        bool $bind=true
    ): self
    {
        if (is_string($values)) {
            $values = [$values];
        }

        $condition = " ";
        $count = count($values);

        if ($count > 1) {
            $condition .= "(";
        }
        // iterating MySQL statements for each value
        foreach ($values as $index => $value) {

            $value = is_string($value) ? trim($value) : $value;
            $place_holder = ":where_{$column}_$index";
            $condition .= $this->protect_identifiers($column) . " $operator ";

            if (str_contains(strtoupper(trim($operator)), "BETWEEN")) {
                // Check if the between value looks like (val1|val2) to split it
                if (!preg_match("/^\(.+\|.+\)$/", $value)){
                    die ("<b>Syntax error: </b>between syntax error.");
                } else {
                    // Split and trim clean from the parentheses
                    list($val1, $val2) = explode("|", ltrim(rtrim($value, ")"), "("));
                }

                if ($bind) {
                    $condition .= "{$place_holder}_start AND {$place_holder}_end";
                    $this->bindings["{$place_holder}_start"] = $val1;
                    $this->bindings["{$place_holder}_end"] = $val2;
                } else {
                    $condition .= "$val1 AND $val2";
                }

            } elseif (str_contains(strtoupper(trim($operator)), "IN")) {

                if(!is_array($value)) {
                    $value = [$value];
                }

                $in_count = count($value);

                if ($bind) {
                    $condition .= "(";
                    foreach ($value as $i => $v) {
                        $condition .= "{$place_holder}_$i,";
                        $this->bindings["{$place_holder}_$i"] = $v;

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
                    $condition .= $value;
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
    }*/

}