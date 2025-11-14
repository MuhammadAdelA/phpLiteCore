# Network Configuration Guide

This guide explains how to configure network access for phpLiteCore when working in environments with firewall restrictions, such as GitHub Actions with network policies or enterprise environments.

## Overview

phpLiteCore requires access to various external resources during development, testing, and CI/CD processes. If you're working in an environment with firewall restrictions or need to configure GitHub Copilot coding agents, this guide will help you set up the necessary access.

## Required External Resources

### 1. Composer/Packagist (PHP Dependencies)
- **packagist.org** - Main Composer package repository
- **repo.packagist.org** - Package metadata and downloads
- **getcomposer.org** - Composer installer

### 2. NPM Registry (JavaScript Dependencies)
- **registry.npmjs.org** - NPM package registry
- **nodejs.org** - Node.js downloads (if installing Node.js)

### 3. GitHub Resources
- **github.com** - Repository access and GitHub Actions marketplace
- **api.github.com** - GitHub API
- **raw.githubusercontent.com** - Raw file access

### 4. Ubuntu APT Repositories (Linux packages and PHP extensions)
- **archive.ubuntu.com** - Main Ubuntu package repository
- **security.ubuntu.com** - Security updates
- **ppa.launchpad.net** - Personal Package Archives

### 5. PHP Resources
- **pecl.php.net** - PHP Extension Community Library (if needed)
- **pear.php.net** - PHP Extension and Application Repository (if needed)

## Configuration Methods

### Method 1: GitHub Actions Setup Steps

If you're using GitHub Actions and need to access these resources **before** a firewall is enabled, configure your workflow to set up the environment in the setup phase:

```yaml
name: PHP Code Quality Checks

on:
  push:
    branches: [ "master" ]
  pull_request:
    branches: [ "master" ]

jobs:
  lint-and-test:
    runs-on: ubuntu-latest
    
    steps:
      # Step 1: Checkout repository (requires github.com access)
      - name: 📥 Checkout Repository
        uses: actions/checkout@v4

      # Step 2: Install system dependencies (requires apt repositories)
      - name: Install required PHP extensions
        run: |
          sudo apt-get update
          sudo apt-get install -y php-pdo php-exif php-fileinfo php-mbstring php-mysqli php-intl php-gd

      # Step 3: Setup PHP environment (requires github.com for action)
      - name: ⚙️ Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
          extensions: pdo, exif, fileinfo, mbstring, mysqli, intl, gd, bcmath
          tools: composer:v2

      # Step 4: Install Composer dependencies (requires packagist.org)
      - name: 📦 Install Composer Dependencies
        run: composer install --no-interaction --prefer-dist

      # Step 5: Install NPM dependencies (requires registry.npmjs.org)
      - name: 📦 Install NPM Dependencies
        run: npm install

      # After these setup steps, the firewall can be enabled
```

**Key Points:**
- Place all external resource access in setup steps at the beginning of your workflow
- Use caching strategies to minimize repeated downloads
- Ensure all dependencies are installed before firewall activation

### Method 2: Custom Allowlist Configuration

For GitHub Copilot coding agents or enterprise firewall configurations, add these URLs/hosts to your custom allowlist:

#### Required Hosts for Full Functionality:

```
# Composer/PHP Dependencies
packagist.org
repo.packagist.org
getcomposer.org

# NPM/Node.js Dependencies
registry.npmjs.org
nodejs.org

# GitHub Services
github.com
api.github.com
raw.githubusercontent.com
objects.githubusercontent.com

# Ubuntu Package Repositories
archive.ubuntu.com
security.ubuntu.com
ppa.launchpad.net
*.ubuntu.com

# PHP Resources (if needed)
pecl.php.net
pear.php.net
```

#### Minimal Required Hosts (for basic operation):

```
packagist.org
repo.packagist.org
registry.npmjs.org
github.com
```

### Method 3: Proxy Configuration

If you're behind a corporate proxy, configure your tools:

#### Composer Proxy
```bash
# Set Composer proxy
composer config --global http-proxy http://proxy.example.com:8080
composer config --global https-proxy https://proxy.example.com:8080
```

#### NPM Proxy
```bash
# Set NPM proxy
npm config set proxy http://proxy.example.com:8080
npm config set https-proxy http://proxy.example.com:8080
```

#### Git Proxy
```bash
# Set Git proxy
git config --global http.proxy http://proxy.example.com:8080
git config --global https.proxy https://proxy.example.com:8080
```

## GitHub Copilot Coding Agent Configuration

If you're using GitHub Copilot coding agents and encounter network access issues:

1. **Navigate to Repository Settings** → Copilot → Coding agents settings
2. **Add Custom Allowlist Entries:**
   - Add the required hosts from the list above
   - Prioritize the "Minimal Required Hosts" for basic functionality
   - Add additional hosts as needed for specific features

3. **Alternative: Configure Actions Setup**
   - Modify `.github/workflows/` files to download/install resources in setup steps
   - These setup steps run before network restrictions are applied

## Troubleshooting

### Common Issues and Solutions

#### Issue: Composer fails to download packages
**Solution:** Ensure `packagist.org` and `repo.packagist.org` are accessible
```bash
# Test connectivity
curl -I https://packagist.org
curl -I https://repo.packagist.org
```

#### Issue: NPM fails to install dependencies
**Solution:** Ensure `registry.npmjs.org` is accessible
```bash
# Test connectivity
curl -I https://registry.npmjs.org
```

#### Issue: GitHub Actions fail to checkout code
**Solution:** Ensure `github.com` and `api.github.com` are accessible

#### Issue: PHP extensions fail to install
**Solution:** Ensure Ubuntu APT repositories are accessible
```bash
# Test APT repository access
sudo apt-get update
```

## Caching Strategies

To minimize external resource access in CI/CD:

### GitHub Actions Caching Example:

```yaml
- name: 📥 Cache Composer dependencies
  uses: actions/cache@v4
  with:
    path: vendor
    key: ${{ runner.os }}-php-${{ hashFiles('**/composer.lock') }}
    restore-keys: |
      ${{ runner.os }}-php-

- name: 📥 Cache NPM dependencies
  uses: actions/cache@v4
  with:
    path: node_modules
    key: ${{ runner.os }}-node-${{ hashFiles('**/package-lock.json') }}
    restore-keys: |
      ${{ runner.os }}-node-
```

## Security Considerations

- **Minimize Allowlist Entries:** Only add hosts that are strictly necessary
- **Use HTTPS:** Ensure all external resources use HTTPS protocol
- **Verify Sources:** Only add trusted domains to your allowlist
- **Regular Audits:** Periodically review and update your allowlist
- **Version Pinning:** Use specific versions in `composer.lock` and `package-lock.json` to ensure reproducible builds

## Additional Resources

- [Composer Documentation](https://getcomposer.org/doc/)
- [NPM Documentation](https://docs.npmjs.com/)
- [GitHub Actions Documentation](https://docs.github.com/en/actions)
- [GitHub Copilot Documentation](https://docs.github.com/en/copilot)

## Support

If you encounter network configuration issues specific to phpLiteCore, please:
1. Check this guide for solutions
2. Review the GitHub Actions workflow in `.github/workflows/tests.yml`
3. Open an issue on the [GitHub repository](https://github.com/MuhammadAdelA/phpLiteCore/issues)
