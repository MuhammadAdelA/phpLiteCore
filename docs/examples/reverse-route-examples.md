# Reverse Route URL Generation - Practical Examples

This document provides practical, real-world examples of using the reverse route URL generation feature in phpLiteCore.

## Example 1: Blog Post Management

### Route Definition
```php
// routes/web.php
$router->get('/posts', ['PostController', 'index'])->name('posts.index');
$router->get('/posts/create', ['PostController', 'create'])->name('posts.create');
$router->get('/posts/{id}', ['PostController', 'show'])
    ->name('posts.show')
    ->where(['id' => '[0-9]+']);
$router->get('/posts/{id}/edit', ['PostController', 'edit'])
    ->name('posts.edit')
    ->where(['id' => '[0-9]+']);
$router->post('/posts', ['PostController', 'store'])->name('posts.store');
$router->post('/posts/{id}', ['PostController', 'update'])
    ->name('posts.update')
    ->where(['id' => '[0-9]+']);
```

### Controller Usage
```php
// app/Controllers/PostController.php
class PostController extends BaseController
{
    public function index()
    {
        $posts = Post::all();
        
        // Generate URLs for each post
        foreach ($posts as $post) {
            $post->viewUrl = route('posts.show', ['id' => $post->id]);
            $post->editUrl = route('posts.edit', ['id' => $post->id]);
        }
        
        return view('posts.index', ['posts' => $posts]);
    }
    
    public function store()
    {
        // Validate and create post
        $post = Post::create($_POST);
        
        // Redirect to the newly created post
        header('Location: ' . route('posts.show', ['id' => $post->id]));
        exit;
    }
    
    public function update($id)
    {
        // Validate and update post
        $post = Post::find($id);
        $post->update($_POST);
        
        // Redirect back to post view
        header('Location: ' . route('posts.show', ['id' => $id]));
        exit;
    }
}
```

### View Usage
```php
<!-- views/posts/index.php -->
<h1>Blog Posts</h1>

<a href="<?= route('posts.create') ?>" class="btn btn-primary">Create New Post</a>

<ul>
    <?php foreach ($posts as $post): ?>
        <li>
            <a href="<?= route('posts.show', ['id' => $post->id]) ?>">
                <?= e($post->title) ?>
            </a>
            <a href="<?= route('posts.edit', ['id' => $post->id]) ?>">Edit</a>
        </li>
    <?php endforeach; ?>
</ul>
```

## Example 2: REST API with Nested Resources

### Route Definition
```php
// routes/web.php
$router->group(['prefix' => 'api/v1'], function ($router) {
    // User routes
    $router->get('/users', ['Api\UserController', 'index'])->name('api.users.index');
    $router->get('/users/{userId}', ['Api\UserController', 'show'])->name('api.users.show');
    
    // User posts routes (nested resource)
    $router->get('/users/{userId}/posts', ['Api\UserPostController', 'index'])
        ->name('api.users.posts.index');
    $router->get('/users/{userId}/posts/{postId}', ['Api\UserPostController', 'show'])
        ->name('api.users.posts.show');
});
```

### API Controller Usage
```php
// app/Controllers/Api/UserPostController.php
class UserPostController
{
    public function index($userId)
    {
        $posts = Post::where('user_id', $userId)->get();
        
        return json([
            'data' => array_map(function($post) use ($userId) {
                return [
                    'id' => $post->id,
                    'title' => $post->title,
                    'links' => [
                        'self' => route('api.users.posts.show', [
                            'userId' => $userId,
                            'postId' => $post->id
                        ]),
                        'user' => route('api.users.show', ['userId' => $userId])
                    ]
                ];
            }, $posts)
        ]);
    }
    
    public function show($userId, $postId)
    {
        $post = Post::find($postId);
        
        return json([
            'data' => [
                'id' => $post->id,
                'title' => $post->title,
                'content' => $post->content,
                'links' => [
                    'self' => route('api.users.posts.show', [
                        'userId' => $userId,
                        'postId' => $postId
                    ]),
                    'collection' => route('api.users.posts.index', ['userId' => $userId]),
                    'user' => route('api.users.show', ['userId' => $userId])
                ]
            ]
        ]);
    }
}
```

## Example 3: Admin Panel with Route Groups

### Route Definition
```php
// routes/web.php
$router->group(['prefix' => 'admin', 'middleware' => ['AuthMiddleware', 'AdminMiddleware']], function ($router) {
    $router->get('/dashboard', ['Admin\DashboardController', 'index'])->name('admin.dashboard');
    
    $router->group(['prefix' => 'users'], function ($router) {
        $router->get('/', ['Admin\UserController', 'index'])->name('admin.users.index');
        $router->get('/{id}', ['Admin\UserController', 'show'])->name('admin.users.show');
        $router->get('/{id}/edit', ['Admin\UserController', 'edit'])->name('admin.users.edit');
    });
    
    $router->group(['prefix' => 'posts'], function ($router) {
        $router->get('/', ['Admin\PostController', 'index'])->name('admin.posts.index');
        $router->get('/{id}', ['Admin\PostController', 'show'])->name('admin.posts.show');
    });
});
```

### Admin Navigation Menu
```php
<!-- views/admin/partials/navigation.php -->
<nav class="admin-nav">
    <ul>
        <li>
            <a href="<?= route('admin.dashboard') ?>">Dashboard</a>
        </li>
        <li>
            <a href="<?= route('admin.users.index') ?>">Users</a>
        </li>
        <li>
            <a href="<?= route('admin.posts.index') ?>">Posts</a>
        </li>
    </ul>
</nav>
```

## Example 4: Breadcrumb Navigation

### Helper Function
```php
// app/Helpers/BreadcrumbHelper.php
class BreadcrumbHelper
{
    protected $breadcrumbs = [];
    
    public function add($label, $routeName, $params = [])
    {
        $this->breadcrumbs[] = [
            'label' => $label,
            'url' => route($routeName, $params)
        ];
        return $this;
    }
    
    public function addCurrent($label)
    {
        $this->breadcrumbs[] = [
            'label' => $label,
            'url' => null // Current page, no link
        ];
        return $this;
    }
    
    public function render()
    {
        $html = '<nav aria-label="breadcrumb"><ol class="breadcrumb">';
        
        foreach ($this->breadcrumbs as $crumb) {
            if ($crumb['url'] === null) {
                $html .= '<li class="breadcrumb-item active">' . e($crumb['label']) . '</li>';
            } else {
                $html .= '<li class="breadcrumb-item">';
                $html .= '<a href="' . e($crumb['url']) . '">' . e($crumb['label']) . '</a>';
                $html .= '</li>';
            }
        }
        
        $html .= '</ol></nav>';
        return $html;
    }
}
```

### Usage in Controller
```php
// app/Controllers/PostController.php
class PostController extends BaseController
{
    public function show($id)
    {
        $post = Post::find($id);
        
        $breadcrumbs = new BreadcrumbHelper();
        $breadcrumbs
            ->add('Home', 'home')
            ->add('Posts', 'posts.index')
            ->addCurrent($post->title);
        
        return view('posts.show', [
            'post' => $post,
            'breadcrumbs' => $breadcrumbs->render()
        ]);
    }
}
```

## Example 5: Email Templates with Links

### Email Service
```php
// app/Services/EmailService.php
class EmailService
{
    public function sendPostNotification($user, $post)
    {
        $postUrl = route('posts.show', ['id' => $post->id]);
        $unsubscribeUrl = route('settings.notifications', ['userId' => $user->id]);
        
        $body = "
            <h2>New Post: {$post->title}</h2>
            <p>{$post->excerpt}</p>
            <p><a href='{$postUrl}'>Read More</a></p>
            <hr>
            <p><small>
                <a href='{$unsubscribeUrl}'>Manage notification settings</a>
            </small></p>
        ";
        
        // Send email using your mail service
        mail($user->email, "New Post: {$post->title}", $body);
    }
}
```

## Example 6: Pagination with Route Preservation

### Custom Paginator Extension
```php
// app/Helpers/PaginationHelper.php
class PaginationHelper
{
    public static function generateLinks($currentPage, $totalPages, $routeName, $routeParams = [])
    {
        $html = '<nav><ul class="pagination">';
        
        for ($page = 1; $page <= $totalPages; $page++) {
            $params = array_merge($routeParams, ['page' => $page]);
            $url = route($routeName, $params);
            $active = $page === $currentPage ? ' class="active"' : '';
            
            $html .= "<li{$active}><a href='{$url}'>{$page}</a></li>";
        }
        
        $html .= '</ul></nav>';
        return $html;
    }
}
```

### Usage in Controller
```php
// app/Controllers/PostController.php
public function index()
{
    $categoryId = $_GET['category'] ?? null;
    $page = $_GET['page'] ?? 1;
    
    // Fetch posts...
    
    $paginationLinks = PaginationHelper::generateLinks(
        $page,
        $totalPages,
        'posts.index',
        $categoryId ? ['category' => $categoryId] : []
    );
    
    return view('posts.index', [
        'posts' => $posts,
        'paginationLinks' => $paginationLinks
    ]);
}
```

## Example 7: Form Actions

### Generic Form View
```php
<!-- views/posts/form.php -->
<form 
    action="<?= isset($post) ? route('posts.update', ['id' => $post->id]) : route('posts.store') ?>" 
    method="POST"
>
    <?= csrf_field() ?>
    
    <input type="text" name="title" value="<?= e($post->title ?? '') ?>">
    <textarea name="content"><?= e($post->content ?? '') ?></textarea>
    
    <button type="submit">
        <?= isset($post) ? 'Update' : 'Create' ?> Post
    </button>
    
    <a href="<?= isset($post) ? route('posts.show', ['id' => $post->id]) : route('posts.index') ?>">
        Cancel
    </a>
</form>
```

## Example 8: Dynamic Sitemap Generation

### Sitemap Controller
```php
// app/Controllers/SitemapController.php
class SitemapController
{
    public function xml()
    {
        header('Content-Type: application/xml');
        
        $posts = Post::all();
        
        $urls = [
            ['loc' => route('home'), 'priority' => '1.0'],
            ['loc' => route('about'), 'priority' => '0.8'],
            ['loc' => route('posts.index'), 'priority' => '0.9'],
        ];
        
        foreach ($posts as $post) {
            $urls[] = [
                'loc' => route('posts.show', ['id' => $post->id]),
                'priority' => '0.7',
                'lastmod' => $post->updated_at
            ];
        }
        
        echo '<?xml version="1.0" encoding="UTF-8"?>';
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        
        foreach ($urls as $url) {
            echo '<url>';
            echo '<loc>' . htmlspecialchars($url['loc']) . '</loc>';
            echo '<priority>' . $url['priority'] . '</priority>';
            if (isset($url['lastmod'])) {
                echo '<lastmod>' . date('Y-m-d', strtotime($url['lastmod'])) . '</lastmod>';
            }
            echo '</url>';
        }
        
        echo '</urlset>';
    }
}
```

## Example 9: CLI Command with URL Generation

### CLI Command
```php
// app/Console/Commands/SendDigestCommand.php
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SendDigestCommand extends Command
{
    protected function configure()
    {
        $this->setName('digest:send')
             ->setDescription('Send daily digest email to users');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $posts = Post::where('created_at', '>=', date('Y-m-d', strtotime('-1 day')))->get();
        
        foreach (User::all() as $user) {
            $links = [];
            foreach ($posts as $post) {
                $links[] = route('posts.show', ['id' => $post->id]);
            }
            
            // Send digest email with links
            $output->writeln("Sent digest to {$user->email} with " . count($links) . " links");
        }
        
        return Command::SUCCESS;
    }
}
```

## Best Practices from Examples

1. **Always use named routes**: Makes refactoring easier
2. **Generate URLs in controllers**: Pass them to views rather than generating in views when complex logic is involved
3. **Use consistent naming**: Follow patterns like `resource.action`
4. **Include links in API responses**: Following HATEOAS principles
5. **Preserve route parameters**: When building pagination or filters
6. **Use route groups**: For organizing related routes with common prefixes
7. **Generate absolute URLs for emails**: Use route() for consistent URLs
8. **Build breadcrumbs dynamically**: Using route names for flexibility

## Conclusion

These examples demonstrate the flexibility and power of reverse route URL generation in phpLiteCore. The feature integrates seamlessly with all parts of your application, from controllers and views to CLI commands and API responses.
