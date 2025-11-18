# Versioning and Release Policy

phpLiteCore follows [Semantic Versioning 2.0.0](https://semver.org/) (SemVer) for all releases.

## Table of Contents

- [Semantic Versioning](#semantic-versioning)
- [Version Numbers](#version-numbers)
- [Release Schedule](#release-schedule)
- [Version Support](#version-support)
- [Upgrade Paths](#upgrade-paths)
- [Breaking Changes](#breaking-changes)
- [Deprecation Policy](#deprecation-policy)
- [Release Process](#release-process)

## Semantic Versioning

Given a version number `MAJOR.MINOR.PATCH` (e.g., `2.3.1`), we increment:

- **MAJOR** version when making incompatible API changes
- **MINOR** version when adding functionality in a backward-compatible manner
- **PATCH** version when making backward-compatible bug fixes

Additional labels for pre-release and build metadata are available as extensions to the MAJOR.MINOR.PATCH format:

- **Pre-release**: `1.0.0-alpha`, `1.0.0-beta.1`, `1.0.0-rc.1`
- **Build metadata**: `1.0.0+20240101`

## Version Numbers

### Major Versions (X.0.0)

Major versions include breaking changes that may require code modifications when upgrading. These releases:

- May remove deprecated features
- May change core APIs or behaviors
- May require migration steps
- Will be documented with upgrade guides

**Example**: `1.0.0` → `2.0.0`

### Minor Versions (1.X.0)

Minor versions add new features while maintaining backward compatibility. These releases:

- Add new functionality
- Deprecate (but don't remove) existing features
- Improve performance
- Are fully backward compatible with the same major version

**Example**: `1.0.0` → `1.1.0`

### Patch Versions (1.0.X)

Patch versions fix bugs and security issues without adding features. These releases:

- Fix bugs
- Address security vulnerabilities
- Improve documentation
- Are fully backward compatible with the same minor version

**Example**: `1.0.0` → `1.0.1`

## Release Schedule

phpLiteCore follows a flexible release schedule:

- **Major releases**: As needed, typically annually
- **Minor releases**: As needed, typically every 2-3 months
- **Patch releases**: As needed for critical bugs and security issues

Security patches are released as soon as possible after a vulnerability is confirmed.

## Version Support

### Long-Term Support (LTS)

- Each major version receives **active support** for 12 months
- After active support ends, versions receive **security fixes only** for an additional 12 months
- Total support lifecycle: **24 months** per major version

### Support Phases

| Phase | Duration | Updates Provided |
|-------|----------|-----------------|
| Active Support | 12 months | Bug fixes, security patches, new features (minor versions) |
| Security Support | 12 months | Security patches only |
| End of Life | After 24 months | No updates |

### Current Supported Versions

| Version | Release Date | Active Support Until | Security Support Until | Status |
|---------|--------------|---------------------|------------------------|--------|
| 1.x | TBD | TBD | TBD | In Development |

## Upgrade Paths

### Upgrading Between Patch Versions

Patch version upgrades (e.g., `1.0.0` → `1.0.1`) are straightforward:

```bash
composer update muhammadadela/phplitecore
```

No code changes should be required.

### Upgrading Between Minor Versions

Minor version upgrades (e.g., `1.0.0` → `1.1.0`) are designed to be backward compatible:

1. Update your `composer.json`:
   ```json
   {
     "require": {
       "muhammadadela/phplitecore": "^1.1.0"
     }
   }
   ```

2. Run composer update:
   ```bash
   composer update muhammadadela/phplitecore
   ```

3. Review the [CHANGELOG.md](../CHANGELOG.md) for new features and deprecations
4. Update your code to use new features (optional)
5. Test your application thoroughly

### Upgrading Between Major Versions

Major version upgrades (e.g., `1.x` → `2.x`) may require code changes:

1. Read the upgrade guide for the specific version (see `docs/upgrades/`)
2. Review breaking changes in [CHANGELOG.md](../CHANGELOG.md)
3. Update your `composer.json` to the new major version
4. Run composer update
5. Follow the migration steps in the upgrade guide
6. Update deprecated code
7. Test your application thoroughly

**Note**: Always back up your application and database before performing major version upgrades.

## Breaking Changes

We minimize breaking changes and provide advance notice when they're necessary:

1. **Deprecation Notice**: Features are marked as deprecated in a minor release
2. **Deprecation Period**: Deprecated features remain functional for at least one full minor version cycle
3. **Removal**: Deprecated features are removed in the next major version

### Example Timeline

- `v1.0.0`: Feature X is introduced
- `v1.5.0`: Feature X is deprecated, Feature Y is introduced as replacement
- `v1.6.0`: Feature X remains deprecated (still functional)
- `v2.0.0`: Feature X is removed

## Deprecation Policy

### Marking Deprecations

Deprecated features are:

- Marked with `@deprecated` annotations in code
- Documented in the CHANGELOG under "Deprecated" section
- May trigger warnings in development mode
- Listed in the upgrade guide

### Using Deprecated Features

While deprecated features still work, we strongly recommend:

1. Avoiding their use in new code
2. Updating existing code when practical
3. Following the recommended alternatives in documentation

## Release Process

### Pre-release Versions

Before stable releases, we may publish pre-release versions:

- **Alpha** (`1.0.0-alpha.1`): Early development, APIs may change
- **Beta** (`1.0.0-beta.1`): Feature-complete, may have bugs
- **Release Candidate** (`1.0.0-rc.1`): Stable, pending final testing

### Release Checklist

Each release follows this process:

1. ✅ All tests pass
2. ✅ Code quality checks pass (PHPStan, PHP CS Fixer)
3. ✅ Documentation is updated
4. ✅ CHANGELOG.md is updated
5. ✅ Version number is bumped in relevant files
6. ✅ Git tag is created (e.g., `v1.0.0`)
7. ✅ Release notes are published on GitHub
8. ✅ Package is published to Packagist

### CHANGELOG Format

We follow [Keep a Changelog](https://keepachangelog.com/) format with these sections:

- **Added**: New features
- **Changed**: Changes in existing functionality
- **Deprecated**: Soon-to-be removed features
- **Removed**: Removed features
- **Fixed**: Bug fixes
- **Security**: Security fixes

## API Stability Promise

phpLiteCore provides API stability guarantees:

### Stable APIs

These components are considered stable and will not break within the same major version:

- Core classes and interfaces in `PhpLiteCore\` namespace
- Public methods and properties
- Configuration file structures
- Database schema (migrations only add, never remove)

### Unstable APIs

These components may change in minor versions:

- Classes/methods marked with `@internal`
- Experimental features (clearly marked in documentation)
- Development tools and helpers

### Semantic Versioning Exceptions

We reserve the right to make backward-incompatible changes in patch versions for:

- Critical security fixes
- Bug fixes where the current behavior is clearly incorrect
- Changes to undocumented internal implementation details

Such changes will be clearly documented in the CHANGELOG.

## Version Constraints

When requiring phpLiteCore in your `composer.json`, use these version constraints:

```json
{
  "require": {
    "muhammadadela/phplitecore": "^1.0"
  }
}
```

This uses the caret (`^`) operator which:
- Allows updates to `1.x.x` (minor and patch versions)
- Does not allow updates to `2.0.0` (major version)
- Ensures you receive bug fixes and new features without breaking changes

### Other Constraint Options

- **Tilde (`~`)**: `~1.5` allows `1.5.x` but not `1.6.0`
- **Exact**: `1.5.2` locks to a specific version
- **Wildcard**: `1.*` allows any `1.x` version

## Resources

- [CHANGELOG.md](../CHANGELOG.md) - Detailed change history
- [Semantic Versioning Specification](https://semver.org/)
- [Keep a Changelog](https://keepachangelog.com/)
- [Packagist Package](https://packagist.org/packages/muhammadadela/phplitecore)

## Questions?

If you have questions about versioning or need help with upgrades, please:

- Check the [CHANGELOG.md](../CHANGELOG.md)
- Review the upgrade guides in `docs/upgrades/`
- Open an issue on [GitHub](https://github.com/MuhammadAdelA/phpLiteCore/issues)
- Join our community discussions
