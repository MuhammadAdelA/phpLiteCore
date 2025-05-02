# Contributing to LiteCore

First off, thank you for taking the time to contribute to LiteCore — your help is what makes this project better for everyone!

LiteCore is a lightweight PHP framework focused on speed, simplicity, and clean architecture. Whether you're fixing a bug, adding a feature, or improving documentation — you're welcome!

---

## 🚀 How to Contribute

Follow these steps to contribute:

1. **Fork the repository**  
   Click the "Fork" button at the top right of the GitHub page.

2. **Clone your forked repo**:
   ```bash
   git clone https://github.com/your-username/LiteCore.git
   cd LiteCore
   ```

3. **Create a new branch**:
   ```bash
   git checkout -b feature/your-feature-name
   ```

4. **Make your changes**, then commit:
   ```bash
   git add .
   git commit -m "Add: short description of your change"
   ```

5. **Push the branch**:
   ```bash
   git push origin feature/your-feature-name
   ```

6. **Open a Pull Request**  
   Go to GitHub, open your fork, and click "Compare & Pull Request".

---

## ✅ Code Style Guidelines

Please follow these conventions:

- Use **PSR-12** coding standards.
- Use **camelCase** for variables and functions.
- Use **PascalCase** for class names.
- Separate logic into small, reusable functions.
- Document complex logic using inline comments (`//`).

---

## 🗂️ Folder Structure Guide

To keep the project consistent:

- `core/` → Core framework classes
- `app/` → Controllers, Models, Views
- `routes/` → Route definitions
- `public/` → Entry point (e.g. `index.php`)
- `storage/` → Cache, logs, etc.

---

## 🛡 Security Guidelines

- Always **sanitize inputs**.
- Never trust client-side data.
- Use **prepared statements** for SQL queries.

---

## 🧪 Testing & Validation

Please test your changes before submitting. If you're fixing a bug, explain:
- What was the issue?
- How did your fix resolve it?

You can run PHP syntax checks locally:
```bash
find . -type f -name "*.php" -exec php -l {} \;
```

---

## 🤝 Need Help?

- Open an issue on GitHub
- Join the community (coming soon)

---

Thanks again for contributing to LiteCore!
