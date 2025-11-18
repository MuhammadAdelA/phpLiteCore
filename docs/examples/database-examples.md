# Database Examples - Real-World Use Cases

This document provides practical, real-world examples of using the phpLiteCore database layer in common application scenarios.

## Table of Contents

1. [User Management System](#user-management-system)
2. [Blog Platform](#blog-platform)
3. [E-commerce Order Processing](#e-commerce-order-processing)
4. [Social Media Feed](#social-media-feed)
5. [Multi-tenancy Application](#multi-tenancy-application)

---

## User Management System

### User Registration with Profile Creation

```php
<?php
use App\Models\User;
use App\Models\Profile;

function registerUser(array $userData, Database $db): ?User
{
    $db->beginTransaction();
    
    try {
        // Create user account
        $user = new User();
        $user->email = $userData['email'];
        $user->password = password_hash($userData['password'], PASSWORD_DEFAULT);
        $user->name = $userData['name'];
        $user->status = 'pending_verification';
        $user->created_at = date('Y-m-d H:i:s');
        $user->save();
        
        // Create associated profile
        $profile = new Profile();
        $profile->user_id = $user->id;
        $profile->bio = $userData['bio'] ?? '';
        $profile->avatar = 'default.jpg';
        $profile->save();
        
        // Create welcome notification or initial settings
        $db->table('notifications')->insert([
            'user_id' => $user->id,
            'message' => 'Welcome to our platform!',
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        $db->commit();
        return $user;
        
    } catch (\Exception $e) {
        $db->rollBack();
        error_log("User registration failed: " . $e->getMessage());
        return null;
    }
}
```

### User Authentication with Activity Tracking

```php
<?php
function authenticateUser(string $email, string $password, Database $db): ?User
{
    // Find user by email
    $user = User::where('email', $email)->first();
    
    if (!$user || !password_verify($password, $user->password)) {
        return null;
    }
    
    // Update last login
    $db->beginTransaction();
    try {
        User::where('id', $user->id)->update([
            'last_login' => date('Y-m-d H:i:s'),
            'login_count' => $db->raw('login_count + 1')
        ]);
        
        // Log login activity
        $db->table('activity_logs')->insert([
            'user_id' => $user->id,
            'action' => 'login',
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        $db->commit();
    } catch (\Exception $e) {
        $db->rollBack();
        error_log("Login tracking failed: " . $e->getMessage());
    }
    
    return $user;
}
```

### List Users with Pagination and Filtering

```php
<?php
function getUserList(array $filters, int $page = 1): array
{
    $perPage = 20;
    
    $query = User::where('deleted_at', null); // Soft delete filter
    
    // Apply filters
    if (!empty($filters['role'])) {
        $query->where('role', $filters['role']);
    }
    
    if (!empty($filters['status'])) {
        $query->where('status', $filters['status']);
    }
    
    if (!empty($filters['search'])) {
        $query->where(function($q) use ($filters) {
            $q->whereHas('name', $filters['search'])
              ->orWhereHas('email', $filters['search']);
        });
    }
    
    // Load relationships to avoid N+1
    $query->with(['profile', 'posts']);
    
    return $query->orderBy('created_at', 'DESC')
        ->paginate($perPage, $page);
}
```

---

## Blog Platform

### Creating a Blog Post with Tags

```php
<?php
use App\Models\Post;
use App\Models\Tag;

function createBlogPost(array $postData, Database $db): ?Post
{
    $db->beginTransaction();
    
    try {
        // Create the post
        $post = new Post();
        $post->user_id = $postData['user_id'];
        $post->title = $postData['title'];
        $post->slug = generateSlug($postData['title']);
        $post->content = $postData['content'];
        $post->status = 'draft';
        $post->created_at = date('Y-m-d H:i:s');
        $post->save();
        
        // Handle tags
        if (!empty($postData['tags'])) {
            foreach ($postData['tags'] as $tagName) {
                // Find or create tag
                $tag = Tag::where('name', $tagName)->first();
                
                if (!$tag) {
                    $tag = new Tag();
                    $tag->name = $tagName;
                    $tag->slug = generateSlug($tagName);
                    $tag->save();
                }
                
                // Create post-tag relationship
                $db->table('post_tags')->insert([
                    'post_id' => $post->id,
                    'tag_id' => $tag->id
                ]);
            }
        }
        
        $db->commit();
        return $post;
        
    } catch (\Exception $e) {
        $db->rollBack();
        throw $e;
    }
}

function generateSlug(string $text): string
{
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $text)));
    return substr($slug, 0, 100);
}
```

### Fetching Blog Posts with Author and Comments

```php
<?php
function getBlogPosts(int $page = 1, ?int $categoryId = null): array
{
    $perPage = 10;
    
    $query = Post::where('status', 'published')
        ->where('published_at', '<=', date('Y-m-d H:i:s'));
    
    if ($categoryId) {
        $query->where('category_id', $categoryId);
    }
    
    // Eager load author and comment count
    $result = $query->with(['author', 'author.profile'])
        ->orderBy('published_at', 'DESC')
        ->paginate($perPage, $page);
    
    // Manually add comment counts (if not using a relation)
    foreach ($result['items'] as &$post) {
        $post['comment_count'] = Comment::where('post_id', $post['id'])
            ->where('approved', 1)
            ->count();
    }
    
    return $result;
}
```

### Incrementing Post Views

```php
<?php
function incrementPostViews(int $postId, Database $db): void
{
    // Use raw SQL to increment atomically
    $db->table('posts')
        ->where('id', $postId)
        ->update(['views' => $db->raw('views + 1')]);
}
```

---

## E-commerce Order Processing

### Processing an Order with Inventory Management

```php
<?php
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;

function processOrder(array $orderData, Database $db): ?Order
{
    $db->beginTransaction();
    
    try {
        // Create order
        $order = new Order();
        $order->user_id = $orderData['user_id'];
        $order->status = 'pending';
        $order->total_amount = 0;
        $order->created_at = date('Y-m-d H:i:s');
        $order->save();
        
        $totalAmount = 0;
        
        // Process each item
        foreach ($orderData['items'] as $item) {
            $product = Product::find($item['product_id']);
            
            if (!$product) {
                throw new \Exception("Product not found: {$item['product_id']}");
            }
            
            // Check stock
            if ($product->stock < $item['quantity']) {
                throw new \Exception("Insufficient stock for: {$product->name}");
            }
            
            // Create order item
            $orderItem = new OrderItem();
            $orderItem->order_id = $order->id;
            $orderItem->product_id = $product->id;
            $orderItem->quantity = $item['quantity'];
            $orderItem->price = $product->price;
            $orderItem->subtotal = $product->price * $item['quantity'];
            $orderItem->save();
            
            // Update product stock
            Product::where('id', $product->id)
                ->update(['stock' => $product->stock - $item['quantity']]);
            
            $totalAmount += $orderItem->subtotal;
        }
        
        // Update order total
        Order::where('id', $order->id)
            ->update(['total_amount' => $totalAmount]);
        
        $db->commit();
        return $order;
        
    } catch (\Exception $e) {
        $db->rollBack();
        error_log("Order processing failed: " . $e->getMessage());
        return null;
    }
}
```

### Order Status Updates with History

```php
<?php
function updateOrderStatus(int $orderId, string $newStatus, Database $db): bool
{
    $db->beginTransaction();
    
    try {
        $order = Order::find($orderId);
        
        if (!$order) {
            throw new \Exception("Order not found");
        }
        
        $oldStatus = $order->status;
        
        // Update order status
        Order::where('id', $orderId)->update([
            'status' => $newStatus,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        // Log status change
        $db->table('order_history')->insert([
            'order_id' => $orderId,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'changed_at' => date('Y-m-d H:i:s'),
            'changed_by' => $_SESSION['user_id'] ?? null
        ]);
        
        // If order is cancelled, restore inventory
        if ($newStatus === 'cancelled') {
            $items = OrderItem::where('order_id', $orderId)->get();
            
            foreach ($items as $item) {
                Product::where('id', $item['product_id'])
                    ->update(['stock' => $db->raw("stock + {$item['quantity']}")]);
            }
        }
        
        $db->commit();
        return true;
        
    } catch (\Exception $e) {
        $db->rollBack();
        error_log("Order status update failed: " . $e->getMessage());
        return false;
    }
}
```

---

## Social Media Feed

### Creating a Post with Mentions

```php
<?php
use App\Models\FeedPost;
use App\Models\Mention;

function createFeedPost(int $userId, string $content, Database $db): ?FeedPost
{
    $db->beginTransaction();
    
    try {
        // Create post
        $post = new FeedPost();
        $post->user_id = $userId;
        $post->content = $content;
        $post->likes_count = 0;
        $post->comments_count = 0;
        $post->created_at = date('Y-m-d H:i:s');
        $post->save();
        
        // Extract and save mentions (@username)
        preg_match_all('/@(\w+)/', $content, $matches);
        $mentions = array_unique($matches[1]);
        
        foreach ($mentions as $username) {
            $mentionedUser = User::where('username', $username)->first();
            
            if ($mentionedUser) {
                $mention = new Mention();
                $mention->post_id = $post->id;
                $mention->user_id = $mentionedUser->id;
                $mention->mentioned_by = $userId;
                $mention->save();
                
                // Create notification
                $db->table('notifications')->insert([
                    'user_id' => $mentionedUser->id,
                    'type' => 'mention',
                    'data' => json_encode(['post_id' => $post->id]),
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }
        }
        
        $db->commit();
        return $post;
        
    } catch (\Exception $e) {
        $db->rollBack();
        throw $e;
    }
}
```

### Building a News Feed with Following

```php
<?php
function getUserFeed(int $userId, int $page = 1): array
{
    $perPage = 20;
    
    // Get list of users that the current user follows
    $followingIds = $db->table('followers')
        ->select('following_id')
        ->where('follower_id', $userId)
        ->get();
    
    $followingIds = array_column($followingIds, 'following_id');
    $followingIds[] = $userId; // Include own posts
    
    // Get posts from followed users
    $result = FeedPost::whereIn('user_id', $followingIds)
        ->with(['author', 'author.profile'])
        ->orderBy('created_at', 'DESC')
        ->paginate($perPage, $page);
    
    return $result;
}
```

### Like/Unlike System

```php
<?php
function toggleLike(int $userId, int $postId, Database $db): array
{
    $db->beginTransaction();
    
    try {
        // Check if already liked
        $existing = $db->table('likes')
            ->select('id')
            ->where('user_id', $userId)
            ->where('post_id', $postId)
            ->get();
        
        if (empty($existing)) {
            // Add like
            $db->table('likes')->insert([
                'user_id' => $userId,
                'post_id' => $postId,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            // Increment count
            FeedPost::where('id', $postId)
                ->update(['likes_count' => $db->raw('likes_count + 1')]);
            
            $liked = true;
        } else {
            // Remove like
            $db->table('likes')
                ->where('user_id', $userId)
                ->where('post_id', $postId)
                ->delete();
            
            // Decrement count
            FeedPost::where('id', $postId)
                ->update(['likes_count' => $db->raw('likes_count - 1')]);
            
            $liked = false;
        }
        
        // Get updated count
        $post = FeedPost::find($postId);
        
        $db->commit();
        
        return [
            'liked' => $liked,
            'total_likes' => $post->likes_count
        ];
        
    } catch (\Exception $e) {
        $db->rollBack();
        throw $e;
    }
}
```

---

## Multi-tenancy Application

### Tenant-scoped Queries

```php
<?php
// Global scope for tenant isolation
class TenantModel extends BaseModel
{
    protected static function boot()
    {
        parent::boot();
        
        // Automatically add tenant filter to all queries
        static::addGlobalScope('tenant', function ($query) {
            if ($tenantId = $_SESSION['tenant_id'] ?? null) {
                $query->where('tenant_id', $tenantId);
            }
        });
    }
}

// Usage
class Customer extends TenantModel
{
    // Automatically filtered by tenant_id
}

// Get customers for current tenant only
$customers = Customer::where('status', 'active')->get();
```

### Switching Between Tenants

```php
<?php
function switchTenant(int $newTenantId, int $userId, Database $db): bool
{
    $db->beginTransaction();
    
    try {
        // Verify user has access to this tenant
        $access = $db->table('tenant_users')
            ->where('user_id', $userId)
            ->where('tenant_id', $newTenantId)
            ->first();
        
        if (!$access) {
            throw new \Exception('Access denied to tenant');
        }
        
        // Log tenant switch
        $db->table('tenant_access_log')->insert([
            'user_id' => $userId,
            'tenant_id' => $newTenantId,
            'switched_at' => date('Y-m-d H:i:s'),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'
        ]);
        
        // Update session
        $_SESSION['tenant_id'] = $newTenantId;
        
        $db->commit();
        return true;
        
    } catch (\Exception $e) {
        $db->rollBack();
        return false;
    }
}
```

---

## Best Practices Summary

1. **Always use transactions** for operations that modify multiple tables
2. **Use eager loading** (`with()`) to prevent N+1 query problems
3. **Validate input** before database operations
4. **Handle exceptions** gracefully and log errors
5. **Use parameter binding** (never concatenate user input)
6. **Keep transactions short** - don't include external API calls
7. **Index frequently queried columns** for better performance
8. **Use soft deletes** instead of hard deletes when possible
9. **Log important operations** for audit trails
10. **Test edge cases** - empty results, concurrent updates, etc.

---

For more information, see the [Complete Database Guide](database-guide.md).
