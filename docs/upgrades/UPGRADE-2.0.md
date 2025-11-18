# Upgrading to 2.0 (Template for Future Use)

This guide will help you upgrade from phpLiteCore 1.x to 2.0.

> **Note**: This is a template for a future major version upgrade. Version 2.0 has not been released yet.

## Requirements

Before upgrading to 2.0, ensure your environment meets these requirements:

- PHP >= 8.3 (or updated requirement)
- Composer 2.0+
- Required PHP extensions (check documentation)

## Upgrade Steps

### 1. Backup

```bash
# Backup your database
mysqldump -u username -p database_name > backup_$(date +%Y%m%d).sql

# Backup your application files
tar -czf app_backup_$(date +%Y%m%d).tar.gz /path/to/your/app
```

### 2. Update Composer Dependencies

```json
{
  "require": {
    "muhammadadela/phplitecore": "^2.0"
  }
}
```

```bash
composer update muhammadadela/phplitecore
```

### 3. Breaking Changes

#### Removed Features

List features that were deprecated in 1.x and removed in 2.0:

- **Feature A**: Removed. Use Feature B instead.
- **Feature C**: Removed. See migration guide below.

#### Changed APIs

List API changes that may affect your code:

- **Class X**: Method `oldMethod()` renamed to `newMethod()`
- **Class Y**: Parameter order changed in `method()`

### 4. Migration Guide

#### Migrating from Feature A to Feature B

**Before (1.x):**
```php
$result = FeatureA::doSomething($param);
```

**After (2.0):**
```php
$result = FeatureB::doSomething($param);
```

#### Updating Method Calls

**Before (1.x):**
```php
$instance->oldMethod($a, $b);
```

**After (2.0):**
```php
$instance->newMethod($b, $a); // Note: parameter order changed
```

### 5. Configuration Changes

Update your configuration files:

**config/app.php:**
```php
// Old (1.x)
'option' => 'old_value',

// New (2.0)
'new_option' => 'new_value',
```

### 6. Database Migrations

Run any new migrations:

```bash
php bin/console migrate
```

### 7. Testing

After upgrading:

1. Clear all caches
2. Run your test suite
3. Manually test critical features
4. Check logs for deprecation warnings

## Troubleshooting

### Common Issues

**Issue 1: Method not found**
- **Cause**: Method was renamed or removed
- **Solution**: Check the API changes section above

**Issue 2: Configuration error**
- **Cause**: Configuration structure changed
- **Solution**: Review configuration changes section

## Rollback

If you need to rollback:

```bash
# Restore database
mysql -u username -p database_name < backup_YYYYMMDD.sql

# Restore composer dependencies
composer require muhammadadela/phplitecore:^1.0
composer update
```

## Getting Help

If you encounter issues during the upgrade:

1. Check the [CHANGELOG.md](../../CHANGELOG.md)
2. Search [GitHub Issues](https://github.com/MuhammadAdelA/phpLiteCore/issues)
3. Ask on [GitHub Discussions](https://github.com/MuhammadAdelA/phpLiteCore/discussions)
4. Open a [new issue](https://github.com/MuhammadAdelA/phpLiteCore/issues/new) with:
   - Your current version
   - The version you're upgrading to
   - Error messages
   - Steps to reproduce the issue
