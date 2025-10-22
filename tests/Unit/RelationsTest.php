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
        email TEXT NOT NULL
    )');

    $this->pdo->exec('CREATE TABLE posts (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        title TEXT NOT NULL,
        content TEXT
    )');

    $this->pdo->exec('CREATE TABLE profiles (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        bio TEXT
    )');

    // Insert test data
    $this->pdo->exec("INSERT INTO users (id, name, email) VALUES 
        (1, 'Alice', 'alice@example.com'),
        (2, 'Bob', 'bob@example.com'),
        (3, 'Charlie', 'charlie@example.com')
    ");

    $this->pdo->exec("INSERT INTO posts (id, user_id, title, content) VALUES 
        (1, 1, 'First Post', 'Content 1'),
        (2, 1, 'Second Post', 'Content 2'),
        (3, 2, 'Bob Post', 'Content 3')
    ");

    $this->pdo->exec("INSERT INTO profiles (id, user_id, bio) VALUES 
        (1, 1, 'Alice bio'),
        (2, 2, 'Bob bio')
    ");

    // Set up container with mock database
    $this->container = new Container();
    $this->mockDb = new class($this->pdo) {
        private PDO $pdo;
        
        public function __construct(PDO $pdo) {
            $this->pdo = $pdo;
        }
        
        public function getPdo(): PDO {
            return $this->pdo;
        }
    };
    $this->container->bind('db', fn() => $this->mockDb, true);
    
    BaseModel::setContainer($this->container);
});

test('hasMany relation eager loads correctly', function () {
    // Define test models with explicit table names
    $userClass = new class extends BaseModel {
        protected string $table = 'users';
        
        public function posts() {
            $postClass = new class extends BaseModel {
                protected string $table = 'posts';
            };
            // Explicitly provide foreign key to avoid issues with anonymous classes
            return $this->hasMany(get_class($postClass), 'user_id', 'id');
        }
    };

    // Query users
    $stmt = $this->pdo->query('SELECT * FROM users');
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Manually invoke eager loading
    \PhpLiteCore\Database\Model\EagerLoader::load(
        $this->pdo,
        get_class($userClass),
        $users,
        ['posts']
    );

    // Verify results
    expect($users)->toHaveCount(3);
    expect($users[0]['posts'])->toHaveCount(2);
    expect($users[1]['posts'])->toHaveCount(1);
    expect($users[2]['posts'])->toHaveCount(0);
    expect($users[0]['posts'][0]['title'])->toBe('First Post');
});

test('hasOne relation eager loads correctly', function () {
    $userClass = new class extends BaseModel {
        protected string $table = 'users';
        
        public function profile() {
            $profileClass = new class extends BaseModel {
                protected string $table = 'profiles';
            };
            return $this->hasOne(get_class($profileClass), 'user_id', 'id');
        }
    };

    $stmt = $this->pdo->query('SELECT * FROM users');
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    \PhpLiteCore\Database\Model\EagerLoader::load(
        $this->pdo,
        get_class($userClass),
        $users,
        ['profile']
    );

    expect($users)->toHaveCount(3);
    expect($users[0]['profile'])->toBeArray();
    expect($users[0]['profile']['bio'])->toBe('Alice bio');
    expect($users[1]['profile'])->toBeArray();
    expect($users[1]['profile']['bio'])->toBe('Bob bio');
    expect($users[2]['profile'])->toBeNull();
});

test('belongsTo relation eager loads correctly', function () {
    $postClass = new class extends BaseModel {
        protected string $table = 'posts';
        
        public function author() {
            $userClass = new class extends BaseModel {
                protected string $table = 'users';
            };
            return $this->belongsTo(get_class($userClass), 'user_id', 'id');
        }
    };

    $stmt = $this->pdo->query('SELECT * FROM posts');
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    \PhpLiteCore\Database\Model\EagerLoader::load(
        $this->pdo,
        get_class($postClass),
        $posts,
        ['author']
    );

    expect($posts)->toHaveCount(3);
    expect($posts[0]['author'])->toBeArray();
    expect($posts[0]['author']['name'])->toBe('Alice');
    expect($posts[1]['author'])->toBeArray();
    expect($posts[1]['author']['name'])->toBe('Alice');
    expect($posts[2]['author'])->toBeArray();
    expect($posts[2]['author']['name'])->toBe('Bob');
});

test('relation helper methods work with model instances', function () {
    // Test model instance methods
    $userClass = new class extends BaseModel {
        protected string $table = 'users';
        
        public function posts() {
            $postClass = new class extends BaseModel {
                protected string $table = 'posts';
            };
            return $this->hasMany(get_class($postClass), 'user_id', 'id');
        }
    };

    $user = new $userClass();
    $relation = $user->posts();
    
    expect($relation)->toBeInstanceOf(\PhpLiteCore\Database\Model\Relations\HasMany::class);
    expect($relation->name())->toBe('posts');
});

test('eager loading works with empty results', function () {
    $userClass = new class extends BaseModel {
        protected string $table = 'users';
        
        public function posts() {
            $postClass = new class extends BaseModel {
                protected string $table = 'posts';
            };
            return $this->hasMany(get_class($postClass));
        }
    };

    $users = [];

    // Should not throw error with empty array
    \PhpLiteCore\Database\Model\EagerLoader::load(
        $this->pdo,
        get_class($userClass),
        $users,
        ['posts']
    );

    expect($users)->toBeEmpty();
});

test('eager loading handles missing relation methods gracefully', function () {
    $userClass = new class extends BaseModel {
        protected string $table = 'users';
    };

    $stmt = $this->pdo->query('SELECT * FROM users LIMIT 1');
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Should not throw error when relation doesn't exist
    \PhpLiteCore\Database\Model\EagerLoader::load(
        $this->pdo,
        get_class($userClass),
        $users,
        ['nonexistent']
    );

    expect($users)->toHaveCount(1);
    expect($users[0])->not->toHaveKey('nonexistent');
});
