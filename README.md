# phpLiteCore PHP Framework

[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D8.3-blue.svg)](https://www.php.net/)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)
[![Tests](https://github.com/MuhammadAdelA/phpLiteCore/workflows/PHP%20Code%20Quality%20Checks/badge.svg)](https://github.com/MuhammadAdelA/phpLiteCore/actions)
[![Documentation](https://img.shields.io/badge/docs-online-brightgreen.svg)](https://muhammadadela.github.io/phpLiteCore/)

**phpLiteCore** is a modern, lightweight, and fast PHP framework designed for building web applications of any size. It focuses on simplicity, speed, and a clean architecture, providing core essentials without unnecessary bloat.

---

## ✨ Features

* ⚡ **Ultra-lightweight and Fast:** Minimal core for optimal performance.
* 🧩 **Clean Architecture:** Adheres to MVC principles with strict separation of concerns.
* 🧱 **Hybrid Active Record:** Simplifies database interactions (Querying & Manipulation).
* 🛣️ **Flexible Routing:** Supports GET, POST, dynamic route parameters, named routes, and reverse URL generation.
* 🌍 **Built-in Translation (i18n):** Modular system supporting multiple languages (EN/AR included).
* 🛡️ **Environment-Aware Error Handling:** Detailed errors in development, user-friendly messages & developer notifications (SMTP) in production.
* 📦 **Asset Management Ready:** Integrated with NPM, Webpack, and SCSS for easy frontend workflows.
* ✔️ **Input Validation:** Simple, integrated validation system.
* 📄 **Pagination:** Built-in pagination logic and renderers (Bootstrap 5).
* 🛠️ **Extensible:** Designed to be easily extended with custom components.

---

## 🚀 Getting Started

1.  **Clone the repository:**
    ```bash
    git clone [https://github.com/MuhammadAdelA/phpLiteCore.git](https://github.com/MuhammadAdelA/phpLiteCore.git)
    cd phpLiteCore
    ```

2.  **Install Dependencies:**
    ```bash
    # Install PHP dependencies
    composer install

    # Install Node.js dependencies and build assets
    npm install
    npm run build # or 'npm run dev' for development
    ```

3.  **Configure Environment:**
    * Copy `.env.example` to `.env`.
    * Update `.env` with your database credentials, SMTP settings (for production error reporting), and `APP_ENV` (`development` or `production`).

4.  **Set Up Database:**
    * Import the `phplitecore.sql` file into your MySQL database.

5.  **Configure Web Server:**
    * Point your web server's document root to the **project root directory** (where `index.php` and `.htaccess` reside).
    * Ensure `mod_rewrite` (or equivalent for your server) is enabled.

6.  **Run:**
    * Open the project URL in your browser. You should see the welcome page!

---

## 📖 Documentation

**View the live documentation:**

**[https://muhammadadela.github.io/phpLiteCore/](https://muhammadadela.github.io/phpLiteCore/)**

*(Includes guides for both English and Arabic, covering core concepts, routing, database interaction, translation, and more.)*

**Network Configuration:**

If you need to configure network access for GitHub Actions or work behind firewalls, see the **[Network Configuration Guide](NETWORK_CONFIGURATION.md)** for detailed setup instructions.

---

## 📦 Versioning

phpLiteCore follows [Semantic Versioning 2.0.0](https://semver.org/) (SemVer). We maintain a detailed [CHANGELOG.md](CHANGELOG.md) documenting all notable changes for each release.

### Version Support

- **Active Support**: 12 months (bug fixes, security patches, new features)
- **Security Support**: Additional 12 months (security patches only)
- **Total Support**: 24 months per major version

### Current Version

Check the [latest release](https://github.com/MuhammadAdelA/phpLiteCore/releases) for the current stable version.

**Learn More:**
- [Versioning Policy](docs/VERSIONING.md) - Complete versioning and release policy
- [CHANGELOG](CHANGELOG.md) - Detailed change history for all versions
- [Upgrade Guides](docs/upgrades/) - Step-by-step upgrade instructions

---

## 🛡️ Security

Security is a top priority for phpLiteCore. If you discover a security vulnerability, please review our [Security Policy](SECURITY.md) for responsible disclosure guidelines.

---

## 🧪 Code Quality

phpLiteCore maintains high code quality standards:

- **PSR-12 Compliant**: All code follows PSR-12 coding standards
- **Static Analysis**: Uses PHPStan for type safety
- **Automated Tests**: Comprehensive test suite with Pest PHP
- **Code Style**: Automated formatting with PHP CS Fixer

### Running Quality Checks

```bash
# Run tests
./vendor/bin/pest

# Run static analysis (requires PHPStan)
composer require --dev phpstan/phpstan
./vendor/bin/phpstan analyse

# Fix code style (requires PHP CS Fixer)
composer require --dev friendsofphp/php-cs-fixer
./vendor/bin/php-cs-fixer fix
```

---

## 🤝 Contributing

Contributions are welcome! Please read the [CONTRIBUTING.md](CONTRIBUTING.md) guide and our [Code of Conduct](CODE_OF_CONDUCT.md).

---

## 📜 License

phpLiteCore is open-source software licensed under the [MIT license](LICENSE).