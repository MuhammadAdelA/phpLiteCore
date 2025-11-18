# CLI Commands Reference

phpLiteCore provides a powerful command-line interface (CLI) built with Symfony Console. This guide documents all available commands and their usage.

## Getting Started

All CLI commands are executed through the `bin/console` script:

```bash
php bin/console [command] [options] [arguments]
```

To see all available commands:

```bash
php bin/console list
```

To get help for a specific command:

```bash
php bin/console help [command]
```

---

## Route Management Commands

### route:list

Display all registered routes with their details including HTTP methods, URIs, names, controller actions, and middleware.

**Usage:**
```bash
php bin/console route:list
```

**Output Example:**
```
+--------+------------------+--------------+-----------------------+------------+
| Method | URI              | Name         | Action                | Middleware |
+--------+------------------+--------------+-----------------------+------------+
| GET    | /                | home         | HomeController@index  | -          |
| GET    | /about           | about        | AboutController@index | -          |
| GET    | /posts           | posts.index  | PostController@index  | -          |
| GET    | /posts/create    | posts.create | PostController@create | -          |
| GET    | /posts/{id}      | posts.show   | PostController@show   | -          |
| POST   | /posts           | posts.store  | PostController@store  | CsrfMiddle |
+--------+------------------+--------------+-----------------------+------------+

Total routes: 6
```

**Use Cases:**
- Debugging route registration issues
- Documentation and API overview
- Identifying route conflicts
- Understanding application structure

---

### route:cache

Cache all registered routes to a PHP file for faster route loading in production. This eliminates the need to parse route definitions on every request.

**Usage:**
```bash
php bin/console route:cache
```

**Output:**
```
Routes cached successfully!
Cache file: /path/to/storage/cache/routes.php
```

**Benefits:**
- Significantly improves application performance in production
- Reduces overhead of route registration
- Essential for deployment workflows

**Important Notes:**
- After adding or modifying routes, you must run `route:cache` again
- Cached routes are stored in `storage/cache/routes.php`
- Use `route:clear` to remove the cache

**Production Workflow:**
```bash
# After deploying new code with route changes
php bin/console route:clear
php bin/console route:cache
```

---

### route:clear

Remove the route cache file. This forces the application to load routes from the source files on the next request.

**Usage:**
```bash
php bin/console route:clear
```

**Output:**
```
Route cache cleared successfully!
```

**When to Use:**
- During development when routes are being modified
- When troubleshooting route-related issues
- Before caching routes again after changes

---

## Configuration Management Commands

### config:cache

Cache configuration from the `.env` file to a PHP file for faster configuration loading. This is particularly useful in production environments.

**Usage:**
```bash
php bin/console config:cache
```

**Output:**
```
Configuration cached successfully!
Cache file: /path/to/storage/cache/config.php
```

**What Gets Cached:**
- All environment variables from `.env` file
- Configuration values parsed and ready for use
- Timestamp of when cache was created

**Benefits:**
- Reduces file I/O operations
- Improves application bootstrap time
- Essential for production deployments

**Important Notes:**
- After modifying `.env`, you must run `config:cache` again
- Cached config is stored in `storage/cache/config.php`
- Use `config:clear` to remove the cache

**Production Workflow:**
```bash
# After updating .env file
php bin/console config:clear
php bin/console config:cache
```

---

### config:clear

Remove the configuration cache file. This forces the application to load configuration from the `.env` file on the next request.

**Usage:**
```bash
php bin/console config:clear
```

**Output:**
```
Configuration cache cleared successfully!
```

**When to Use:**
- During development when configuration is being modified
- When troubleshooting configuration-related issues
- Before caching configuration again after changes

---

## Database Management Commands

### migrate

Apply all pending database migrations. Migrations are executed in order based on their version numbers.

**Usage:**
```bash
php bin/console migrate
```

**Output:**
```
Applied: 20240101000000_create_users_table.php
Applied: 20240101000001_create_posts_table.php
```

**Notes:**
- Migrations are located in `database/migrations/`
- Already applied migrations are tracked and won't run again
- Use migration files to version control your database schema

---

### migrate:rollback

Rollback the last batch of database migrations.

**Usage:**
```bash
php bin/console migrate:rollback
```

**Output:**
```
Rolled back: 20240101000001_create_posts_table.php
```

**Use Cases:**
- Reverting recent database changes
- Testing migration down methods
- Recovery from failed migrations

---

### seed

Seed the database with sample data using database seeders.

**Usage:**
```bash
php bin/console seed
```

**Notes:**
- Seeders are located in `database/seeders/`
- Useful for development and testing environments
- Can populate database with test data

---

## Code Generation Commands

### make:migration

Create a new database migration file with a timestamped filename.

**Usage:**
```bash
php bin/console make:migration create_products_table
```

**Output:**
```
Created migration: database/migrations/20240115123456_create_products_table.php
```

**Generated File Structure:**
```php
<?php
return new class {
    public function up($db) {
        // Add your schema changes here
    }
    
    public function down($db) {
        // Add your rollback logic here
    }
};
```

---

### make:model

Create a new model class in the `app/Models` directory.

**Usage:**
```bash
php bin/console make:model Product
```

**Output:**
```
Created model: app/Models/Product.php
```

**Generated File:**
```php
<?php
declare(strict_types=1);

namespace App\Models;

use PhpLiteCore\Database\Model\BaseModel;

final class Product extends BaseModel
{
    // protected string $table = '...'; // optionally override
}
```

---

### make:controller

Create a new controller class in the `app/Controllers` directory.

**Usage:**
```bash
php bin/console make:controller ProductController
```

**Output:**
```
Created controller: app/Controllers/ProductController.php
```

**Generated File:**
```php
<?php
declare(strict_types=1);

namespace App\Controllers;

use PhpLiteCore\Bootstrap\Application;

final class ProductController extends BaseController
{
    public function index(): void
    {
        echo '<h1>ProductController works</h1>';
    }
}
```

---

## Best Practices

### Development Environment

During development, keep caches cleared for faster iteration:

```bash
php bin/console route:clear
php bin/console config:clear
```

### Production Environment

Always cache routes and configuration in production for optimal performance:

```bash
# During deployment
php bin/console route:cache
php bin/console config:cache
```

### CI/CD Integration

Example deployment script:

```bash
#!/bin/bash
# Deploy script example

# Pull latest code
git pull origin main

# Install dependencies
composer install --no-dev --optimize-autoloader

# Clear old caches
php bin/console route:clear
php bin/console config:clear

# Run migrations
php bin/console migrate

# Cache for production
php bin/console route:cache
php bin/console config:cache

# Restart services (if needed)
# sudo systemctl restart php-fpm
```

---

## Troubleshooting

### Command Not Found

If you get "command not found" errors, ensure:
1. You're in the project root directory
2. PHP is in your PATH
3. The `bin/console` file has execute permissions: `chmod +x bin/console`

### Permission Errors

If you encounter permission errors when caching:
```bash
# Ensure storage directories are writable
chmod -R 775 storage/cache
```

### Cache Not Taking Effect

If cached data isn't being used:
1. Verify cache files exist in `storage/cache/`
2. Check file permissions
3. Ensure your application is loading from cache (check bootstrap code)

---

## Contributing

When adding new CLI commands:

1. Create command class in `src/Console/Commands/`
2. Extend `Symfony\Component\Console\Command\Command`
3. Implement `configure()` and `execute()` methods
4. Register in `src/Console/Kernel.php`
5. Add comprehensive tests in `tests/Unit/Console/`
6. Update this documentation

Example command structure:

```php
<?php
declare(strict_types=1);

namespace PhpLiteCore\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class YourCommand extends Command
{
    public function __construct()
    {
        parent::__construct('namespace:command');
    }

    protected function configure(): void
    {
        $this->setDescription('Your command description');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Command logic here
        
        return Command::SUCCESS;
    }
}
```

---

## Additional Resources

- [Symfony Console Documentation](https://symfony.com/doc/current/console.html)
- [phpLiteCore Main Documentation](https://muhammadadela.github.io/phpLiteCore/)
- [GitHub Repository](https://github.com/MuhammadAdelA/phpLiteCore)
