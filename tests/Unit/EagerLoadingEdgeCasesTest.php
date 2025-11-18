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
        content TEXT,
        views INTEGER DEFAULT 0
    )');

    $this->pdo->exec('CREATE TABLE profiles (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        bio TEXT,
        avatar TEXT
    )');
    
    $this->pdo->exec('CREATE TABLE comments (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        post_id INTEGER NOT NULL,
        user_id INTEGER NOT NULL,
        content TEXT NOT NULL
    )');

    // Insert comprehensive test data
    $this->pdo->exec("INSERT INTO users (id, name, email) VALUES 
        (1, 'Alice', 'alice@example.com'),
        (2, 'Bob', 'bob@example.com'),
        (3, 'Charlie', 'charlie@example.com'),
        (4, 'David', 'david@example.com')
    ");

    $this->pdo->exec("INSERT INTO posts (id, user_id, title, content, views) VALUES 
        (1, 1, 'First Post', 'Content 1', 100),
        (2, 1, 'Second Post', 'Content 2', 50),
        (3, 1, 'Third Post', 'Content 3', 75),
        (4, 2, 'Bob Post', 'Content 4', 200),
        (5, 2, 'Bob Second Post', 'Content 5', 150),
        (6, 3, 'Charlie Post', 'Content 6', 25)
    ");

    $this->pdo->exec("INSERT INTO profiles (id, user_id, bio, avatar) VALUES 
        (1, 1, 'Alice bio', 'alice.jpg'),
        (2, 2, 'Bob bio', 'bob.jpg'),
        (3, 3, 'Charlie bio', 'charlie.jpg')
    ");
    
    $this->pdo->exec("INSERT INTO comments (id, post_id, user_id, content) VALUES 
        (1, 1, 2, 'Great post!'),
        (2, 1, 3, 'Nice work'),
        (3, 1, 4, 'Interesting'),
        (4, 2, 2, 'Thanks for sharing'),
        (5, 4, 1, 'Good job Bob'),
        (6, 4, 3, 'Well written')
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

test('hasMany loads all related records correctly', function () {
    $userClass = new class extends BaseModel {
        protected string $table = 'users';
        
        public function posts() {
            $postClass = new class extends BaseModel {
                protected string $table = 'posts';
            };
            return $this->hasMany(get_class($postClass), 'user_id', 'id');
        }
    };

    $stmt = $this->pdo->query('SELECT * FROM users ORDER BY id');
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    \PhpLiteCore\Database\Model\EagerLoader::load(
        $this->pdo,
        get_class($userClass),
        $users,
        ['posts']
    );

    expect($users[0]['posts'])->toHaveCount(3); // Alice has 3 posts
    expect($users[1]['posts'])->toHaveCount(2); // Bob has 2 posts
    expect($users[2]['posts'])->toHaveCount(1); // Charlie has 1 post
    expect($users[3]['posts'])->toHaveCount(0); // David has 0 posts
});

test('hasOne loads single related record correctly', function () {
    $userClass = new class extends BaseModel {
        protected string $table = 'users';
        
        public function profile() {
            $profileClass = new class extends BaseModel {
                protected string $table = 'profiles';
            };
            return $this->hasOne(get_class($profileClass), 'user_id', 'id');
        }
    };

    $stmt = $this->pdo->query('SELECT * FROM users ORDER BY id');
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    \PhpLiteCore\Database\Model\EagerLoader::load(
        $this->pdo,
        get_class($userClass),
        $users,
        ['profile']
    );

    expect($users[0]['profile'])->toBeArray();
    expect($users[0]['profile']['bio'])->toBe('Alice bio');
    expect($users[1]['profile']['bio'])->toBe('Bob bio');
    expect($users[2]['profile']['bio'])->toBe('Charlie bio');
    expect($users[3]['profile'])->toBeNull(); // David has no profile
});

test('belongsTo loads parent record correctly', function () {
    $postClass = new class extends BaseModel {
        protected string $table = 'posts';
        
        public function author() {
            $userClass = new class extends BaseModel {
                protected string $table = 'users';
            };
            return $this->belongsTo(get_class($userClass), 'user_id', 'id');
        }
    };

    $stmt = $this->pdo->query('SELECT * FROM posts ORDER BY id LIMIT 4');
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    \PhpLiteCore\Database\Model\EagerLoader::load(
        $this->pdo,
        get_class($postClass),
        $posts,
        ['author']
    );

    expect($posts[0]['author']['name'])->toBe('Alice');
    expect($posts[1]['author']['name'])->toBe('Alice');
    expect($posts[2]['author']['name'])->toBe('Alice');
    expect($posts[3]['author']['name'])->toBe('Bob');
});

test('eager loading with multiple relationships', function () {
    $userClass = new class extends BaseModel {
        protected string $table = 'users';
        
        public function posts() {
            $postClass = new class extends BaseModel {
                protected string $table = 'posts';
            };
            return $this->hasMany(get_class($postClass), 'user_id', 'id');
        }
        
        public function profile() {
            $profileClass = new class extends BaseModel {
                protected string $table = 'profiles';
            };
            return $this->hasOne(get_class($profileClass), 'user_id', 'id');
        }
    };

    $stmt = $this->pdo->query('SELECT * FROM users WHERE id IN (1, 2)');
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    \PhpLiteCore\Database\Model\EagerLoader::load(
        $this->pdo,
        get_class($userClass),
        $users,
        ['posts', 'profile']
    );

    // Verify both relationships loaded
    expect($users[0]['posts'])->toHaveCount(3);
    expect($users[0]['profile'])->toBeArray();
    expect($users[0]['profile']['bio'])->toBe('Alice bio');
    
    expect($users[1]['posts'])->toHaveCount(2);
    expect($users[1]['profile'])->toBeArray();
    expect($users[1]['profile']['bio'])->toBe('Bob bio');
});

test('eager loading preserves all original record data', function () {
    $userClass = new class extends BaseModel {
        protected string $table = 'users';
        
        public function posts() {
            $postClass = new class extends BaseModel {
                protected string $table = 'posts';
            };
            return $this->hasMany(get_class($postClass), 'user_id', 'id');
        }
    };

    $stmt = $this->pdo->query('SELECT * FROM users WHERE id = 1');
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    \PhpLiteCore\Database\Model\EagerLoader::load(
        $this->pdo,
        get_class($userClass),
        $users,
        ['posts']
    );

    // Verify original data is intact
    expect($users[0]['id'])->toBe(1);
    expect($users[0]['name'])->toBe('Alice');
    expect($users[0]['email'])->toBe('alice@example.com');
    
    // And relationship is added
    expect($users[0]['posts'])->toBeArray();
    expect($users[0]['posts'])->toHaveCount(3);
});

test('eager loading handles empty parent array', function () {
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

    // Should not throw error
    \PhpLiteCore\Database\Model\EagerLoader::load(
        $this->pdo,
        get_class($userClass),
        $users,
        ['posts']
    );

    expect($users)->toBeEmpty();
});

test('eager loading handles empty relations array', function () {
    $userClass = new class extends BaseModel {
        protected string $table = 'users';
    };

    $stmt = $this->pdo->query('SELECT * FROM users LIMIT 1');
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Should not throw error
    \PhpLiteCore\Database\Model\EagerLoader::load(
        $this->pdo,
        get_class($userClass),
        $users,
        []
    );

    expect($users)->toHaveCount(1);
    expect($users[0])->not->toHaveKey('posts');
});

test('eager loading handles non-existent relation gracefully', function () {
    $userClass = new class extends BaseModel {
        protected string $table = 'users';
    };

    $stmt = $this->pdo->query('SELECT * FROM users LIMIT 1');
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Should not throw error for missing relation
    \PhpLiteCore\Database\Model\EagerLoader::load(
        $this->pdo,
        get_class($userClass),
        $users,
        ['nonexistent']
    );

    expect($users)->toHaveCount(1);
    expect($users[0])->not->toHaveKey('nonexistent');
});

test('eager loading with no matching related records', function () {
    $userClass = new class extends BaseModel {
        protected string $table = 'users';
        
        public function posts() {
            $postClass = new class extends BaseModel {
                protected string $table = 'posts';
            };
            return $this->hasMany(get_class($postClass), 'user_id', 'id');
        }
    };

    // David has no posts
    $stmt = $this->pdo->query('SELECT * FROM users WHERE id = 4');
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    \PhpLiteCore\Database\Model\EagerLoader::load(
        $this->pdo,
        get_class($userClass),
        $users,
        ['posts']
    );

    expect($users[0]['posts'])->toBeArray();
    expect($users[0]['posts'])->toBeEmpty();
});

test('hasMany preserves order of related records', function () {
    $userClass = new class extends BaseModel {
        protected string $table = 'users';
        
        public function posts() {
            $postClass = new class extends BaseModel {
                protected string $table = 'posts';
            };
            return $this->hasMany(get_class($postClass), 'user_id', 'id');
        }
    };

    $stmt = $this->pdo->query('SELECT * FROM users WHERE id = 1');
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    \PhpLiteCore\Database\Model\EagerLoader::load(
        $this->pdo,
        get_class($userClass),
        $users,
        ['posts']
    );

    // Posts should maintain their database order
    expect($users[0]['posts'][0]['id'])->toBe(1);
    expect($users[0]['posts'][1]['id'])->toBe(2);
    expect($users[0]['posts'][2]['id'])->toBe(3);
});

test('eager loading with custom foreign keys', function () {
    $postClass = new class extends BaseModel {
        protected string $table = 'posts';
        
        public function comments() {
            $commentClass = new class extends BaseModel {
                protected string $table = 'comments';
            };
            return $this->hasMany(get_class($commentClass), 'post_id', 'id');
        }
    };

    $stmt = $this->pdo->query('SELECT * FROM posts WHERE id IN (1, 4)');
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    \PhpLiteCore\Database\Model\EagerLoader::load(
        $this->pdo,
        get_class($postClass),
        $posts,
        ['comments']
    );

    expect($posts[0]['comments'])->toHaveCount(3); // Post 1 has 3 comments
    expect($posts[1]['comments'])->toHaveCount(2); // Post 4 has 2 comments
});

test('eager loading maintains data types', function () {
    $userClass = new class extends BaseModel {
        protected string $table = 'users';
        
        public function posts() {
            $postClass = new class extends BaseModel {
                protected string $table = 'posts';
            };
            return $this->hasMany(get_class($postClass), 'user_id', 'id');
        }
    };

    $stmt = $this->pdo->query('SELECT * FROM users WHERE id = 1');
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    \PhpLiteCore\Database\Model\EagerLoader::load(
        $this->pdo,
        get_class($userClass),
        $users,
        ['posts']
    );

    // Check that numeric fields are preserved as numbers
    expect($users[0]['posts'][0]['id'])->toBeInt();
    expect($users[0]['posts'][0]['user_id'])->toBeInt();
    expect($users[0]['posts'][0]['views'])->toBeInt();
    
    // And string fields as strings
    expect($users[0]['posts'][0]['title'])->toBeString();
    expect($users[0]['posts'][0]['content'])->toBeString();
});

test('eager loading handles large number of parent records efficiently', function () {
    // Insert more users
    for ($i = 5; $i <= 100; $i++) {
        $this->pdo->exec("INSERT INTO users (id, name, email) VALUES ({$i}, 'User{$i}', 'user{$i}@example.com')");
    }

    $userClass = new class extends BaseModel {
        protected string $table = 'users';
        
        public function posts() {
            $postClass = new class extends BaseModel {
                protected string $table = 'posts';
            };
            return $this->hasMany(get_class($postClass), 'user_id', 'id');
        }
    };

    $stmt = $this->pdo->query('SELECT * FROM users');
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Should handle 100 users without issues
    \PhpLiteCore\Database\Model\EagerLoader::load(
        $this->pdo,
        get_class($userClass),
        $users,
        ['posts']
    );

    expect($users)->toHaveCount(100);
    
    // Verify first few users still have correct posts
    expect($users[0]['posts'])->toHaveCount(3);
    expect($users[1]['posts'])->toHaveCount(2);
    expect($users[2]['posts'])->toHaveCount(1);
    
    // And new users have no posts
    expect($users[50]['posts'])->toBeEmpty();
});

test('hasOne returns null for missing relationship', function () {
    $userClass = new class extends BaseModel {
        protected string $table = 'users';
        
        public function profile() {
            $profileClass = new class extends BaseModel {
                protected string $table = 'profiles';
            };
            return $this->hasOne(get_class($profileClass), 'user_id', 'id');
        }
    };

    // User 4 (David) has no profile
    $stmt = $this->pdo->query('SELECT * FROM users WHERE id = 4');
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    \PhpLiteCore\Database\Model\EagerLoader::load(
        $this->pdo,
        get_class($userClass),
        $users,
        ['profile']
    );

    expect($users[0]['profile'])->toBeNull();
});

test('belongsTo handles all parents pointing to same related record', function () {
    // All posts by Alice
    $postClass = new class extends BaseModel {
        protected string $table = 'posts';
        
        public function author() {
            $userClass = new class extends BaseModel {
                protected string $table = 'users';
            };
            return $this->belongsTo(get_class($userClass), 'user_id', 'id');
        }
    };

    $stmt = $this->pdo->query('SELECT * FROM posts WHERE user_id = 1');
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    \PhpLiteCore\Database\Model\EagerLoader::load(
        $this->pdo,
        get_class($postClass),
        $posts,
        ['author']
    );

    // All posts should have the same author
    expect($posts[0]['author']['name'])->toBe('Alice');
    expect($posts[1]['author']['name'])->toBe('Alice');
    expect($posts[2]['author']['name'])->toBe('Alice');
});

test('relation methods return correct instance types', function () {
    $userClass = new class extends BaseModel {
        protected string $table = 'users';
        
        public function posts() {
            $postClass = new class extends BaseModel {
                protected string $table = 'posts';
            };
            return $this->hasMany(get_class($postClass), 'user_id', 'id');
        }
        
        public function profile() {
            $profileClass = new class extends BaseModel {
                protected string $table = 'profiles';
            };
            return $this->hasOne(get_class($profileClass), 'user_id', 'id');
        }
    };

    $user = new $userClass();
    
    expect($user->posts())->toBeInstanceOf(\PhpLiteCore\Database\Model\Relations\HasMany::class);
    expect($user->profile())->toBeInstanceOf(\PhpLiteCore\Database\Model\Relations\HasOne::class);
});
