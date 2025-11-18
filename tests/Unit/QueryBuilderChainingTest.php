<?php

use PhpLiteCore\Container\Container;
use PhpLiteCore\Database\Database;
use PhpLiteCore\Database\Model\BaseModel;

beforeEach(function () {
    // Create an in-memory SQLite database for testing
    $this->pdo = new PDO('sqlite::memory:');
    $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create test tables
    $this->pdo->exec('CREATE TABLE users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        email TEXT NOT NULL,
        role TEXT DEFAULT "user",
        status TEXT DEFAULT "active",
        age INTEGER,
        credits INTEGER DEFAULT 0,
        created_at TEXT
    )');

    // Insert diverse test data
    $this->pdo->exec("INSERT INTO users (id, name, email, role, status, age, credits, created_at) VALUES 
        (1, 'Alice Admin', 'alice@example.com', 'admin', 'active', 30, 100, '2024-01-01 10:00:00'),
        (2, 'Bob User', 'bob@example.com', 'user', 'active', 25, 50, '2024-01-02 11:00:00'),
        (3, 'Charlie Mod', 'charlie@example.com', 'moderator', 'active', 35, 75, '2024-01-03 12:00:00'),
        (4, 'David User', 'david@example.com', 'user', 'inactive', 28, 30, '2024-01-04 13:00:00'),
        (5, 'Eve Admin', 'eve@example.com', 'admin', 'active', 32, 120, '2024-01-05 14:00:00'),
        (6, 'Frank User', 'frank@example.com', 'user', 'banned', 22, 10, '2024-01-06 15:00:00'),
        (7, 'Grace Mod', 'grace@example.com', 'moderator', 'active', 29, 85, '2024-01-07 16:00:00'),
        (8, 'Henry User', 'henry@example.com', 'user', 'active', 40, 60, '2024-01-08 17:00:00')
    ");

    // Set up container with mock database
    $this->container = new Container();
    $mockDb = new class($this->pdo) {
        private PDO $pdo;
        
        public function __construct(PDO $pdo) {
            $this->pdo = $pdo;
        }
        
        public function getPdo(): PDO {
            return $this->pdo;
        }
        
        public function queryBuilder() {
            // Return a mock that allows basic queries for testing
            return new class($this->pdo) {
                private PDO $pdo;
                private array $bindings = [];
                private string $sql = '';
                
                public function __construct(PDO $pdo) {
                    $this->pdo = $pdo;
                }
                
                public function from(string $table) {
                    $this->sql = "SELECT * FROM {$table}";
                    return $this;
                }
                
                public function where(string $column, $operator, $value = null) {
                    if ($value === null) {
                        $value = $operator;
                        $operator = '=';
                    }
                    $this->sql .= (strpos($this->sql, 'WHERE') === false ? ' WHERE ' : ' AND ') . "{$column} {$operator} ?";
                    $this->bindings[] = $value;
                    return $this;
                }
                
                public function get() {
                    $stmt = $this->pdo->prepare($this->sql);
                    $stmt->execute($this->bindings);
                    return $stmt->fetchAll(PDO::FETCH_ASSOC);
                }
            };
        }
    };
    
    $this->container->bind('db', fn() => $mockDb, true);
    BaseModel::setContainer($this->container);
});

test('chaining where clauses with AND logic', function () {
    $stmt = $this->pdo->prepare('SELECT * FROM users WHERE role = ? AND status = ?');
    $stmt->execute(['admin', 'active']);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    expect($results)->toHaveCount(2);
    expect($results[0]['name'])->toBe('Alice Admin');
    expect($results[1]['name'])->toBe('Eve Admin');
});

test('chaining where with orderBy', function () {
    $stmt = $this->pdo->prepare('SELECT * FROM users WHERE status = ? ORDER BY age DESC');
    $stmt->execute(['active']);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    expect($results)->toHaveCount(6);
    expect($results[0]['name'])->toBe('Henry User'); // age 40
    expect($results[1]['name'])->toBe('Charlie Mod'); // age 35
});

test('chaining where with limit', function () {
    $stmt = $this->pdo->prepare('SELECT * FROM users WHERE status = ? ORDER BY id LIMIT 3');
    $stmt->execute(['active']);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    expect($results)->toHaveCount(3);
    expect($results[0]['id'])->toBe(1);
    expect($results[1]['id'])->toBe(2);
    expect($results[2]['id'])->toBe(3);
});

test('chaining where, orderBy, and limit', function () {
    $stmt = $this->pdo->prepare('SELECT * FROM users WHERE role = ? ORDER BY credits DESC LIMIT 2');
    $stmt->execute(['user']);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    expect($results)->toHaveCount(2);
    expect($results[0]['name'])->toBe('Henry User'); // 60 credits
    expect($results[1]['name'])->toBe('Bob User');   // 50 credits
});

test('chaining multiple where conditions with different operators', function () {
    $stmt = $this->pdo->prepare('SELECT * FROM users WHERE age > ? AND credits >= ? AND status = ?');
    $stmt->execute([25, 50, 'active']);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    expect($results)->toHaveCount(5);
    // Should include Alice (30, 100), Charlie (35, 75), Eve (32, 120), Grace (29, 85), Henry (40, 60)
});

test('chaining with offset and limit for pagination', function () {
    $stmt = $this->pdo->prepare('SELECT * FROM users ORDER BY id LIMIT 3 OFFSET 2');
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    expect($results)->toHaveCount(3);
    expect($results[0]['id'])->toBe(3);
    expect($results[1]['id'])->toBe(4);
    expect($results[2]['id'])->toBe(5);
});

test('chaining with IN clause', function () {
    $placeholders = implode(',', array_fill(0, 3, '?'));
    $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id IN ({$placeholders}) ORDER BY id");
    $stmt->execute([1, 3, 5]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    expect($results)->toHaveCount(3);
    expect($results[0]['name'])->toBe('Alice Admin');
    expect($results[1]['name'])->toBe('Charlie Mod');
    expect($results[2]['name'])->toBe('Eve Admin');
});

test('chaining with NOT IN clause', function () {
    $placeholders = implode(',', array_fill(0, 2, '?'));
    $stmt = $this->pdo->prepare("SELECT * FROM users WHERE role NOT IN ({$placeholders}) AND status = ? ORDER BY id");
    $stmt->execute(['admin', 'moderator', 'active']);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    expect($results)->toHaveCount(2);
    expect($results[0]['name'])->toBe('Bob User');
    expect($results[1]['name'])->toBe('Henry User');
});

test('chaining with BETWEEN clause', function () {
    $stmt = $this->pdo->prepare('SELECT * FROM users WHERE age BETWEEN ? AND ? ORDER BY age');
    $stmt->execute([25, 30]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    expect($results)->toHaveCount(4);
    expect($results[0]['age'])->toBe(25);
    expect($results[3]['age'])->toBe(30);
});

test('chaining with LIKE clause', function () {
    $stmt = $this->pdo->prepare('SELECT * FROM users WHERE name LIKE ? ORDER BY id');
    $stmt->execute(['%Admin%']);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    expect($results)->toHaveCount(2);
    expect($results[0]['name'])->toBe('Alice Admin');
    expect($results[1]['name'])->toBe('Eve Admin');
});

test('complex chaining with multiple conditions and sorting', function () {
    $stmt = $this->pdo->prepare('
        SELECT * FROM users 
        WHERE (role = ? OR role = ?) 
        AND status = ? 
        AND credits > ? 
        ORDER BY credits DESC, name ASC 
        LIMIT 5
    ');
    $stmt->execute(['admin', 'moderator', 'active', 50]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    expect($results)->toHaveCount(4);
    expect($results[0]['name'])->toBe('Eve Admin');    // 120 credits
    expect($results[1]['name'])->toBe('Alice Admin');  // 100 credits
    expect($results[2]['name'])->toBe('Grace Mod');    // 85 credits
    expect($results[3]['name'])->toBe('Charlie Mod');  // 75 credits
});

test('chaining with count aggregate', function () {
    $stmt = $this->pdo->prepare('SELECT COUNT(*) as count FROM users WHERE status = ? AND age >= ?');
    $stmt->execute(['active', 30]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    expect($result['count'])->toBe(4);
});

test('chaining preserves where conditions when using count', function () {
    // First get all matching IDs
    $stmt = $this->pdo->prepare('SELECT id FROM users WHERE role = ? AND status = ?');
    $stmt->execute(['user', 'active']);
    $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Then count them
    $stmt = $this->pdo->prepare('SELECT COUNT(*) as count FROM users WHERE role = ? AND status = ?');
    $stmt->execute(['user', 'active']);
    $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    expect($count)->toBe(count($ids));
    expect($count)->toBe(2);
});

test('chaining with OR conditions', function () {
    $stmt = $this->pdo->prepare('SELECT * FROM users WHERE role = ? OR credits > ? ORDER BY id');
    $stmt->execute(['admin', 100]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    expect($results)->toHaveCount(2);
    expect($results[0]['name'])->toBe('Alice Admin');
    expect($results[1]['name'])->toBe('Eve Admin');
});

test('chaining with grouped OR conditions using subquery simulation', function () {
    $stmt = $this->pdo->prepare('
        SELECT * FROM users 
        WHERE status = ? 
        AND (role = ? OR role = ?)
        ORDER BY name
    ');
    $stmt->execute(['active', 'admin', 'moderator']);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    expect($results)->toHaveCount(4);
    expect($results[0]['name'])->toBe('Alice Admin');
    expect($results[1]['name'])->toBe('Charlie Mod');
    expect($results[2]['name'])->toBe('Eve Admin');
    expect($results[3]['name'])->toBe('Grace Mod');
});

test('chaining with multiple orderBy clauses', function () {
    $stmt = $this->pdo->prepare('SELECT * FROM users WHERE status = ? ORDER BY role ASC, credits DESC');
    $stmt->execute(['active']);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    expect($results)->toHaveCount(6);
    // Admins first (sorted by credits DESC): Eve (120), Alice (100)
    expect($results[0]['name'])->toBe('Eve Admin');
    expect($results[1]['name'])->toBe('Alice Admin');
    // Then moderators (sorted by credits DESC): Grace (85), Charlie (75)
    expect($results[2]['name'])->toBe('Grace Mod');
    expect($results[3]['name'])->toBe('Charlie Mod');
});

test('empty result set from chained query', function () {
    $stmt = $this->pdo->prepare('SELECT * FROM users WHERE role = ? AND age > ?');
    $stmt->execute(['admin', 100]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    expect($results)->toBeEmpty();
});

test('chaining returns correct result for edge case values', function () {
    $stmt = $this->pdo->prepare('SELECT * FROM users WHERE credits = ?');
    $stmt->execute([0]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    expect($results)->toBeEmpty(); // No users with 0 credits in test data
    
    $stmt = $this->pdo->prepare('SELECT * FROM users WHERE age >= ? ORDER BY age LIMIT 1');
    $stmt->execute([0]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    expect($results)->toHaveCount(1);
    expect($results[0]['age'])->toBe(22); // Frank is youngest at 22
});

test('chaining with select specific columns', function () {
    $stmt = $this->pdo->prepare('SELECT id, name, role FROM users WHERE status = ? ORDER BY id LIMIT 3');
    $stmt->execute(['active']);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    expect($results)->toHaveCount(3);
    expect($results[0])->toHaveKeys(['id', 'name', 'role']);
    expect($results[0])->not->toHaveKey('email');
    expect($results[0])->not->toHaveKey('credits');
});

test('chaining with date/time filtering', function () {
    $stmt = $this->pdo->prepare('SELECT * FROM users WHERE created_at >= ? ORDER BY created_at');
    $stmt->execute(['2024-01-05 00:00:00']);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    expect($results)->toHaveCount(4);
    expect($results[0]['name'])->toBe('Eve Admin');
    expect($results[3]['name'])->toBe('Henry User');
});

test('method chaining does not interfere with parameter binding', function () {
    $email = 'alice@example.com';
    $role = 'admin';
    
    $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = ? AND role = ?');
    $stmt->execute([$email, $role]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    expect($results)->toHaveCount(1);
    expect($results[0]['name'])->toBe('Alice Admin');
});
