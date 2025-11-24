#!/bin/bash

################################################################################
# Quick Docker Setup for phpLiteCore
################################################################################
# This script quickly sets up and starts the Docker development environment
#
# Prerequisites:
#   - Docker and Docker Compose installed
#   - Run this from the phpLiteCore project root
#
# Usage:
#   chmod +x docker-quick-start.sh
#   ./docker-quick-start.sh
################################################################################

set -e

# Colors
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo "================================================================================"
echo "           phpLiteCore Docker Development Environment Quick Start"
echo "================================================================================"
echo ""

# Check if Docker is installed
if ! command -v docker &> /dev/null; then
    echo -e "${YELLOW}Docker is not installed!${NC}"
    echo "Please run ./setup-wsl2-ubuntu.sh first to install Docker."
    exit 1
fi

# Check if Docker Compose is installed
if ! command -v docker-compose &> /dev/null && ! docker compose version &> /dev/null; then
    echo -e "${YELLOW}Docker Compose is not installed!${NC}"
    echo "Please run ./setup-wsl2-ubuntu.sh first to install Docker Compose."
    exit 1
fi

# Check if we're in the right directory
if [ ! -f "composer.json" ]; then
    echo -e "${YELLOW}Error: composer.json not found!${NC}"
    echo "Please run this script from the phpLiteCore project root."
    exit 1
fi

echo -e "${BLUE}Step 1: Creating .env file...${NC}"
if [ ! -f ".env" ]; then
    cp .env.example .env
    # Update for Docker (these sed commands are safe - they only update if patterns match)
    sed -i 's/MYSQL_DB_HOST=localhost/MYSQL_DB_HOST=db/' .env
    sed -i 's/SMTP_HOST=smtp.example.com/SMTP_HOST=mailhog/' .env
    sed -i 's/SMTP_PORT=587/SMTP_PORT=1025/' .env
    sed -i 's/SMTP_ENCRYPTION=tls/SMTP_ENCRYPTION=/' .env
    echo -e "${GREEN}✓ .env file created and configured for Docker${NC}"
    echo -e "   ${BLUE}Note: Review .env and adjust settings if needed${NC}"
else
    echo -e "${YELLOW}✓ .env file already exists${NC}"
    echo -e "   ${BLUE}Make sure MYSQL_DB_HOST=db and SMTP_HOST=mailhog for Docker${NC}"
fi
echo ""

echo -e "${BLUE}Step 2: Starting Docker containers...${NC}"
docker-compose up -d
echo -e "${GREEN}✓ Docker containers started${NC}"
echo ""

echo -e "${BLUE}Step 3: Waiting for services to be ready...${NC}"
sleep 10
echo -e "${GREEN}✓ Services are ready${NC}"
echo ""

echo -e "${BLUE}Step 4: Installing PHP dependencies...${NC}"
if [ ! -d "vendor" ]; then
    docker-compose exec -T app composer install --no-interaction
    echo -e "${GREEN}✓ PHP dependencies installed${NC}"
else
    echo -e "${YELLOW}✓ PHP dependencies already installed (vendor/ exists)${NC}"
    echo -e "   Run 'docker-compose exec app composer update' to update"
fi
echo ""

echo -e "${BLUE}Step 5: Installing Node.js dependencies...${NC}"
if [ ! -d "node_modules" ]; then
    docker-compose exec -T app npm install
    echo -e "${GREEN}✓ Node.js dependencies installed${NC}"
else
    echo -e "${YELLOW}✓ Node.js dependencies already installed (node_modules/ exists)${NC}"
    echo -e "   Run 'docker-compose exec app npm update' to update"
fi
echo ""

echo -e "${BLUE}Step 6: Building frontend assets...${NC}"
docker-compose exec -T app npm run build
echo -e "${GREEN}✓ Frontend assets built${NC}"
echo ""

echo "================================================================================"
echo "                          Setup Complete!"
echo "================================================================================"
echo ""
echo -e "${GREEN}Your phpLiteCore development environment is now running!${NC}"
echo ""
echo "Access your services:"
echo "  • Web Application:    http://localhost:8080"
echo "  • phpMyAdmin:         http://localhost:8081"
echo "  • MailHog:            http://localhost:8025"
echo ""
echo "Useful commands:"
echo "  • View logs:          docker-compose logs -f"
echo "  • Stop services:      docker-compose down"
echo "  • Access container:   docker-compose exec app bash"
echo "  • Run tests:          docker-compose exec app ./vendor/bin/pest"
echo ""
echo "For more information, see DOCKER_SETUP.md"
echo "================================================================================"
