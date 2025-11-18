# phpLiteCore PHP Framework

[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D8.3-blue.svg)](https://www.php.net/)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)
[![CI](https://github.com/MuhammadAdelA/phpLiteCore/workflows/PHP%20Code%20Quality%20Checks/badge.svg)](https://github.com/MuhammadAdelA/phpLiteCore/actions)
[![PHPStan](https://img.shields.io/badge/PHPStan-level%206-brightgreen.svg)](phpstan.neon)
[![Code Style](https://img.shields.io/badge/code%20style-PSR--12-blue.svg)](https://www.php-fig.org/psr/psr-12/)
[![Codecov](https://codecov.io/gh/MuhammadAdelA/phpLiteCore/branch/master/graph/badge.svg)](https://codecov.io/gh/MuhammadAdelA/phpLiteCore)
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

## 🛡️ Security

Security is a top priority for phpLiteCore. If you discover a security vulnerability, please review our [Security Policy](SECURITY.md) for responsible disclosure guidelines.

---

## 🧪 Code Quality

phpLiteCore maintains high code quality standards with automated CI checks:

- **PSR-12 Compliant**: All code follows PSR-12 coding standards
- **Static Analysis**: Uses PHPStan level 6 for type safety and code correctness
- **Automated Tests**: Comprehensive test suite with Pest PHP
- **Code Style**: Automated formatting with PHP CS Fixer
- **CI/CD**: All PRs and pushes are automatically checked for quality

### Running Quality Checks Locally

```bash
# Install dev dependencies (if not already installed)
composer install

# Run all quality checks at once
composer quality

# Or run individually:
composer test              # Run Pest tests
composer test:coverage     # Run tests with coverage report
composer analyse           # Run PHPStan static analysis
composer format:check      # Check code style without fixing
composer format            # Fix code style issues
composer lint              # Check PHP syntax
```

All these checks run automatically on every pull request and push to ensure code quality.
```

---

## 🤝 Contributing

Contributions are welcome! Please read the [CONTRIBUTING.md](CONTRIBUTING.md) guide and our [Code of Conduct](CODE_OF_CONDUCT.md).

---

## 📜 License

phpLiteCore is open-source software licensed under the [MIT license](LICENSE).