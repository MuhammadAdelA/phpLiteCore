<?php

declare(strict_types=1);

namespace PhpLiteCore\Database\Model\Relations;

use PDO;

abstract class Relation
{
    public function __construct(
        protected PDO $pdo,
        protected string $parentTable,
        protected string $relatedTable,
        protected string $localKey,
        protected string $foreignKey,
        protected string $relationName
    ) {
    }

    public function name(): string
    {
        return $this->relationName;
    }

    abstract public function eagerLoad(array &$parents): void;

    /**
     * Attach a value to a parent model/record under the relation name.
     * Supports both objects and associative arrays.
     */
    protected function attach(mixed &$parent, mixed $value): void
    {
        if (is_object($parent)) {
            $parent->{$this->relationName} = $value;
        } else {
            $parent[$this->relationName] = $value;
        }
    }

    protected function getValue(mixed $row, string $key): mixed
    {
        if (is_object($row)) {
            return $row->{$key} ?? null;
        }

        return $row[$key] ?? null;
    }
}
