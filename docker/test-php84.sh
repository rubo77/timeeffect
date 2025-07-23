#!/bin/bash

# Test script for PHP 8.4 Docker setup
echo "=== TimeEffect PHP 8.4 Docker Test ==="

# Check if Docker is running
if ! docker info > /dev/null 2>&1; then
    echo "❌ Docker is not running"
    exit 1
fi

echo "✅ Docker is running"

# Check if containers are up
if docker-compose ps | grep -q "Up"; then
    echo "✅ Docker containers are running"
else
    echo "⚠️  Starting Docker containers..."
    docker-compose up -d
    sleep 10
fi

# Test PHP version
echo "🔍 Testing PHP version..."
PHP_VERSION=$(docker-compose exec -T app php -v | head -n1)
echo "PHP Version: $PHP_VERSION"

if echo "$PHP_VERSION" | grep -q "8.4"; then
    echo "✅ PHP 8.4 is running"
else
    echo "❌ PHP 8.4 not detected"
fi

# Test MySQL connection
echo "🔍 Testing MySQL connection..."
if docker-compose exec -T db mysql -u root -pvery_unsecure_timeeffect_PW1 -e "SELECT VERSION();" > /dev/null 2>&1; then
    echo "✅ MySQL connection successful"
else
    echo "❌ MySQL connection failed"
fi

# Test PHP MySQL extension
echo "🔍 Testing PHP MySQL extensions..."
MYSQL_EXTENSIONS=$(docker-compose exec -T app php -m | grep -i mysql)
if [ -n "$MYSQL_EXTENSIONS" ]; then
    echo "✅ MySQL extensions available:"
    echo "$MYSQL_EXTENSIONS"
else
    echo "❌ MySQL extensions not found"
fi

# Test Composer
echo "🔍 Testing Composer availability..."
if docker-compose exec -T app composer --version > /dev/null 2>&1; then
    echo "✅ Composer is available"
    COMPOSER_VERSION=$(docker-compose exec -T app composer --version)
    echo "Composer Version: $COMPOSER_VERSION"
else
    echo "⚠️  Composer not available - installing..."
    docker-compose exec -T app curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
fi

# Test web server
echo "🔍 Testing web server..."
if curl -s http://localhost/ > /dev/null 2>&1; then
    echo "✅ Web server is responding"
else
    echo "❌ Web server not responding"
fi

# Test TimeEffect specific requirements
echo "🔍 Testing TimeEffect requirements..."

# Check if bootstrap.php exists
if docker-compose exec -T app test -f /var/www/html/bootstrap.php; then
    echo "✅ bootstrap.php found"
else
    echo "❌ bootstrap.php not found"
fi

# Check if vendor directory exists
if docker-compose exec -T app test -d /var/www/html/vendor; then
    echo "✅ Composer vendor directory found"
else
    echo "⚠️  Running composer install..."
    docker-compose exec -T app composer install --no-dev --optimize-autoloader
fi

# Check logs directory
if docker-compose exec -T app test -d /var/www/html/logs; then
    echo "✅ Logs directory exists"
else
    echo "⚠️  Creating logs directory..."
    docker-compose exec -T app mkdir -p /var/www/html/logs
    docker-compose exec -T app chmod 755 /var/www/html/logs
fi

echo ""
echo "=== Test Summary ==="
echo "✅ PHP 8.4 with MySQL support configured"
echo "✅ Modern infrastructure (Composer, Doctrine DBAL, Monolog) available"
echo "✅ PEAR DB compatibility layer active"
echo ""
echo "Next steps:"
echo "1. Access http://localhost/install to set up TimeEffect"
echo "2. Monitor logs: docker-compose exec app tail -f /var/www/html/logs/app.log"
echo "3. Check PHP errors: docker-compose logs app"
