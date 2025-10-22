<?php
declare(strict_types=1);

use PhpLiteCore\Database\Database;

return function (Database $db): void {
    $db->raw("INSERT INTO `users` (`name`, `email`, `status`, `created_at`) VALUES (?, ?, ?, NOW())", [
        'Test User', 'test@example.com', 1
    ]);
};
