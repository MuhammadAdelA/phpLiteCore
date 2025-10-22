<?php

declare(strict_types=1);

namespace PhpLiteCore\Database\Model;

use PhpLiteCore\Container\Container;
use PhpLiteCore\Database\QueryBuilder\BaseQueryBuilder;
use PhpLiteCore\Database\Model\Relations\HasMany;
use PhpLiteCore\Database\Model\Relations\HasOne;
use PhpLiteCore\Database\Model\Relations\BelongsTo;

/**
 * The Hybrid Base Model for the Active Record implementation.
 * Provides static methods for querying and instance methods for record manipulation.
 *
 * @method static BaseQueryBuilder where(string|callable|array $column, mixed $operator = null, mixed $value = null)
 * @method static BaseQueryBuilder orderBy(string $column, string $direction = 'ASC')
 * @method static BaseQueryBuilder limit(int $limit)
 * @method static array get(array $columns = ['*'])
 * @method static static|null first(array $columns = ['*'])
 * @method static string|false insert(array $data)
 * @method static int update(array $data)
 * @method static int delete()
 * @method static array paginate(int $perPage, int $currentPage = 1)
 * @method static int count()
 */
abstract class BaseModel
{
    /** * @var Container The static container instance, providing access to services like the database.
     */
    protected static Container $container;

    /** * @var string The table associated with the model. Can be overridden in child classes.
     */
    protected string $table;

    /** * @var array The model's current attributes.
     */
    protected array $attributes = [];

    /** * @var array The model's original attributes, loaded from the database. Used to track changes for updates.
     */
    protected array $original = [];

    /**
     * Sets the container instance for all models to use.
     * This should be called once during the application bootstrap.
     * @param Container $container
     */
    public static function setContainer(Container $container): void
    {
        static::$container = $container;
    }

    /**
     * BaseModel constructor.
     * @param array $attributes Initial attributes to fill the model with.
     */
    public function __construct(array $attributes = [])
    {
        $this->setTable();
        $this->fill($attributes);
        // Set the original state after initial fill, typically from a DB record.
        $this->original = $this->attributes;
    }

    /**
     * Fills the model with an array of attributes.
     * @param array $attributes
     */
    public function fill(array $attributes): void
    {
        foreach ($attributes as $key => $value) {
            $this->setAttribute($key, $value);
        }
    }

    /**
     * A static helper to get all records for the model.
     * This is a shortcut for query()->get().
     *
     * @return array An array of model instances.
     */
    public static function all(): array
    {
        // This is an explicit helper method that uses the query builder.
        return static::query()->get();
    }

    /**
     * A static helper to find a record by its primary key.
     *
     * @param int|string $id The primary key value.
     * @return static|null An instance of the model or null if not found.
     */
    public static function find(int|string $id): ?static
    {
        return static::query()->where('id', '=', $id)->first();
    }

    /**
     * Saves the model's state to the database (handles both insert and update).
     * @return bool True on success, false on failure.
     */
    public function save(): bool
    {
        $builder = static::query();

        // If 'id' exists in the original attributes, we assume it's an update.
        if (isset($this->original['id']) && !empty($this->original['id'])) {
            $dirty = $this->getDirtyAttributes();
            // If there are no changes, no need to run a query.
            if (empty($dirty)) {
                return true;
            }
            // Execute an update and return true if one or more rows were affected.
            return $builder->where('id', '=', $this->original['id'])->update($dirty) > 0;
        } else {
            // Otherwise, it's a new record (insert).
            $newId = $builder->insert($this->attributes);

            if ($newId) {
                // Update the current model instance with its new ID.
                $this->id = (int)$newId;
                // Sync the original state to reflect the saved state.
                $this->original = $this->attributes;
                $this->original['id'] = $this->id;
                return true;
            }

            return false;
        }
    }

    /**
     * Sets a given attribute on the model.
     * @param string $key
     * @param mixed $value
     */
    public function setAttribute(string $key, mixed $value): void
    {
        $this->attributes[$key] = $value;
    }

    /**
     * Gets an attribute from the model.
     * @param string $key
     * @return mixed
     */
    public function getAttribute(string $key): mixed
    {
        return $this->attributes[$key] ?? null;
    }

    /**
     * Magic method to get attributes as properties.
     * @param string $key
     * @return mixed
     */
    public function __get(string $key)
    {
        return $this->getAttribute($key);
    }

    /**
     * Magic method to set attributes as properties.
     * @param string $key
     * @param mixed $value
     */
    public function __set(string $key, mixed $value)
    {
        $this->setAttribute($key, $value);
    }

    /**
     * Gets the attributes that have been changed since the model was last synced.
     * @return array
     */
    protected function getDirtyAttributes(): array
    {
        return array_diff_assoc($this->attributes, $this->original);
    }

    /**
     * Sets the table name automatically based on the model's class name.
     */
    protected function setTable(): void
    {
        if (empty($this->table)) {
            $className = substr(strrchr(static::class, "\\"), 1);
            // Converts "UserPost" to "user_posts"
            $tableName = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $className)) . 's';
            // Handles simple pluralization for words ending in 'y' like "Category"
            if (str_ends_with($tableName, 'ys')) {
                $tableName = substr($tableName, 0, -2) . 'ies';
            }
            $this->table = $tableName;
        }
    }

    /**
     * Get the table name for this model.
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * Begins a new query for this model.
     * @return BaseQueryBuilder
     */
    public static function query(): BaseQueryBuilder
    {
        $db = static::$container->get('db');
        return $db->queryBuilder()
            ->from((new static())->table)
            ->setModel(static::class);
    }

    /**
     * Handles dynamic static method calls by forwarding them to a new query builder instance.
     * This allows for calls like User::where(...)
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
    public static function __callStatic(string $method, array $arguments)
    {
        // Forward static calls (like 'where', 'orderBy', etc.) to a new query builder instance.
        return static::query()->{$method}(...$arguments);
    }

    /**
     * Helper: relation hasMany
     *
     * @param class-string $related
     * @param string|null $foreignKey Column on the related table referencing this model (default: snake_case(model)_id)
     * @param string $localKey Column on this model (default: 'id')
     */
    protected function hasMany(string $related, ?string $foreignKey = null, string $localKey = 'id'): HasMany
    {
        $relatedTable = (new $related())->getTable();
        $foreignKey ??= $this->inferForeignKey();

        // Get PDO from container
        /** @var \PhpLiteCore\Database\Database $db */
        $db = static::$container->get('db');

        return new HasMany($db->getPdo(), $this->table, $relatedTable, $localKey, $foreignKey, $this->inferRelationName());
    }

    /**
     * Helper: relation hasOne
     */
    protected function hasOne(string $related, ?string $foreignKey = null, string $localKey = 'id'): HasOne
    {
        $relatedTable = (new $related())->getTable();
        $foreignKey ??= $this->inferForeignKey();

        /** @var \PhpLiteCore\Database\Database $db */
        $db = static::$container->get('db');

        return new HasOne($db->getPdo(), $this->table, $relatedTable, $localKey, $foreignKey, $this->inferRelationName());
    }

    /**
     * Helper: relation belongsTo
     *
     * @param class-string $related
     * @param string|null $foreignKey Column on THIS model referencing owner's key (default: snake_case(related)_id)
     * @param string $ownerKey Column on the related model (default: 'id')
     */
    protected function belongsTo(string $related, ?string $foreignKey = null, string $ownerKey = 'id'): BelongsTo
    {
        $relatedTable = (new $related())->getTable();
        $foreignKey ??= $this->inferForeignKey($related);

        /** @var \PhpLiteCore\Database\Database $db */
        $db = static::$container->get('db');

        // Note: For belongsTo, localKey is the FK on parent; foreignKey is the owner key on related.
        return new BelongsTo($db->getPdo(), $this->table, $relatedTable, $foreignKey, $ownerKey, $this->inferRelationName());
    }

    /**
     * Infer FK name as snake_case(model)_id (or snake_case(related)_id when $class provided).
     */
    protected function inferForeignKey(?string $class = null): string
    {
        $name = $class ? $this->shortClass($class) : $this->shortClass(static::class);
        return $this->toSnakeCase($name) . '_id';
    }

    /**
     * Infer relation name from the calling method.
     */
    protected function inferRelationName(): string
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5);
        // Skip frames until we find one that's not inferRelationName, hasMany, hasOne, or belongsTo
        foreach ($trace as $frame) {
            $func = $frame['function'] ?? '';
            if ($func && !in_array($func, ['inferRelationName', 'hasMany', 'hasOne', 'belongsTo'], true)) {
                return (string)$func;
            }
        }
        return 'relation';
    }

    protected function shortClass(string $fqcn): string
    {
        $parts = explode('\\', $fqcn);
        return end($parts) ?: $fqcn;
    }

    protected function toSnakeCase(string $name): string
    {
        $name = preg_replace('/(?<!^)[A-Z]/', '_$0', $name);
        return strtolower((string)$name);
    }
}