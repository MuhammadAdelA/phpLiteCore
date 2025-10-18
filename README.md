# phpLiteCore PHP Framework

**phpLiteCore** is a modern, lightweight, and fast PHP framework designed for building web applications of any size. It focuses on simplicity, speed, and a clean architecture, providing core essentials without unnecessary bloat.

---

## ✨ Features

* ⚡ **Ultra-lightweight and Fast:** Minimal core for optimal performance.
* 🧩 **Clean Architecture:** Adheres to MVC principles with strict separation of concerns.
* 🧱 **Hybrid Active Record:** Simplifies database interactions (Querying & Manipulation).
* 🛣️ **Flexible Routing:** Supports GET, POST, and dynamic route parameters.
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

* *(Coming Soon - Link to the generated documentation HTML files)*

---

## 🤝 Contributing

Contributions are welcome! Please read the [CONTRIBUTING.md](CONTRIBUTING.md) guide.

---

## 📜 License

phpLiteCore is open-source software licensed under the [MIT license](LICENSE).