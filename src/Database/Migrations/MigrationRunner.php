<?php
declare(strict_types=1);

namespace PhpLiteCore\Database\Migrations;

use PhpLiteCore\Database\Database;
use PDO;

final class MigrationRunner
{
    public function __construct(private readonly Database $db)
    {
        $this->ensureMigrationsTable();
    }

    private function ensureMigrationsTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS `schema_migrations` (
            `version` VARCHAR(255) PRIMARY KEY,
            `applied_at` DATETIME NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $this->db->raw($sql);
    }

    /**
     * Apply pending migrations and return array of versions applied.
     *
     * @param string $migrationsPath
     * @return string[]
     */
    public function migrate(string $migrationsPath): array
    {
        $applied = [];

        $files = glob(rtrim($migrationsPath, '/\\') . DIRECTORY_SEPARATOR . '*.php') ?: [];
        sort($files, SORT_STRING);

        $appliedVersions = $this->getAppliedVersions();

        foreach ($files as $file) {
            $version = basename($file, '.php');
            if (isset($appliedVersions[$version])) {
                continue;
            }

            $migration = $this->loadMigration($file);
            $this->db->beginTransaction();
            try {
                $migration->up();
                $this->recordVersion($version);
                $this->db->commit();
                $applied[] = $version;
            } catch (\Throwable $e) {
                $this->db->rollBack();
                throw $e;
            }
        }

        return $applied;
    }

    /**
     * Roll back the latest migration; return version string or null if none.
     */
    public function rollback(string $migrationsPath): ?string
    {
        $last = $this->getLastAppliedVersion();
        if ($last === null) {
            return null;
        }

        $file = rtrim($migrationsPath, '/\\') . DIRECTORY_SEPARATOR . $last . '.php';
        if (!is_file($file)) {
            // If file missing, still remove from table to allow progress
            $this->removeVersion($last);
            return $last;
        }

        $migration = $this->loadMigration($file);

        $this->db->beginTransaction();
        try {
            $migration->down();
            $this->removeVersion($last);
            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }

        return $last;
    }

    private function loadMigration(string $file): Migration
    {
        // Provide $db to the included file scope
        $db = $this->db;
        $loaded = require $file;

        if (!$loaded instanceof Migration) {
            throw new \RuntimeException("Migration file must return an instance of " . Migration::class . " ($file)");
        }
        return $loaded;
    }

    /**
     * @return array<string, string> version => applied_at
     */
    private function getAppliedVersions(): array
    {
        $stmt = $this->db->raw("SELECT version, applied_at FROM `schema_migrations` ORDER BY version ASC");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $map = [];
        foreach ($rows as $row) {
            $map[$row['version']] = $row['applied_at'];
        }
        return $map;
    }

    private function getLastAppliedVersion(): ?string
    {
        $stmt = $this->db->raw("SELECT version FROM `schema_migrations` ORDER BY applied_at DESC, version DESC LIMIT 1");
        $ver = $stmt->fetchColumn(0);
        return $ver !== false ? (string)$ver : null;
    }

    private function recordVersion(string $version): void
    {
        $sql = "INSERT INTO `schema_migrations` (`version`, `applied_at`) VALUES (?, NOW())";
        $this->db->raw($sql, [$version]);
    }

    private function removeVersion(string $version): void
    {
        $sql = "DELETE FROM `schema_migrations` WHERE `version` = ?";
        $this->db->raw($sql, [$version]);
    }
}
