# Docker Development Environment Setup

This guide explains how to set up the phpLiteCore development environment using Docker Compose on WSL2 Ubuntu 24.04.

## Table of Contents

- [Prerequisites](#prerequisites)
- [Quick Start](#quick-start)
- [Automated Setup Script](#automated-setup-script)
- [Manual Setup](#manual-setup)
- [Using the Development Environment](#using-the-development-environment)
- [Services](#services)
- [Common Tasks](#common-tasks)
- [Troubleshooting](#troubleshooting)

---

## Prerequisites

### Windows Requirements

- Windows 10 version 2004+ (Build 19041+) or Windows 11
- WSL2 installed and configured
- At least 8GB of RAM
- At least 20GB of free disk space

### Install WSL2 and Ubuntu 24.04

If you haven't installed WSL2 yet, run this in PowerShell as Administrator:

```powershell
wsl --install -d Ubuntu-24.04
```

After installation, restart your computer and set up your Ubuntu user account.

---

## Quick Start

The easiest way to get started is using our automated setup script:

### 1. Clone the Repository

```bash
git clone https://github.com/MuhammadAdelA/phpLiteCore.git
cd phpLiteCore
```

### 2. Run the Setup Script

```bash
chmod +x setup-wsl2-ubuntu.sh
./setup-wsl2-ubuntu.sh
```

The script will:
- Update your system
- Install Docker and Docker Compose
- Install Git and development tools
- Configure your environment
- Set up the project structure

### 3. Start the Environment

After the script completes, log out and back in (or run `newgrp docker`), then:

```bash
docker-compose up -d
```

### 4. Install Dependencies

```bash
# Install PHP dependencies
docker-compose exec app composer install

# Install Node.js dependencies
docker-compose exec app npm install

# Build frontend assets
docker-compose exec app npm run build
```

### 5. Access Your Application

- **Web Application:** http://localhost:8080
- **phpMyAdmin:** http://localhost:8081
- **MailHog (Email testing):** http://localhost:8025

---

## Automated Setup Script

The `setup-wsl2-ubuntu.sh` script automates the entire environment setup process.

### What It Does

1. **System Update:** Updates all packages to the latest versions
2. **Docker Installation:** Installs Docker Engine and Docker Compose
3. **Development Tools:** Installs Git, vim, curl, and other utilities
4. **Git Configuration:** Optionally configures your Git user name and email
5. **Environment Setup:** Creates `.env` file from `.env.example`
6. **Permissions:** Adds your user to the docker group

### Usage

```bash
# Make the script executable
chmod +x setup-wsl2-ubuntu.sh

# Run the script
./setup-wsl2-ubuntu.sh

# After completion, apply docker group changes
newgrp docker
# Or log out and log back in
```

---

## Manual Setup

If you prefer manual setup or the script doesn't work for your environment:

### 1. Install Docker

```bash
# Update packages
sudo apt-get update
sudo apt-get upgrade -y

# Install prerequisites
sudo apt-get install -y ca-certificates curl gnupg lsb-release

# Add Docker's GPG key
sudo install -m 0755 -d /etc/apt/keyrings
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /etc/apt/keyrings/docker.gpg
sudo chmod a+r /etc/apt/keyrings/docker.gpg

# Add Docker repository
echo \
  "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/ubuntu \
  $(. /etc/os-release && echo "$VERSION_CODENAME") stable" | \
  sudo tee /etc/apt/sources.list.d/docker.list > /dev/null

# Install Docker
sudo apt-get update
sudo apt-get install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin

# Add user to docker group
sudo usermod -aG docker $USER

# Start Docker
sudo service docker start
```

### 2. Install Docker Compose (standalone)

```bash
DOCKER_COMPOSE_VERSION=$(curl -s https://api.github.com/repos/docker/compose/releases/latest | grep '"tag_name":' | sed -E 's/.*"([^"]+)".*/\1/')
sudo curl -L "https://github.com/docker/compose/releases/download/${DOCKER_COMPOSE_VERSION}/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose
```

### 3. Configure the Project

```bash
# Copy environment file
cp .env.example .env

# Edit .env file with your preferences
nano .env
```

### 4. Start Docker Compose

```bash
docker-compose up -d
```

---

## Using the Development Environment

### Starting the Environment

```bash
# Start all services
docker-compose up -d

# Start and rebuild containers
docker-compose up -d --build

# Start with logs visible
docker-compose up
```

### Stopping the Environment

```bash
# Stop all services
docker-compose down

# Stop and remove volumes (WARNING: deletes database data)
docker-compose down -v
```

### Viewing Logs

```bash
# View all logs
docker-compose logs

# Follow logs in real-time
docker-compose logs -f

# View logs for specific service
docker-compose logs -f app
docker-compose logs -f db
```

### Accessing Containers

```bash
# Access PHP container bash
docker-compose exec app bash

# Access MySQL container
docker-compose exec db mysql -u root -p

# Run PHP commands
docker-compose exec app php -v
docker-compose exec app php artisan --version
```

---

## Services

The Docker Compose setup includes the following services:

### 1. App (PHP Application)

- **Container:** phplitecore_app
- **Port:** 8080
- **Description:** PHP 8.3 with Apache web server
- **Access:** http://localhost:8080

#### Included Extensions:
- PDO (MySQL)
- mysqli
- mbstring
- exif
- bcmath
- gd
- intl
- zip
- opcache

### 2. Database (MySQL)

- **Container:** phplitecore_db
- **Port:** 3306
- **Description:** MySQL 8.0 database server
- **Default Credentials:**
  - Root Password: `rootsecret` (configured in .env)
  - Database: `phplitecore`
  - User: `phplitecore`
  - Password: `secret`

#### Database Import

The database is automatically initialized with `phplitecore.sql` on first run. To manually import:

```bash
docker-compose exec db mysql -u root -p phplitecore < phplitecore.sql
```

### 3. phpMyAdmin

- **Container:** phplitecore_phpmyadmin
- **Port:** 8081
- **Description:** Web-based database management tool
- **Access:** http://localhost:8081
- **Credentials:** Use MySQL credentials

### 4. MailHog

- **Container:** phplitecore_mailhog
- **Ports:** 
  - SMTP: 1025
  - Web UI: 8025
- **Description:** Email testing tool that catches all outgoing emails
- **Access:** http://localhost:8025

#### Configure SMTP in .env:

```env
SMTP_HOST=mailhog
SMTP_PORT=1025
SMTP_USERNAME=
SMTP_PASSWORD=
SMTP_ENCRYPTION=
```

---

## Common Tasks

### Installing PHP Dependencies

```bash
docker-compose exec app composer install

# Update dependencies
docker-compose exec app composer update

# Add a new package
docker-compose exec app composer require package/name
```

### Installing Node Dependencies

```bash
docker-compose exec app npm install

# Update dependencies
docker-compose exec app npm update

# Add a new package
docker-compose exec app npm install package-name
```

### Building Assets

```bash
# Development build
docker-compose exec app npm run dev

# Production build
docker-compose exec app npm run build

# Watch mode (rebuilds on file changes)
docker-compose exec app npm run dev
```

### Running Tests

```bash
# Run all tests
docker-compose exec app ./vendor/bin/pest

# Run specific test file
docker-compose exec app ./vendor/bin/pest tests/Feature/SomeTest.php

# Run with coverage
docker-compose exec app composer test:coverage
```

### Code Quality Checks

```bash
# PHP syntax check
docker-compose exec app composer lint

# Code style check
docker-compose exec app composer format:check

# Fix code style
docker-compose exec app composer format

# Static analysis
docker-compose exec app composer analyse

# Run all quality checks
docker-compose exec app composer quality
```

### Database Operations

```bash
# Access MySQL CLI
docker-compose exec db mysql -u root -p

# Export database
docker-compose exec db mysqldump -u root -p phplitecore > backup.sql

# Import database
docker-compose exec db mysql -u root -p phplitecore < backup.sql

# View database logs
docker-compose logs db
```

### File Permissions

If you encounter permission issues:

```bash
# Fix storage permissions
docker-compose exec app chmod -R 777 storage

# Fix ownership
docker-compose exec app chown -R www-data:www-data storage
```

---

## Troubleshooting

### Docker Service Not Starting

```bash
# Check Docker status
sudo service docker status

# Start Docker
sudo service docker start

# Enable Docker to start on boot
sudo systemctl enable docker
```

### Port Already in Use

If you see an error about ports already being in use:

```bash
# Check what's using the port
sudo lsof -i :8080
sudo lsof -i :3306

# Stop the conflicting service or change ports in docker-compose.yml
```

### Permission Denied Errors

```bash
# Verify docker group membership
groups

# If docker is not listed, add yourself:
sudo usermod -aG docker $USER

# Apply changes without logout
newgrp docker
```

### MySQL Connection Issues

1. Verify database is running:
   ```bash
   docker-compose ps db
   ```

2. Check database logs:
   ```bash
   docker-compose logs db
   ```

3. Verify environment variables in `.env`:
   ```bash
   MYSQL_DB_HOST=db
   MYSQL_DB_PORT=3306
   ```

### Container Build Issues

```bash
# Rebuild containers from scratch
docker-compose down
docker-compose build --no-cache
docker-compose up -d
```

### WSL2 Memory Issues

If Docker is using too much memory, create/edit `%USERPROFILE%\.wslconfig` in Windows:

```ini
[wsl2]
memory=4GB
processors=2
swap=2GB
```

Then restart WSL:
```powershell
wsl --shutdown
```

### View All Container Details

```bash
# List all containers
docker-compose ps

# Inspect a specific container
docker inspect phplitecore_app

# Check container resource usage
docker stats
```

### Fresh Start

To completely reset the environment:

```bash
# Stop and remove everything
docker-compose down -v

# Remove images
docker-compose down --rmi all -v

# Start fresh
docker-compose up -d --build
```

---

## Additional Resources

- [phpLiteCore Documentation](https://muhammadadela.github.io/phpLiteCore/)
- [Docker Documentation](https://docs.docker.com/)
- [Docker Compose Documentation](https://docs.docker.com/compose/)
- [WSL2 Documentation](https://docs.microsoft.com/en-us/windows/wsl/)

---

## Environment Variables

Key environment variables in `.env`:

```env
# Application
APP_ENV=development

# Database
MYSQL_DB_HOST=db
MYSQL_DB_PORT=3306
MYSQL_DB_NAME=phplitecore
MYSQL_DB_USER=phplitecore
MYSQL_DB_PASS=secret

# Root password for database container
MYSQL_ROOT_PASSWORD=rootsecret

# SMTP (use MailHog for development)
SMTP_HOST=mailhog
SMTP_PORT=1025
```

---

## Support

If you encounter issues not covered in this guide:

1. Check the [main README](README.md)
2. Review [CONTRIBUTING.md](CONTRIBUTING.md)
3. Open an issue on [GitHub](https://github.com/MuhammadAdelA/phpLiteCore/issues)
