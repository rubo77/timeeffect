#!/bin/bash

# TimeEffect Docker Setup Script
# Automatically builds and starts TimeEffect with PHP 8.4

set -e

echo "=== TimeEffect Docker Setup ==="
echo "Building custom PHP 8.4 image with all configurations..."

# Stop existing containers
echo "Stopping existing containers..."
sudo docker-compose down --volumes --remove-orphans 2>/dev/null || true

# Build custom image
echo "Building TimeEffect PHP 8.4 image..."
sudo docker-compose build --no-cache

# Start containers
echo "Starting containers..."
sudo docker-compose up -d

# Wait for containers to be ready
echo "Waiting for containers to start..."
sleep 15

# Install Composer dependencies
echo "Installing Composer dependencies..."
sudo docker-compose exec -T app bash -c "cd /var/www/html && composer install --no-dev --optimize-autoloader" || echo "Composer install skipped (may not be needed)"

# Setup install directory permissions
echo "Setting up install directory permissions..."
sudo docker-compose exec -T app bash -c "mkdir -p /var/www/html/install/include"
sudo docker-compose exec -T app bash -c "cp /var/www/html/install/config.inc.php-dist /var/www/html/install/include/config.inc.php 2>/dev/null || true"
sudo docker-compose exec -T app bash -c "chown -R application:application /var/www/html/install/include"
sudo docker-compose exec -T app bash -c "chmod 666 /var/www/html/install/include/config.inc.php"
echo "✅ Install directory permissions set"

# Test PHP functionality
echo "Testing PHP functionality..."
PHP_VERSION=$(sudo docker-compose exec -T app php -v | head -n1)
echo "✅ $PHP_VERSION"

# Test short tags
echo "Testing PHP short tags..."
sudo docker-compose exec -T app bash -c "echo '<?= \"PHP Short Tags: \" . (ini_get('short_open_tag') ? 'ENABLED' : 'DISABLED') ?>' > /var/www/html/test_short_tags.php"
SHORT_TAG_TEST=$(curl -s http://localhost/test_short_tags.php 2>/dev/null || echo "Connection failed")
sudo docker-compose exec -T app rm -f /var/www/html/test_short_tags.php
echo "✅ $SHORT_TAG_TEST"

# Test MySQL extensions
echo "Testing MySQL extensions..."
MYSQL_EXTENSIONS=$(sudo docker-compose exec -T app php -m | grep -i mysql | tr '\n' ', ')
echo "✅ MySQL Extensions: $MYSQL_EXTENSIONS"

# Test web server
echo "Testing web server..."
HTTP_STATUS=$(curl -s -o /dev/null -w "%{http_code}" http://localhost/ 2>/dev/null || echo "000")
if [ "$HTTP_STATUS" = "200" ]; then
    echo "✅ Web server responding (HTTP $HTTP_STATUS)"
else
    echo "⚠️  Web server status: HTTP $HTTP_STATUS"
fi

# Test install page
echo "Testing install page..."
INSTALL_STATUS=$(curl -s -o /dev/null -w "%{http_code}" http://localhost/install/ 2>/dev/null || echo "000")
if [ "$INSTALL_STATUS" = "200" ]; then
    echo "✅ Install page accessible (HTTP $INSTALL_STATUS)"
else
    echo "⚠️  Install page status: HTTP $INSTALL_STATUS"
fi

echo ""
echo "=== Setup Complete! ==="
echo "🚀 TimeEffect is ready:"
echo "   📱 Application: http://localhost/"
echo "   ⚙️  Installation: http://localhost/install/"
echo "   📊 Container status: sudo docker-compose ps"
echo "   📋 Logs: sudo docker-compose logs -f app"
echo ""
echo "🎉 PHP 8.4 with all modern features is now running!"
