<?php

declare(strict_types=1);

namespace PhpLiteCore\Database\Model;

use PhpLiteCore\Database\Database;
use PhpLiteCore\Database\QueryBuilder\BaseQueryBuilder;

/**
 * The Base Model for the Active Record implementation.
 *
 * @method static BaseQueryBuilder where(string|callable|array $column, mixed $operator = null, mixed $value = null)
 * @method static BaseQueryBuilder orderBy(string $column, string $direction = 'ASC')
 * @method static BaseQueryBuilder limit(int $limit)
 * @method static array get(array $columns = ['*'])
 * @method static array|null first(array $columns = ['*'])
 * @method static array|null find(int|string $id)
 * @method static int insert(array $data)
 * @method static int update(array $data)
 * @method static int delete()
 * @method static array paginate(int $perPage, int $currentPage = 1)
 * @method static int count()
 */

abstract class BaseModel
{
    /** @var ?Database The static database connection instance. */
    protected static ?Database $db = null;

    /** @var string The table associated with the model. */
    protected static string $table;

    /**
     * Set the database connection for all models.
     * @param Database $db
     */
    public static function setConnection(Database $db): void
    {
        self::$db = $db;
    }

    /**
     * Get the table name for the model.
     * Derives from the class name if not set explicitly.
     * @return string
     */
    protected static function getTable(): string
    {
        if (isset(static::$table)) {
            return static::$table;
        }

        // Converts "User" or "Post" to "users" or "posts"
        $className = substr(strrchr(static::class, "\\"), 1);
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $className)) . 's';
    }

    /**
     * Handle dynamic static method calls to the query builder.
     * This is the magic that allows for calls like User::where(...)
     *
     * @param string $method The method name (e.g., 'where', 'find', 'get').
     * @param array $arguments The arguments for the method.
     * @return mixed
     */
    public static function __callStatic(string $method, array $arguments)
    {
        // Create a new query builder instance and set its table.
        $builder = self::$db->queryBuilder()->from(static::getTable());

        // Call the intended method on the builder instance and return the result.
        return $builder->{$method}(...$arguments);
    }
}