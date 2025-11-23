# Changelog

All notable changes to phpLiteCore will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## Quick Links

- [Versioning Policy](docs/VERSIONING.md) - Learn about our SemVer approach and release schedule
- [Upgrade Guides](docs/upgrades/) - Step-by-step upgrade instructions
- [Latest Release](https://github.com/MuhammadAdelA/phpLiteCore/releases/latest)

## [Unreleased]

### Added
- PHP CS Fixer configuration for PSR-12 compliance
- PHPStan configuration for static analysis (level 6)
- EditorConfig for consistent coding styles
- Comprehensive SECURITY.md with best practices
- CODE_OF_CONDUCT.md for community guidelines
- GitHub issue templates (bug report, feature request, documentation)
- GitHub pull request template
- Rate limiting middleware for abuse prevention
- PSR-3 compatible Logger class with file-based storage
- File-based Cache class with TTL support
- Custom exception classes (PhpLiteCoreException, DatabaseException, AuthenticationException, ConfigurationException, FileSystemException)
- Response::tooManyRequests() method for 429 responses
- README badges for PHP version, license, tests, and documentation
- Code quality section in README

### Changed
- Improved .gitignore to include PHP CS Fixer and PHPStan cache files
- Enhanced README with code quality information and badges

### Security
- Added comprehensive security documentation
- Documented security best practices for developers

## [1.0.0] - 2024-12-01

_Initial stable release of phpLiteCore framework._

### Added
- Initial release of phpLiteCore framework
- MVC architecture with clean separation of concerns
- Hybrid Active Record pattern for database operations
- Flexible routing system with support for GET, POST, and dynamic parameters
- Built-in translation system (i18n) with EN/AR support
- Environment-aware error handling
- Input validation system
- Pagination with Bootstrap 5 renderer
- CSRF protection middleware
- Session management
- Authentication system with email/phone/username support
- Query Builder with eager loading support
- View system with composers
- Console commands for migrations, seeders, and generators
- Dependency injection container
- Asset management with Webpack and SCSS
- GitHub Actions CI/CD pipeline
- Comprehensive test suite with Pest PHP

### Features
- ⚡ Ultra-lightweight and fast
- 🧩 Clean architecture (PSR-12 compliant)
- 🧱 Hybrid Active Record for database interactions
- 🛣️ Flexible routing
- 🌍 Built-in translation (i18n)
- 🛡️ Environment-aware error handling
- 📦 Asset management ready
- ✔️ Input validation
- 📄 Pagination
- 🛠️ Extensible design

[Unreleased]: https://github.com/MuhammadAdelA/phpLiteCore/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/MuhammadAdelA/phpLiteCore/releases/tag/v1.0.0
