# Upgrade Guides

This directory contains version-specific upgrade guides for phpLiteCore.

## Available Guides

- [Upgrading to 2.0](UPGRADE-2.0.md) - _(Coming in the future)_

## General Upgrade Process

### For Patch Versions (e.g., 1.0.0 → 1.0.1)

```bash
composer update muhammadadela/phplitecore
```

No code changes required.

### For Minor Versions (e.g., 1.0.0 → 1.1.0)

1. Update your `composer.json`:
   ```json
   {
     "require": {
       "muhammadadela/phplitecore": "^1.1.0"
     }
   }
   ```

2. Run:
   ```bash
   composer update muhammadadela/phplitecore
   ```

3. Review the [CHANGELOG.md](../../CHANGELOG.md) for new features
4. Test your application

### For Major Versions (e.g., 1.x → 2.x)

Follow the specific upgrade guide for the version you're upgrading to (linked above).

## Before Upgrading

Always:

1. **Back up your database** and application files
2. Review the [CHANGELOG.md](../../CHANGELOG.md) for breaking changes
3. Check the specific version upgrade guide (if available)
4. Test in a development environment first
5. Update your dependencies with `composer update`
6. Run your test suite
7. Review deprecation warnings

## Need Help?

- Check the [CHANGELOG.md](../../CHANGELOG.md)
- Read the [Versioning Policy](../VERSIONING.md)
- Search [existing issues](https://github.com/MuhammadAdelA/phpLiteCore/issues)
- Open a [new issue](https://github.com/MuhammadAdelA/phpLiteCore/issues/new) if you need assistance
