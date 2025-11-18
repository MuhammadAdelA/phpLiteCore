<?php

declare(strict_types=1);

namespace PhpLiteCore\Database\Model\Relations;

use PDO;

final class BelongsTo extends Relation
{
    /**
     * For belongsTo:
     * - The parent has the foreign key (e.g., user_id).
     * - The related table has the owner key (e.g., id).
     * Here: localKey = foreignKey on parent; foreignKey = ownerKey on related.
     */
    public function eagerLoad(array &$parents): void
    {
        if (empty($parents)) {
            return;
        }

        // 1) Collect FK values from parents (the column on the parent that points to related owner key)
        $fks = [];
        foreach ($parents as $p) {
            $fk = $this->getValue($p, $this->localKey); // localKey is the FK on parent (e.g., user_id)
            if ($fk !== null) {
                $fks[] = $fk;
            }
        }
        $fks = array_values(array_unique($fks));
        if (empty($fks)) {
            foreach ($parents as &$p) {
                $this->attach($p, null);
            }

            return;
        }

        // 2) Fetch related owners
        $placeholders = implode(',', array_fill(0, count($fks), '?'));
        $sql = "SELECT * FROM {$this->relatedTable} WHERE {$this->foreignKey} IN ({$placeholders})";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($fks);
        $owners = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 3) Index by the owner key (foreignKey property)
        $byOwner = [];
        foreach ($owners as $row) {
            $key = $row[$this->foreignKey] ?? null; // owner key on related, usually 'id'
            if ($key !== null) {
                $byOwner[$key] = $row;
            }
        }

        // 4) Attach the single owner to each parent
        foreach ($parents as &$p) {
            $fk = $this->getValue($p, $this->localKey);
            $this->attach($p, $byOwner[$fk] ?? null);
        }
    }
}
