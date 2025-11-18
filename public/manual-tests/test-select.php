<?php

use PhpLiteCore\Database\Database;

$config = [
    'host' => $_ENV['MYSQL_DB_HOST'] ?: 'localhost',
    'port' => $_ENV['MYSQL_DB_PORT'] ?: 3306,
    'database' => $_ENV['MYSQL_DB_NAME'],
    'username' => $_ENV['MYSQL_DB_USER'],
    'password' => $_ENV['MYSQL_DB_PASS'],
    'charset' => $_ENV['MYSQL_DB_CHAR'] ?: 'utf8mb4',
];
$db = new Database($config);
$builder = $db->table('users')
    ->whereHas('email', '@example.com')
    ->orderBy('created_at', 'DESC')
    ->limit(10);

// الناتج:
// SQL: SELECT * FROM users WHERE (status = ? AND email LIKE ?) OR (status = ? AND name = ?)
// Bindings: [1, '%@example.com%', 0, 'Carol Lee']

$sql = $builder->toSql();        // نص الاستعلام
$bindings = $builder->getBindings();  // القيم المرتبطة

$stmt = $db->raw($sql, $bindings);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<pre>";
echo $sql;
echo "<br><br>";
var_dump($results);
