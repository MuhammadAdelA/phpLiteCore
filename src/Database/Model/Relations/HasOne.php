<?php
declare(strict_types=1);

namespace PhpLiteCore\Database\Model\Relations;

use PDO;

final class HasOne extends Relation
{
    public function eagerLoad(array &$parents): void
    {
        if (empty($parents)) {
            return;
        }

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
                $this->attach($p, null);
            }
            return;
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = "SELECT * FROM {$this->relatedTable} WHERE {$this->foreignKey} IN ({$placeholders})";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($ids);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Keep the first match per foreign key
        $firstByFk = [];
        foreach ($rows as $row) {
            $fk = $row[$this->foreignKey] ?? null;
            if ($fk === null) continue;
            if (!array_key_exists($fk, $firstByFk)) {
                $firstByFk[$fk] = $row;
            }
        }

        foreach ($parents as &$p) {
            $id = $this->getValue($p, $this->localKey);
            $this->attach($p, $firstByFk[$id] ?? null);
        }
    }
}
