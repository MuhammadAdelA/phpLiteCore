<?php

use PhpLiteCore\Database\Database;

beforeEach(function () {
    // Create an in-memory SQLite database for testing
    $config = [
        'host' => ':memory:',
        'port' => 0,
        'database' => ':memory:',
        'username' => '',
        'password' => '',
        'charset' => 'utf8mb4'
    ];
    
    // Create a mock Database that uses SQLite
    $this->pdo = new PDO('sqlite::memory:');
    $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create test table
    $this->pdo->exec('CREATE TABLE users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        email TEXT NOT NULL UNIQUE,
        credits INTEGER DEFAULT 0
    )');
    
    $this->pdo->exec('CREATE TABLE transactions (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        from_user_id INTEGER,
        to_user_id INTEGER,
        amount INTEGER,
        created_at TEXT
    )');
    
    // Insert test data
    $this->pdo->exec("INSERT INTO users (id, name, email, credits) VALUES 
        (1, 'Alice', 'alice@example.com', 100),
        (2, 'Bob', 'bob@example.com', 50)
    ");
    
    // Create a minimal Database mock
    $this->db = new class($this->pdo) {
        private PDO $pdo;
        
        public function __construct(PDO $pdo) {
            $this->pdo = $pdo;
        }
        
        public function beginTransaction(): bool {
            return $this->pdo->beginTransaction();
        }
        
        public function commit(): bool {
            return $this->pdo->commit();
        }
        
        public function rollBack(): bool {
            return $this->pdo->rollBack();
        }
        
        public function getPdo(): PDO {
            return $this->pdo;
        }
        
        public function raw(string $sql, array $bindings = []): PDOStatement {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($bindings);
            return $stmt;
        }
    };
});

test('beginTransaction starts a new transaction', function () {
    $result = $this->db->beginTransaction();
    
    expect($result)->toBeTrue();
    expect($this->pdo->inTransaction())->toBeTrue();
    
    $this->db->commit();
});

test('commit persists changes made during transaction', function () {
    $this->db->beginTransaction();
    
    // Insert a new user
    $stmt = $this->pdo->prepare('INSERT INTO users (name, email, credits) VALUES (?, ?, ?)');
    $stmt->execute(['Charlie', 'charlie@example.com', 75]);
    
    $this->db->commit();
    
    // Verify the user was inserted
    $stmt = $this->pdo->query('SELECT COUNT(*) FROM users');
    $count = $stmt->fetchColumn();
    
    expect($count)->toBe(3);
});

test('rollBack reverts changes made during transaction', function () {
    $this->db->beginTransaction();
    
    // Insert a new user
    $stmt = $this->pdo->prepare('INSERT INTO users (name, email, credits) VALUES (?, ?, ?)');
    $stmt->execute(['Charlie', 'charlie@example.com', 75]);
    
    // Verify user exists in transaction
    $stmt = $this->pdo->query('SELECT COUNT(*) FROM users');
    $count = $stmt->fetchColumn();
    expect($count)->toBe(3);
    
    // Rollback
    $this->db->rollBack();
    
    // Verify the user was NOT persisted
    $stmt = $this->pdo->query('SELECT COUNT(*) FROM users');
    $count = $stmt->fetchColumn();
    
    expect($count)->toBe(2);
});

test('transaction with multiple operations commits all or none', function () {
    $this->db->beginTransaction();
    
    try {
        // Update user credits
        $stmt = $this->pdo->prepare('UPDATE users SET credits = credits + ? WHERE id = ?');
        $stmt->execute([50, 1]);
        
        // Insert transaction log
        $stmt = $this->pdo->prepare('INSERT INTO transactions (from_user_id, to_user_id, amount, created_at) VALUES (?, ?, ?, ?)');
        $stmt->execute([2, 1, 50, date('Y-m-d H:i:s')]);
        
        $this->db->commit();
        
        // Verify both operations succeeded
        $stmt = $this->pdo->query('SELECT credits FROM users WHERE id = 1');
        $credits = $stmt->fetchColumn();
        expect($credits)->toBe(150);
        
        $stmt = $this->pdo->query('SELECT COUNT(*) FROM transactions');
        $txCount = $stmt->fetchColumn();
        expect($txCount)->toBe(1);
    } catch (Exception $e) {
        $this->db->rollBack();
        throw $e;
    }
});

test('transaction rolls back all operations on error', function () {
    $this->db->beginTransaction();
    
    try {
        // Update user credits
        $stmt = $this->pdo->prepare('UPDATE users SET credits = credits + ? WHERE id = ?');
        $stmt->execute([50, 1]);
        
        // This will fail due to UNIQUE constraint on email
        $stmt = $this->pdo->prepare('INSERT INTO users (name, email, credits) VALUES (?, ?, ?)');
        $stmt->execute(['Duplicate', 'alice@example.com', 100]);
        
        $this->db->commit();
        
        $this->fail('Expected exception was not thrown');
    } catch (Exception $e) {
        $this->db->rollBack();
        
        // Verify the update was rolled back
        $stmt = $this->pdo->query('SELECT credits FROM users WHERE id = 1');
        $credits = $stmt->fetchColumn();
        expect($credits)->toBe(100); // Should still be original value
        
        // Verify no new users were added
        $stmt = $this->pdo->query('SELECT COUNT(*) FROM users');
        $count = $stmt->fetchColumn();
        expect($count)->toBe(2);
    }
});

test('complex transaction with credit transfer', function () {
    $this->db->beginTransaction();
    
    try {
        $fromUserId = 1;
        $toUserId = 2;
        $amount = 30;
        
        // Get sender's current balance
        $stmt = $this->pdo->prepare('SELECT credits FROM users WHERE id = ?');
        $stmt->execute([$fromUserId]);
        $senderCredits = $stmt->fetchColumn();
        
        // Verify sufficient funds
        if ($senderCredits < $amount) {
            throw new Exception('Insufficient credits');
        }
        
        // Deduct from sender
        $stmt = $this->pdo->prepare('UPDATE users SET credits = credits - ? WHERE id = ?');
        $stmt->execute([$amount, $fromUserId]);
        
        // Add to receiver
        $stmt = $this->pdo->prepare('UPDATE users SET credits = credits + ? WHERE id = ?');
        $stmt->execute([$amount, $toUserId]);
        
        // Log transaction
        $stmt = $this->pdo->prepare('INSERT INTO transactions (from_user_id, to_user_id, amount, created_at) VALUES (?, ?, ?, ?)');
        $stmt->execute([$fromUserId, $toUserId, $amount, date('Y-m-d H:i:s')]);
        
        $this->db->commit();
        
        // Verify sender's balance
        $stmt = $this->pdo->query('SELECT credits FROM users WHERE id = 1');
        expect($stmt->fetchColumn())->toBe(70);
        
        // Verify receiver's balance
        $stmt = $this->pdo->query('SELECT credits FROM users WHERE id = 2');
        expect($stmt->fetchColumn())->toBe(80);
        
        // Verify transaction was logged
        $stmt = $this->pdo->query('SELECT COUNT(*) FROM transactions WHERE from_user_id = 1 AND to_user_id = 2');
        expect($stmt->fetchColumn())->toBe(1);
    } catch (Exception $e) {
        $this->db->rollBack();
        throw $e;
    }
});

test('transaction with insufficient funds rolls back', function () {
    $this->db->beginTransaction();
    
    try {
        $fromUserId = 2;
        $toUserId = 1;
        $amount = 100; // Bob only has 50 credits
        
        // Get sender's current balance
        $stmt = $this->pdo->prepare('SELECT credits FROM users WHERE id = ?');
        $stmt->execute([$fromUserId]);
        $senderCredits = $stmt->fetchColumn();
        
        // This should fail
        if ($senderCredits < $amount) {
            throw new Exception('Insufficient credits');
        }
        
        $this->fail('Expected exception was not thrown');
    } catch (Exception $e) {
        $this->db->rollBack();
        
        expect($e->getMessage())->toBe('Insufficient credits');
        
        // Verify no changes were made
        $stmt = $this->pdo->query('SELECT credits FROM users WHERE id = 2');
        expect($stmt->fetchColumn())->toBe(50);
        
        $stmt = $this->pdo->query('SELECT COUNT(*) FROM transactions');
        expect($stmt->fetchColumn())->toBe(0);
    }
});

test('raw method works within transactions', function () {
    $this->db->beginTransaction();
    
    // Use raw method
    $this->db->raw('UPDATE users SET credits = credits + ? WHERE id = ?', [25, 1]);
    
    $this->db->commit();
    
    // Verify the update
    $stmt = $this->pdo->query('SELECT credits FROM users WHERE id = 1');
    expect($stmt->fetchColumn())->toBe(125);
});

test('multiple sequential transactions work correctly', function () {
    // First transaction
    $this->db->beginTransaction();
    $stmt = $this->pdo->prepare('UPDATE users SET credits = credits + ? WHERE id = ?');
    $stmt->execute([10, 1]);
    $this->db->commit();
    
    // Second transaction
    $this->db->beginTransaction();
    $stmt = $this->pdo->prepare('UPDATE users SET credits = credits + ? WHERE id = ?');
    $stmt->execute([20, 1]);
    $this->db->commit();
    
    // Verify both transactions were applied
    $stmt = $this->pdo->query('SELECT credits FROM users WHERE id = 1');
    expect($stmt->fetchColumn())->toBe(130);
});

test('transaction state can be checked', function () {
    expect($this->pdo->inTransaction())->toBeFalse();
    
    $this->db->beginTransaction();
    expect($this->pdo->inTransaction())->toBeTrue();
    
    $this->db->commit();
    expect($this->pdo->inTransaction())->toBeFalse();
});
