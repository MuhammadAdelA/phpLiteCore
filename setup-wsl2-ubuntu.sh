#!/bin/bash

################################################################################
# WSL2 Ubuntu 24.04 Setup Script for phpLiteCore Development Environment
################################################################################
# This script automates the setup of a complete development environment for
# phpLiteCore on WSL2 Ubuntu 24.04 with Docker Compose.
#
# Prerequisites:
#   - WSL2 installed on Windows 11/10
#   - Ubuntu 24.04 LTS installed on WSL2
#   - Internet connection
#
# Usage:
#   chmod +x setup-wsl2-ubuntu.sh
#   ./setup-wsl2-ubuntu.sh
################################################################################

set -e  # Exit on error
set -u  # Exit on undefined variable

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Logging functions
log_info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

log_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

log_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Banner
echo "================================================================================"
echo "    phpLiteCore Development Environment Setup for WSL2 Ubuntu 24.04"
echo "================================================================================"
echo ""

# Check if running on WSL2
if ! grep -qEi "(Microsoft|WSL)" /proc/version &> /dev/null; then
    log_warning "This script is designed for WSL2 Ubuntu 24.04"
    read -p "Continue anyway? (y/N): " confirm
    if [[ ! $confirm =~ ^[Yy]$ ]]; then
        exit 1
    fi
fi

# Check if Ubuntu 24.04
if [ -f /etc/os-release ]; then
    . /etc/os-release
    if [[ "$VERSION_ID" != "24.04" ]]; then
        log_warning "This script is optimized for Ubuntu 24.04. Current version: $VERSION_ID"
        read -p "Continue anyway? (y/N): " confirm
        if [[ ! $confirm =~ ^[Yy]$ ]]; then
            exit 1
        fi
    fi
fi

log_info "Starting setup process..."
echo ""

################################################################################
# Step 1: Update System Packages
################################################################################
log_info "Step 1/7: Updating system packages..."
sudo apt-get update
sudo apt-get upgrade -y
log_success "System packages updated"
echo ""

################################################################################
# Step 2: Install Docker
################################################################################
log_info "Step 2/7: Installing Docker..."

if command -v docker &> /dev/null; then
    log_warning "Docker is already installed"
    docker --version
else
    # Remove old versions
    sudo apt-get remove -y docker docker-engine docker.io containerd runc 2>/dev/null || true

    # Install prerequisites
    sudo apt-get install -y \
        ca-certificates \
        curl \
        gnupg \
        lsb-release

    # Add Docker's official GPG key
    sudo install -m 0755 -d /etc/apt/keyrings
    curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /etc/apt/keyrings/docker.gpg
    sudo chmod a+r /etc/apt/keyrings/docker.gpg

    # Set up Docker repository
    echo \
      "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/ubuntu \
      $(. /etc/os-release && echo "$VERSION_CODENAME") stable" | \
      sudo tee /etc/apt/sources.list.d/docker.list > /dev/null

    # Install Docker Engine
    sudo apt-get update
    sudo apt-get install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin

    # Add current user to docker group
    sudo usermod -aG docker $USER

    log_success "Docker installed successfully"
fi

# Start Docker service
sudo service docker start || true

echo ""

################################################################################
# Step 3: Install Docker Compose (standalone, in addition to plugin)
################################################################################
log_info "Step 3/7: Installing Docker Compose standalone..."

if command -v docker-compose &> /dev/null; then
    log_warning "Docker Compose is already installed"
    docker-compose --version
else
    # Install latest Docker Compose
    DOCKER_COMPOSE_VERSION=$(curl -s https://api.github.com/repos/docker/compose/releases/latest | grep '"tag_name":' | sed -E 's/.*"([^"]+)".*/\1/')
    sudo curl -L "https://github.com/docker/compose/releases/download/${DOCKER_COMPOSE_VERSION}/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
    sudo chmod +x /usr/local/bin/docker-compose

    log_success "Docker Compose installed successfully"
fi

docker-compose --version || docker compose version

echo ""

################################################################################
# Step 4: Install Git
################################################################################
log_info "Step 4/7: Installing Git..."

if command -v git &> /dev/null; then
    log_warning "Git is already installed"
    git --version
else
    sudo apt-get install -y git
    log_success "Git installed successfully"
fi

echo ""

################################################################################
# Step 5: Install Additional Tools
################################################################################
log_info "Step 5/7: Installing additional development tools..."

# Install useful tools for development
sudo apt-get install -y \
    vim \
    nano \
    curl \
    wget \
    unzip \
    zip \
    htop \
    net-tools \
    iputils-ping \
    telnet \
    dnsutils \
    jq

log_success "Additional tools installed"
echo ""

################################################################################
# Step 6: Configure Git (Optional)
################################################################################
log_info "Step 6/7: Git configuration (optional)..."

if [ -z "$(git config --global user.name)" ]; then
    read -p "Enter your Git name (or press Enter to skip): " git_name
    if [ ! -z "$git_name" ]; then
        git config --global user.name "$git_name"
    fi
fi

if [ -z "$(git config --global user.email)" ]; then
    read -p "Enter your Git email (or press Enter to skip): " git_email
    if [ ! -z "$git_email" ]; then
        git config --global user.email "$git_email"
    fi
fi

log_success "Git configuration completed"
echo ""

################################################################################
# Step 7: Setup Docker Compose Environment
################################################################################
log_info "Step 7/7: Preparing Docker Compose environment..."

# Check if we're in the phpLiteCore directory
if [ ! -f "composer.json" ]; then
    log_warning "Not in phpLiteCore directory. Make sure to run docker-compose from the project root."
else
    # Check if .env exists
    if [ ! -f ".env" ]; then
        log_info "Creating .env file from .env.example..."
        cp .env.example .env
        log_success ".env file created. Please update it with your configuration."
    else
        log_warning ".env file already exists"
    fi
    
    log_info "Docker setup files are ready. You can now run:"
    echo ""
    echo "    docker-compose up -d"
    echo ""
    log_info "To build and start the development environment."
fi

echo ""

################################################################################
# Summary and Next Steps
################################################################################
echo "================================================================================"
echo "                          Setup Complete!"
echo "================================================================================"
echo ""
log_success "Your WSL2 Ubuntu 24.04 environment is now ready for phpLiteCore development!"
echo ""
echo "Installed components:"
echo "  ✓ Docker Engine"
echo "  ✓ Docker Compose"
echo "  ✓ Git"
echo "  ✓ Development tools"
echo ""
echo "Next steps:"
echo ""
echo "  1. Log out and log back in (or run 'newgrp docker') for Docker group changes"
echo "  2. Navigate to your phpLiteCore project directory"
echo "  3. Run 'docker-compose up -d' to start the development environment"
echo "  4. Run 'docker-compose exec app composer install' to install PHP dependencies"
echo "  5. Run 'docker-compose exec app npm install' to install Node dependencies"
echo "  6. Run 'docker-compose exec app npm run build' to build assets"
echo "  7. Database is auto-imported on first run, or manually: 'docker-compose exec -T db mysql -u root -prootsecret phplitecore < phplitecore.sql'"
echo "  8. Access your application at http://localhost:8080"
echo ""
echo "Useful Docker Compose commands:"
echo "  - Start services:        docker-compose up -d"
echo "  - Stop services:         docker-compose down"
echo "  - View logs:             docker-compose logs -f"
echo "  - Execute commands:      docker-compose exec app bash"
echo "  - Rebuild containers:    docker-compose up -d --build"
echo ""
echo "For more information, see the README.md file."
echo "================================================================================"
