# Contributing to phpLiteCore

First off, thank you for considering contributing to phpLiteCore! Your help is valuable in making this project better.

phpLiteCore is a lightweight PHP framework focused on speed, simplicity, and clean architecture. We welcome contributions, whether it's fixing bugs, adding features, improving documentation, or suggesting ideas.

---

## 🚀 How to Contribute

Follow these steps to contribute:

1.  **Fork the repository**
    Click the "Fork" button at the top right of the GitHub page.

2.  **Clone your forked repo**:
    ```bash
    git clone [https://github.com/your-username/phpLiteCore.git](https://github.com/your-username/phpLiteCore.git)
    cd phpLiteCore
    ```

3.  **Create a new branch** (use descriptive names):
    ```bash
    # For features:
    git checkout -b feature/your-feature-name

    # For bug fixes:
    git checkout -b fix/bug-description
    ```

4.  **Make your changes**, following the guidelines below.

5.  **Commit your changes** with a clear commit message:
    ```bash
    git add .
    git commit -m "Feat: Add feature X"
    # or
    git commit -m "Fix: Resolve issue Y in Z component"
    # or
    git commit -m "Docs: Update documentation for ABC"
    ```

6.  **Push the branch** to your fork:
    ```bash
    git push origin your-branch-name
    ```

7.  **Open a Pull Request (PR)**
    Go to the main `MuhammadAdelA/phpLiteCore` repository on GitHub. Click "New Pull Request" and choose your fork and branch to compare. Provide a clear description of your changes in the PR.

---

## ✅ Code Style Guidelines

Please adhere to these conventions:

* **PSR-12:** Follow the PSR-12 coding standard for PHP.
* **Naming:**
    * Use **camelCase** for variables and functions/methods (`$myVariable`, `getUserData()`).
    * Use **PascalCase** for class names (`MyClass`, `PostController`).
* **Readability:** Separate logic into small, focused functions/methods.
* **Comments:** Use PHPDoc blocks (`/** ... */`) for classes and methods. Use inline comments (`// ...`) to explain complex or non-obvious logic sections. Comments should be in **English**.

---

## 🗂️ Folder Structure Guide

To maintain consistency, please place files according to this structure (based on the project constitution):

* `app/` → Application-specific code:
    * `app/Controllers/` → Request handling logic.
    * `app/Models/` → Database table representations (extend `BaseModel`).
    * `app/ViewComposers/` → Logic for injecting data into specific layouts/views.
* `src/` → Core framework code (Namespaced under `PhpLiteCore`). Subdirectories like `Database`, `Routing`, `Lang`, `View`, etc.
* `routes/` → Route definitions (`web.php`).
* `public/` → Publicly accessible web root **ONLY** for built assets.
    * `public/assets/` → Compiled CSS and JS files from Webpack.
* `resources/` → Source files before compilation:
    * `resources/js/` → Source JavaScript.
    * `resources/scss/` → Source SCSS.
    * `resources/lang/` → Language translation files.
* `views/` → Template files:
    * `views/layouts/` → Main layout files (e.g., `app.php`).
    * `views/partials/` → Reusable template snippets (e.g., `header.php`).
    * `views/themes/default/` → Application-specific view files.
    * `views/system/` → Default framework error pages.
* `storage/` → Writable directories for logs, cache (if implemented), etc.
* `tests/` → Automated tests (PHPUnit/Pest).

---

## 🛡 Security Guidelines

Security is paramount:

* **Sanitize & Validate:** Always validate incoming data (`src/Validation/Validator.php`) and sanitize output (e.g., use `htmlspecialchars()` in views).
* **Database:** The Active Record / Query Builder uses PDO prepared statements internally, protecting against basic SQL injection when used correctly. Be cautious with raw queries (`$app->db->raw()`).
* **Cross-Site Scripting (XSS):** Always escape output in views using `htmlspecialchars()`.
* **Cross-Site Request Forgery (CSRF):** *(Note: CSRF protection is not yet implemented in the core - consider adding it)*.

---

## 🧪 Testing & Validation

* If your change affects core functionality, consider adding a unit or feature test in the `tests/` directory.
* Run existing tests if applicable.
* Manually test your changes thoroughly in a development environment.
* When fixing a bug, explain the original issue and how your fix resolves it in the PR description.

You can run basic PHP syntax checks locally:
```bash
# Find all PHP files and check syntax
find . -name "*.php" -exec php -l {} \; | grep "Errors parsing"