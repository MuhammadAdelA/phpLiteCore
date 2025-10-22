<?php
declare(strict_types=1);

use PhpLiteCore\Database\Migrations\Migration;

/**
 * Migration: 20251022153729_AddUsersIndexes
 * Return an instance of an anonymous class extending Migration.
 */
return new class($db) extends Migration {
    public function up(): void
    {
        $this->db->raw("ALTER TABLE `users` ADD INDEX `idx_users_email` (`email`)");
        $this->db->raw("ALTER TABLE `users` ADD INDEX `idx_users_status` (`status`)");
        $this->db->raw("ALTER TABLE `users` ADD INDEX `idx_users_created_at` (`created_at`)");
    }

    public function down(): void
    {
        $this->db->raw("ALTER TABLE `users` DROP INDEX `idx_users_email`");
        $this->db->raw("ALTER TABLE `users` DROP INDEX `idx_users_status`");
        $this->db->raw("ALTER TABLE `users` DROP INDEX `idx_users_created_at`");
    }
};