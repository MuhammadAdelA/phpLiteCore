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
        try {
            $sql = "CREATE TABLE IF NOT EXISTS `schema_migrations` (
                `version` VARCHAR(255) PRIMARY KEY,
                `applied_at` DATETIME NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            $this->db->raw($sql);
        } catch (\PDOException $e) {
            // Table might already exist, ignore error
            if ($e->getCode() !== '42S01') {
                throw $e;
            }
        }
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
            
            try {
                // Note: DDL statements (CREATE, ALTER, DROP) cause implicit commit in MySQL
                // So transactions are not effective for schema changes
                $migration->up();
                $this->recordVersion($version);
                $applied[] = $version;
            } catch (\Throwable $e) {
                // If migration failed, we don't record it
                // User needs to fix the migration and try again
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

        try {
            // Note: DDL statements (CREATE, ALTER, DROP) cause implicit commit in MySQL
            // So transactions are not effective for schema changes
            $migration->down();
            $this->removeVersion($last);
        } catch (\Throwable $e) {
            // Rollback failed, but we still need to handle it
            // The version is not removed from the table, so user can try again
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
