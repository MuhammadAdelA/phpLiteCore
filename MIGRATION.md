# Migration Guide: Standardized public/ Web Root Structure

## Overview

Starting with this version, phpLiteCore follows PHP framework best practices by using `public/` as the dedicated web root directory. This improves security by ensuring that only public assets and the front controller are accessible via the web server.

## What Changed?

### File Locations

- **`index.php`** - Moved from project root to `public/index.php`
- **`.htaccess`** - Moved from project root to `public/.htaccess`
- The root `.htaccess` now redirects to `public/` for backward compatibility

### Directory Structure

**Before:**
```
project-root/
├── index.php          (Front Controller)
├── .htaccess          (Rewrite Rules)
├── public/
│   └── assets/        (Compiled CSS/JS)
└── ...
```

**After:**
```
project-root/
├── .htaccess          (Redirects to public/)
├── public/            (Web Root - Point your web server here)
│   ├── index.php      (Front Controller)
│   ├── .htaccess      (Rewrite Rules)
│   └── assets/        (Compiled CSS/JS)
└── ...
```

## Migration Steps

### Option 1: Recommended - Update Web Server Configuration (Secure)

This is the **recommended approach** for maximum security.

1. **Update your web server's document root** to point to the `public/` directory instead of the project root.

   **Apache Virtual Host Example:**
   ```apache
   <VirtualHost *:80>
       ServerName yourdomain.com
       DocumentRoot /path/to/phpLiteCore/public
       
       <Directory /path/to/phpLiteCore/public>
           AllowOverride All
           Require all granted
       </Directory>
   </VirtualHost>
   ```

   **Nginx Example:**
   ```nginx
   server {
       listen 80;
       server_name yourdomain.com;
       root /path/to/phpLiteCore/public;
       index index.php;

       location / {
           try_files $uri $uri/ /index.php?$query_string;
       }

       location ~ \.php$ {
           fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
           fastcgi_index index.php;
           fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
           include fastcgi_params;
       }
   }
   ```

2. **Restart your web server** to apply the changes.

3. **Test your application** - Everything should work as before.

### Option 2: Use Backward Compatibility (Less Secure)

If you cannot change your web server configuration immediately, the framework includes backward compatibility support.

1. **No changes required** - The root `.htaccess` file will automatically redirect requests to the `public/` directory.

2. **Note:** This approach is less secure as it exposes the entire project directory to the web server. Plan to migrate to Option 1 when possible.

## Troubleshooting

### Issue: 404 Errors After Migration

**Cause:** Web server document root not updated correctly.

**Solution:** 
- Verify your web server's document root points to the `public/` directory
- Restart your web server after configuration changes
- Check that `mod_rewrite` is enabled (Apache) or try_files is configured (Nginx)

### Issue: Assets Not Loading

**Cause:** Asset paths may need updating.

**Solution:**
- Assets are still in `public/assets/` and should work automatically
- If using hardcoded paths in custom code, update them to reference from the web root

### Issue: Permission Errors

**Cause:** Web server user doesn't have permission to access `public/` directory.

**Solution:**
```bash
# Set appropriate permissions
chmod 755 public/
chmod 644 public/index.php
chmod 644 public/.htaccess
```

## Benefits of This Change

1. **Enhanced Security:** Application files (config, source code, vendor) are no longer accessible via web server
2. **Industry Standard:** Follows conventions used by Laravel, Symfony, and other modern PHP frameworks
3. **Cleaner Separation:** Clear distinction between web-accessible and application-internal files
4. **Better Deployment:** Easier to configure CDN and caching for public assets

## Need Help?

If you encounter issues during migration:
1. Check the [documentation](https://muhammadadela.github.io/phpLiteCore/)
2. Review the [troubleshooting section](#troubleshooting) above
3. Open an issue on [GitHub](https://github.com/MuhammadAdelA/phpLiteCore/issues)
