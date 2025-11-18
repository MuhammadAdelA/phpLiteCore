# Database Layer Guide - phpLiteCore

This comprehensive guide covers all database features in phpLiteCore, including query building, transactions, relationships, and best practices.

## Table of Contents

1. [Getting Started](#getting-started)
2. [Query Builder Basics](#query-builder-basics)
3. [Model Relationships](#model-relationships)
4. [Transactions](#transactions)
5. [Advanced Query Patterns](#advanced-query-patterns)
6. [Edge Cases & Best Practices](#edge-cases--best-practices)

---

## Getting Started

### Basic Model Setup

Create a model by extending `BaseModel`:

```php
<?php
namespace App\Models;

use PhpLiteCore\Database\Model\BaseModel;

class User extends BaseModel
{
    // Table name is auto-inferred as 'users'
    // Override if needed:
    // protected string $table = 'my_users';
}
```

### Basic CRUD Operations

```php
// Create a new record
$user = new User();
$user->name = 'John Doe';
$user->email = 'john@example.com';
$user->save();

// Find by ID
$user = User::find(1);

// Update
$user->email = 'newemail@example.com';
$user->save();

// Delete
User::where('id', 1)->delete();
```

---

## Query Builder Basics

### Retrieving Data

```php
// Get all records
$users = User::all();

// Get first record
$user = User::first();

// Find by primary key
$user = User::find(5);

// Get specific columns
$users = User::select('id', 'name', 'email')->get();
```

### Query Builder Chaining

Chain multiple methods to build complex queries:

```php
// Simple chaining
$activeUsers = User::where('status', 'active')
    ->orderBy('created_at', 'DESC')
    ->limit(10)
    ->get();

// Complex chaining with multiple conditions
$results = User::where('role', 'admin')
    ->where('age', '>', 18)
    ->orWhere('vip_status', 'true')
    ->orderBy('last_login', 'DESC')
    ->offset(20)
    ->limit(10)
    ->get();

// Nested WHERE groups
$users = User::where(function($query) {
        $query->where('role', 'admin')
              ->orWhere('role', 'moderator');
    })
    ->where('status', 'active')
    ->get();
```

### WHERE Clauses

```php
// Basic WHERE
User::where('email', 'john@example.com')->first();
User::where('age', '>', 18)->get();
User::where('status', '!=', 'banned')->get();

// Array WHERE (AND conditions)
User::where([
    'role' => 'admin',
    'status' => 'active'
])->get();

// OR WHERE
User::where('role', 'admin')
    ->orWhere('role', 'moderator')
    ->get();

// WHERE IN
User::whereIn('id', [1, 2, 3, 4, 5])->get();
User::whereNotIn('status', ['banned', 'suspended'])->get();

// WHERE BETWEEN
User::whereBetween('age', [18, 65])->get();
User::orWhereBetween('salary', [50000, 100000])->get();

// LIKE clauses
User::whereStarts('email', 'admin')->get();  // email LIKE 'admin%'
User::whereHas('name', 'smith')->get();      // name LIKE '%smith%'
User::whereEnds('domain', '.com')->get();    // domain LIKE '%.com'
```

### Ordering and Limiting

```php
// Order by single column
User::orderBy('created_at', 'DESC')->get();

// Order by multiple columns
User::orderBy('role', 'ASC')
    ->orderBy('name', 'ASC')
    ->get();

// Limit and offset
User::limit(10)->get();
User::offset(20)->limit(10)->get();
```

### Aggregates

```php
// Count all records
$total = User::count();

// Count with conditions
$activeCount = User::where('status', 'active')->count();

// Count in complex queries
$adminCount = User::where('role', 'admin')
    ->where('age', '>', 18)
    ->count();
```

### Pagination

```php
$perPage = 15;
$currentPage = $_GET['page'] ?? 1;

$result = User::where('status', 'active')
    ->orderBy('created_at', 'DESC')
    ->paginate($perPage, $currentPage);

$users = $result['items'];
$paginator = $result['paginator'];

// In your view:
// <?= $paginator->render() ?>
```

---

## Model Relationships

phpLiteCore supports three types of relationships: `hasMany`, `hasOne`, and `belongsTo`.

### Defining Relationships

#### One-to-Many (hasMany)

```php
// In User model
class User extends BaseModel
{
    public function posts()
    {
        return $this->hasMany(Post::class);
        // Assumes 'user_id' foreign key on posts table
        // Customize: $this->hasMany(Post::class, 'author_id', 'id')
    }
}
```

#### One-to-One (hasOne)

```php
// In User model
class User extends BaseModel
{
    public function profile()
    {
        return $this->hasOne(Profile::class);
        // Assumes 'user_id' foreign key on profiles table
    }
}
```

#### Belongs To (belongsTo)

```php
// In Post model
class Post extends BaseModel
{
    public function author()
    {
        return $this->belongsTo(User::class);
        // Assumes 'user_id' foreign key on posts table
        // Customize: $this->belongsTo(User::class, 'author_id', 'id')
    }
    
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
```

### Eager Loading

Eager loading helps avoid the N+1 query problem by loading relationships upfront.

#### Basic Eager Loading

```php
// Load users with their posts (2 queries instead of N+1)
$users = User::with('posts')->get();

foreach ($users as $user) {
    // $user['posts'] is already loaded
    foreach ($user['posts'] as $post) {
        echo $post['title'];
    }
}
```

#### Multiple Relationships

```php
// Load multiple relationships at once
$users = User::with(['posts', 'profile'])->get();

// Or using array syntax
$users = User::with('posts', 'profile')->get();
```

#### Eager Loading with Constraints

```php
// Load users with their posts, filtered and ordered
$users = User::where('status', 'active')
    ->with('posts')
    ->orderBy('created_at', 'DESC')
    ->get();

foreach ($users as $user) {
    echo $user['name'] . ' has ' . count($user['posts']) . ' posts';
}
```

### Complete Relationship Example

```php
// Models setup
class User extends BaseModel
{
    public function posts()
    {
        return $this->hasMany(Post::class);
    }
    
    public function profile()
    {
        return $this->hasOne(Profile::class);
    }
}

class Post extends BaseModel
{
    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}

class Profile extends BaseModel
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

// Usage examples
// Get users with all their posts and profiles
$users = User::with(['posts', 'profile'])
    ->where('status', 'active')
    ->get();

// Get posts with their authors
$posts = Post::with('author')
    ->orderBy('created_at', 'DESC')
    ->limit(10)
    ->get();

foreach ($posts as $post) {
    echo $post['title'] . ' by ' . $post['author']['name'];
}
```

---

## Transactions

Transactions ensure data integrity by grouping multiple database operations into a single atomic unit.

### Basic Transaction Usage

```php
use PhpLiteCore\Database\Database;

// Get database instance from container
$db = $container->get('db');

try {
    // Begin transaction
    $db->beginTransaction();
    
    // Perform multiple operations
    $user = new User();
    $user->name = 'John Doe';
    $user->email = 'john@example.com';
    $user->save();
    
    $profile = new Profile();
    $profile->user_id = $user->id;
    $profile->bio = 'Software developer';
    $profile->save();
    
    // Commit if all operations succeed
    $db->commit();
    
    echo "User and profile created successfully!";
} catch (\Exception $e) {
    // Rollback on any error
    $db->rollBack();
    
    echo "Error: " . $e->getMessage();
}
```

### Transaction with Query Builder

```php
$db = $container->get('db');

try {
    $db->beginTransaction();
    
    // Insert using query builder
    $userId = $db->table('users')->insert([
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'status' => 'active'
    ]);
    
    // Update related records
    $db->table('profiles')->insert([
        'user_id' => $userId,
        'bio' => 'Designer'
    ]);
    
    // Update counters
    $db->table('statistics')
        ->where('name', 'total_users')
        ->update(['value' => $db->raw('value + 1')]);
    
    $db->commit();
} catch (\Exception $e) {
    $db->rollBack();
    throw $e;
}
```

### Complex Transaction Example

```php
// Transfer credits between users
function transferCredits(Database $db, int $fromUserId, int $toUserId, int $amount): bool
{
    try {
        $db->beginTransaction();
        
        // Check sender's balance
        $sender = User::find($fromUserId);
        if (!$sender || $sender->credits < $amount) {
            throw new \Exception('Insufficient credits');
        }
        
        // Deduct from sender
        User::where('id', $fromUserId)
            ->update(['credits' => $sender->credits - $amount]);
        
        // Add to receiver
        $receiver = User::find($toUserId);
        if (!$receiver) {
            throw new \Exception('Receiver not found');
        }
        
        User::where('id', $toUserId)
            ->update(['credits' => $receiver->credits + $amount]);
        
        // Log transaction
        $db->table('transactions')->insert([
            'from_user_id' => $fromUserId,
            'to_user_id' => $toUserId,
            'amount' => $amount,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        $db->commit();
        return true;
        
    } catch (\Exception $e) {
        $db->rollBack();
        error_log('Transfer failed: ' . $e->getMessage());
        return false;
    }
}
```

### Transaction Best Practices

1. **Always use try-catch blocks**: Ensure rollback happens on errors
2. **Keep transactions short**: Don't include external API calls or long operations
3. **Avoid nested transactions**: PDO doesn't support true nested transactions
4. **Check before committing**: Validate data before commit
5. **Handle deadlocks**: Be prepared for database lock conflicts

```php
// Good transaction practice
try {
    $db->beginTransaction();
    
    // Fast database operations only
    $result = performDatabaseOperations();
    
    // Validate before commit
    if (!$result) {
        throw new \Exception('Validation failed');
    }
    
    $db->commit();
} catch (\Exception $e) {
    $db->rollBack();
    // Handle error appropriately
}

// Bad practice - avoid this
try {
    $db->beginTransaction();
    
    // DON'T: External API call inside transaction
    $apiResponse = file_get_contents('https://api.example.com/data');
    
    // DON'T: Heavy computation
    sleep(10);
    
    $db->commit();
} catch (\Exception $e) {
    $db->rollBack();
}
```

---

## Advanced Query Patterns

### Raw Queries

When you need full control, use raw SQL:

```php
$db = $container->get('db');

// Simple raw query
$stmt = $db->raw('SELECT * FROM users WHERE age > ?', [18]);
$users = $stmt->fetchAll();

// Raw query with multiple bindings
$stmt = $db->raw(
    'SELECT * FROM users WHERE role = ? AND status = ? ORDER BY created_at DESC',
    ['admin', 'active']
);
$admins = $stmt->fetchAll();

// Complex raw query
$sql = "
    SELECT u.*, COUNT(p.id) as post_count
    FROM users u
    LEFT JOIN posts p ON u.id = p.user_id
    WHERE u.status = ?
    GROUP BY u.id
    HAVING post_count > ?
    ORDER BY post_count DESC
";
$stmt = $db->raw($sql, ['active', 5]);
$activeAuthors = $stmt->fetchAll();
```

### Grouping

```php
// Group by single column
$stats = $db->table('orders')
    ->select('status', 'COUNT(*) as count')
    ->groupBy('status')
    ->get();

// Group by multiple columns
$report = $db->table('sales')
    ->select('year', 'month', 'SUM(amount) as total')
    ->groupBy('year', 'month')
    ->orderBy('year', 'DESC')
    ->get();
```

### Subqueries and Complex Patterns

```php
// Using raw for subqueries
$db->raw("
    SELECT * FROM users 
    WHERE id IN (
        SELECT DISTINCT user_id 
        FROM posts 
        WHERE created_at > DATE_SUB(NOW(), INTERVAL 30 DAY)
    )
");

// Combining methods for complex queries
$users = User::where('status', 'active')
    ->where(function($q) {
        $q->where('role', 'admin')
          ->orWhere('role', 'moderator');
    })
    ->whereNotIn('id', [1, 2, 3])
    ->orderBy('last_activity', 'DESC')
    ->limit(50)
    ->get();
```

---

## Edge Cases & Best Practices

### Handling Empty Results

```php
// Check if result exists
$user = User::find(999);
if ($user === null) {
    echo "User not found";
}

// Empty array from get()
$users = User::where('status', 'impossible')->get();
if (empty($users)) {
    echo "No users found";
}

// Count returns 0 for no matches
$count = User::where('age', '>', 1000)->count();
// $count === 0
```

### Preventing SQL Injection

Always use parameter binding, never concatenate user input:

```php
// GOOD: Uses parameter binding
$email = $_GET['email'];
$user = User::where('email', $email)->first();

// GOOD: Bindings in raw queries
$stmt = $db->raw('SELECT * FROM users WHERE email = ?', [$email]);

// BAD: Never do this - SQL injection vulnerability!
// $sql = "SELECT * FROM users WHERE email = '$email'";
// $db->raw($sql);
```

### Working with Dates

```php
// Insert with timestamps
$user = new User();
$user->name = 'John';
$user->created_at = date('Y-m-d H:i:s');
$user->save();

// Query by date ranges
$recentUsers = User::whereBetween('created_at', [
    '2024-01-01 00:00:00',
    '2024-12-31 23:59:59'
])->get();

// Using raw for date functions
$stmt = $db->raw(
    'SELECT * FROM users WHERE DATE(created_at) = ?',
    [date('Y-m-d')]
);
```

### Handling Large Datasets

```php
// Use pagination for large datasets
$page = $_GET['page'] ?? 1;
$result = User::orderBy('id', 'ASC')
    ->paginate(100, $page);

// Or use limit/offset for batch processing
$batchSize = 1000;
$offset = 0;

do {
    $users = User::limit($batchSize)
        ->offset($offset)
        ->get();
    
    foreach ($users as $user) {
        // Process each user
        processUser($user);
    }
    
    $offset += $batchSize;
} while (count($users) === $batchSize);
```

### Avoiding N+1 Queries

```php
// BAD: N+1 query problem
$users = User::all();
foreach ($users as $user) {
    // Each iteration triggers a new query!
    $posts = Post::where('user_id', $user->id)->get();
}

// GOOD: Use eager loading
$users = User::with('posts')->get();
foreach ($users as $user) {
    // Posts are already loaded, no additional queries
    $posts = $user['posts'];
}
```

### Model Update Optimization

```php
// Only updates if attributes changed
$user = User::find(1);
$user->name = 'New Name';
$user->save(); // Executes UPDATE

$user = User::find(1);
$user->save(); // No UPDATE executed - nothing changed

// Bulk updates are more efficient
User::where('status', 'pending')
    ->update(['status' => 'active']);
```

### Validation Before Save

```php
// Validate data before saving
$user = new User();
$user->email = $_POST['email'];

// Check for duplicates
$existing = User::where('email', $user->email)->first();
if ($existing) {
    throw new \Exception('Email already exists');
}

$user->name = $_POST['name'];
$user->save();
```

### Error Handling

```php
use PDOException;

try {
    $user = new User();
    $user->name = 'Test';
    $user->email = 'invalid-email-that-is-too-long-for-database-column';
    $user->save();
} catch (PDOException $e) {
    // Handle database errors
    error_log('Database error: ' . $e->getMessage());
    
    // Check for specific error codes
    if ($e->getCode() == '23000') {
        echo 'Duplicate entry or constraint violation';
    }
}
```

---

## Quick Reference

### Common Query Patterns

```php
// Find by ID
$user = User::find(1);

// First matching record
$user = User::where('email', 'test@example.com')->first();

// All matching records
$users = User::where('status', 'active')->get();

// Count records
$count = User::where('role', 'admin')->count();

// Paginate
$result = User::paginate(20, $page);

// Order and limit
$users = User::orderBy('created_at', 'DESC')->limit(10)->get();

// Multiple conditions
$users = User::where('status', 'active')
    ->where('role', 'admin')
    ->get();

// OR conditions
$users = User::where('role', 'admin')
    ->orWhere('role', 'moderator')
    ->get();

// IN clause
$users = User::whereIn('id', [1, 2, 3])->get();

// Eager loading
$users = User::with('posts')->get();
$users = User::with(['posts', 'profile'])->get();

// Transactions
$db->beginTransaction();
try {
    // operations...
    $db->commit();
} catch (\Exception $e) {
    $db->rollBack();
}
```

---

## Additional Resources

- [Query Builder Guide (HTML)](query-builder-guide_en.html) - Interactive guide with styling
- [API Documentation](https://muhammadadela.github.io/phpLiteCore/)
- [Main README](../README.md)

For questions or issues, please visit the [GitHub repository](https://github.com/MuhammadAdelA/phpLiteCore).
