# Security Policy

## Supported Versions

We release patches for security vulnerabilities. Which versions are eligible for receiving such patches depends on the CVSS v3.0 Rating:

| Version | Supported          |
| ------- | ------------------ |
| 1.x     | :white_check_mark: |

## Reporting a Vulnerability

**Please do not report security vulnerabilities through public GitHub issues.**

If you discover a security vulnerability within phpLiteCore, please send an email to **ceo@itvillage.net**. All security vulnerabilities will be promptly addressed.

When reporting a vulnerability, please include:

- Type of issue (e.g., SQL injection, XSS, CSRF bypass, etc.)
- Full paths of source file(s) related to the manifestation of the issue
- The location of the affected source code (tag/branch/commit or direct URL)
- Any special configuration required to reproduce the issue
- Step-by-step instructions to reproduce the issue
- Proof-of-concept or exploit code (if possible)
- Impact of the issue, including how an attacker might exploit it

## Security Best Practices

When using phpLiteCore, please follow these security best practices:

### 1. Environment Configuration
- Set your web server's document root to the `public/` directory (see [MIGRATION.md](MIGRATION.md) for setup)
- Never commit your `.env` file to version control
- Use strong, unique database passwords
- Keep `APP_ENV=production` in production environments
- Configure SMTP for production error notifications

### 2. Input Validation
- Always validate user input using the `Validator` class
- Use the built-in validation rules or create custom ones
- Never trust user input directly

### 3. Output Escaping
- Use the `e()` helper function to escape HTML output in views
- Be cautious with raw HTML output
- Validate and sanitize data before rendering

### 4. CSRF Protection
- Use the `csrf_field()` helper in all forms
- Ensure CSRF middleware is applied to routes handling state-changing operations
- Include CSRF token in AJAX requests using the `X-CSRF-TOKEN` header

### 5. SQL Injection Prevention
- Use the Query Builder and Active Record pattern (automatically uses prepared statements)
- Avoid raw SQL queries when possible
- If raw queries are necessary, always use parameter binding

### 6. Authentication
- Use the built-in `Auth` class for authentication
- Ensure passwords are properly hashed (the Auth class handles this)
- Implement rate limiting on login endpoints
- Verify emails/phones before allowing authentication with them

### 7. Session Security
- Configure secure session settings in production
- Use HTTPS in production environments
- Set secure and httpOnly flags on session cookies
- Regenerate session IDs after authentication

### 8. Dependency Management
- Regularly update dependencies with `composer update`
- Review security advisories for dependencies
- Use `composer audit` to check for known vulnerabilities

### 9. File Upload Security
- Validate file types and sizes
- Store uploaded files outside the web root (e.g., in `storage/` directory, not `public/`)
- Use random filenames to prevent directory traversal
- Scan uploaded files for malware if handling user uploads

### 10. Error Handling
- Set `APP_ENV=production` in production
- Never expose stack traces or sensitive information to end users
- Configure SMTP to receive error notifications
- Monitor logs regularly for suspicious activity

## Security Features in phpLiteCore

### Built-in Protection
- ✅ **CSRF Protection**: Middleware with token validation
- ✅ **SQL Injection Protection**: PDO prepared statements
- ✅ **XSS Protection**: `e()` helper for output escaping
- ✅ **Input Validation**: Comprehensive validation system
- ✅ **Password Hashing**: Secure password verification in Auth
- ✅ **Session Management**: Secure session handling

### Areas for Enhancement
The following security features are recommended for implementation:

- 🔄 **Rate Limiting**: Implement request rate limiting
- 🔄 **Two-Factor Authentication**: Add 2FA support
- 🔄 **Security Headers**: Add security-related HTTP headers
- 🔄 **Content Security Policy**: Implement CSP headers
- 🔄 **Brute Force Protection**: Add login attempt throttling
- 🔄 **API Rate Limiting**: Implement API-specific rate limiting

## Security Update Policy

- Critical vulnerabilities: Patched within 24-48 hours
- High severity: Patched within 7 days
- Medium severity: Patched in next minor release
- Low severity: Patched in next major release

## Attribution

We appreciate the security research community and will acknowledge researchers who responsibly disclose vulnerabilities (unless you prefer to remain anonymous).

## Contact

For security-related questions: **ceo@itvillage.net**
