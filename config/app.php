<?php
declare(strict_types=1);

return [
    'name' => 'phpLiteCore',
    'version' => '1.0.0',
    'env' => $_ENV['APP_ENV'] ?? 'production',
    'debug' => $_ENV['APP_DEBUG'] ?? false,
    'timezone' => $_ENV['SYSTEM_TIMEZONE'] ?? 'UTC',
];
