<?php
declare(strict_types=1);

namespace PhpLiteCore\Database\Model\Relations;

use PDO;

final class HasMany extends Relation
{
    public function eagerLoad(array &$parents): void
    {
        if (empty($parents)) {
            return;
        }

        // 1) Collect local keys from parents
        $ids = [];
        foreach ($parents as $p) {
            $val = $this->getValue($p, $this->localKey);
            if ($val !== null) {
                $ids[] = $val;
            }
        }
        $ids = array_values(array_unique($ids));
        if (empty($ids)) {
            foreach ($parents as &$p) {
                $this->attach($p, []);
            }
            return;
        }

        // 2) Fetch all related rows in a single query
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = "SELECT * FROM {$this->relatedTable} WHERE {$this->foreignKey} IN ({$placeholders})";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($ids);
        $children = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 3) Group by foreign key
        $byFk = [];
        foreach ($children as $row) {
            $fk = $row[$this->foreignKey] ?? null;
            if ($fk === null) continue;
            $byFk[$fk][] = $row;
        }

        // 4) Attach arrays of children to each parent
        foreach ($parents as &$p) {
            $id = $this->getValue($p, $this->localKey);
            $this->attach($p, $byFk[$id] ?? []);
        }
    }
}
