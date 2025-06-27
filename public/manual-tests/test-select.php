<?php

use PhpLiteCore\Database\Database;

$config = [
    'host'          => $_ENV['MYSQL_DB_HOST'] ?: 'localhost',
    'port'          => $_ENV['MYSQL_DB_PORT'] ?: 3306,
    'database'      => $_ENV['MYSQL_DB_NAME'],
    'username'      => $_ENV['MYSQL_DB_USER'],
    'password'      => $_ENV['MYSQL_DB_PASS'],
    'charset'      => $_ENV['MYSQL_DB_CHAR'] ?: 'utf8mb4',
];
$db = new Database($config);
$builder = $db->table('users')
    ->where('id', '>', 1)
    ->where('id', '=', '1')
    ->orderBy('created_at', 'DESC')
    ->limit(10);

// شروط مجمّعة: (active AND email LIKE '%@example.com%')
//               OR (inactive AND name = 'Carol Lee')
$users = $db->table('users')
    ->whereGroup(function($q) {
        $q->where('status', '=', 1)
            ->where('email', 'LIKE', '%@example.com%')
            ->orWhere('status', '=', 0);
    })
    ->whereGroup(function($q) {
        $q->where('status', '=', 0)
            ->where('name', '=', 'Carol Lee');
    })
    ->toSql();
// الناتج:
// SQL: SELECT * FROM users WHERE (status = ? AND email LIKE ?) OR (status = ? AND name = ?)
// Bindings: [1, '%@example.com%', 0, 'Carol Lee']

$sql      = $builder->toSql();        // نص الاستعلام
$bindings = $builder->getBindings();  // القيم المرتبطة

$stmt = $db->raw($sql, $bindings);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo $users;
echo $db->rowCount($results);
