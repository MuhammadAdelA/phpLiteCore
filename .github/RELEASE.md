# Release Process

This document outlines the process for creating a new release of phpLiteCore.

## Pre-Release Checklist

Before creating a release, ensure:

- [ ] All tests pass (`composer test`)
- [ ] Code quality checks pass (`composer quality`)
- [ ] Documentation is up to date
- [ ] CHANGELOG.md is updated with all changes
- [ ] Version numbers are updated in relevant files
- [ ] Security vulnerabilities are addressed
- [ ] Breaking changes are documented

## Versioning

phpLiteCore follows [Semantic Versioning](https://semver.org/):

- **MAJOR** (X.0.0): Breaking changes
- **MINOR** (1.X.0): New features, backward compatible
- **PATCH** (1.0.X): Bug fixes, backward compatible

## Release Steps

### 1. Update CHANGELOG.md

Move changes from `[Unreleased]` to a new version section:

```markdown
## [1.2.0] - 2024-12-01

### Added
- New feature X
- New feature Y

### Fixed
- Bug fix A
- Bug fix B
```

### 2. Update Version References

Update version numbers in:
- `composer.json` (if applicable)
- Documentation files that reference specific versions
- README.md (if showing current version)

### 3. Commit Changes

```bash
git add .
git commit -m "chore: prepare release v1.2.0"
git push origin main
```

### 4. Create Git Tag

```bash
# Create annotated tag
git tag -a v1.2.0 -m "Release version 1.2.0"

# Push tag to GitHub
git push origin v1.2.0
```

### 5. Create GitHub Release

1. Go to [GitHub Releases](https://github.com/MuhammadAdelA/phpLiteCore/releases)
2. Click "Draft a new release"
3. Select the tag you just created (v1.2.0)
4. Fill in the release details using the template below

### 6. Publish to Packagist

Packagist should automatically detect the new tag and update the package.

If needed, manually trigger an update:
1. Go to https://packagist.org/packages/muhammadadela/phplitecore
2. Click "Update"

## GitHub Release Template

Use this template when creating a GitHub release:

```markdown
# phpLiteCore v1.2.0

[Brief description of this release - what's the main focus or theme]

## 🎉 Highlights

- Major feature or change 1
- Major feature or change 2
- Major feature or change 3

## ✨ Added

- New feature A (#123)
- New feature B (#124)

## 🔧 Changed

- Improvement C (#125)
- Improvement D (#126)

## 🐛 Fixed

- Bug fix E (#127)
- Bug fix F (#128)

## 🔒 Security

- Security fix G (#129)

## 📚 Documentation

- Documentation update H (#130)

## 🔄 Upgrade Guide

### From 1.1.x to 1.2.0

This is a **minor version** release with new features and backward compatibility.

**Steps:**
1. Update your `composer.json`: `"muhammadadela/phplitecore": "^1.2.0"`
2. Run: `composer update muhammadadela/phplitecore`
3. Review new features in the [CHANGELOG](https://github.com/MuhammadAdelA/phpLiteCore/blob/main/CHANGELOG.md)

**No breaking changes** - Your existing code will continue to work.

For major version upgrades, see the [Upgrade Guides](https://github.com/MuhammadAdelA/phpLiteCore/tree/main/docs/upgrades).

## 📦 Installation

### New Projects

```bash
composer require muhammadadela/phplitecore:^1.2.0
```

### Existing Projects

```bash
composer update muhammadadela/phplitecore
```

## 📖 Resources

- **Documentation**: https://muhammadadela.github.io/phpLiteCore/
- **CHANGELOG**: https://github.com/MuhammadAdelA/phpLiteCore/blob/main/CHANGELOG.md
- **Versioning Policy**: https://github.com/MuhammadAdelA/phpLiteCore/blob/main/docs/VERSIONING.md
- **Upgrade Guides**: https://github.com/MuhammadAdelA/phpLiteCore/tree/main/docs/upgrades

## 🙏 Contributors

Thank you to everyone who contributed to this release!

[List contributors if applicable]

## 📝 Full Changelog

See the complete list of changes: https://github.com/MuhammadAdelA/phpLiteCore/blob/main/CHANGELOG.md#120---2024-12-01
```

## Post-Release

After releasing:

1. [ ] Verify the release appears on GitHub
2. [ ] Verify Packagist shows the new version
3. [ ] Announce the release (if major/minor version)
   - Social media
   - Discussion forums
   - Project newsletter
4. [ ] Create a new `[Unreleased]` section in CHANGELOG.md for future changes
5. [ ] Close any milestone associated with this release

## Rolling Back a Release

If you need to rollback a release:

1. **Do NOT delete the tag** - This can break installations
2. Instead, create a new patch version that reverts changes
3. Document the issue in CHANGELOG.md
4. Create a new release with the fix

## Pre-Release Versions

For alpha, beta, or RC versions:

### Alpha (Early Development)
```bash
git tag -a v2.0.0-alpha.1 -m "Release version 2.0.0-alpha.1"
```

### Beta (Feature Complete)
```bash
git tag -a v2.0.0-beta.1 -m "Release version 2.0.0-beta.1"
```

### Release Candidate (Final Testing)
```bash
git tag -a v2.0.0-rc.1 -m "Release version 2.0.0-rc.1"
```

Mark pre-release versions as "Pre-release" on GitHub.

## Automation (Future Enhancement)

Consider automating releases with GitHub Actions:
- Automatic CHANGELOG generation
- Automated testing before release
- Automatic GitHub release creation
- Packagist notification

## Questions?

For questions about the release process, contact the maintainers or open a discussion on GitHub.
