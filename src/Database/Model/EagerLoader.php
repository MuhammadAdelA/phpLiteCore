<?php
declare(strict_types=1);

namespace PhpLiteCore\Database\Model;

use PDO;
use PhpLiteCore\Database\Model\Relations\BelongsTo;
use PhpLiteCore\Database\Model\Relations\HasMany;
use PhpLiteCore\Database\Model\Relations\HasOne;
use PhpLiteCore\Database\Model\Relations\Relation;

final class EagerLoader
{
    /**
     * @param PDO $pdo
     * @param class-string $modelClass
     * @param array $parents Array of parent rows (arrays or objects), passed by reference.
     * @param array $relations List of relation names defined as methods on the model class.
     */
    public static function load(PDO $pdo, string $modelClass, array &$parents, array $relations): void
    {
        if (empty($parents) || empty($relations)) {
            return;
        }

        $model = new $modelClass();

        foreach ($relations as $name) {
            if (!method_exists($model, $name)) {
                // Unknown relation; skip silently to avoid breaking existing flows.
                continue;
            }

            $relation = $model->{$name}();
            if (!$relation instanceof Relation) {
                // Relation method must return a Relation; skip otherwise.
                continue;
            }

            // Inject the PDO into the relation (in case the model didn't pass it)
            self::injectPdo($relation, $pdo);

            // Perform the batch eager load and attach to $parents
            $relation->eagerLoad($parents);
        }
    }

    private static function injectPdo(Relation $relation, PDO $pdo): void
    {
        // Use reflection to set the protected $pdo property if it's not set already
        $ref = new \ReflectionObject($relation);
        if ($ref->hasProperty('pdo')) {
            $prop = $ref->getProperty('pdo');
            $prop->setAccessible(true);
            if (!$prop->getValue($relation) instanceof PDO) {
                $prop->setValue($relation, $pdo);
            }
        }
    }
}
