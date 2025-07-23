<?php
/**
 * Generate .env file from existing config.inc.php
 * This helps migrate from old configuration to modern environment variables
 */

$config_file = __DIR__ . '/../include/config.inc.php';
$env_file = __DIR__ . '/../.env';

if (!file_exists($config_file)) {
    die("❌ Config file not found: $config_file\n");
}

echo "🔄 Generating .env from existing config.inc.php...\n";

// Include the config file to get variables
ob_start();
include $config_file;
ob_end_clean();

// Extract database configuration
$db_host = isset($_PJ_db_host) ? $_PJ_db_host : 'localhost';
$db_name = isset($_PJ_db_name) ? $_PJ_db_name : 'timeeffect';
$db_user = isset($_PJ_db_user) ? $_PJ_db_user : 'root';
$db_password = isset($_PJ_db_password) ? $_PJ_db_password : '';
$db_prefix = isset($_PJ_db_prefix) ? $_PJ_db_prefix : 'te_';

// Extract application settings
$debug = isset($GLOBALS['debug']) ? ($GLOBALS['debug'] ? 'true' : 'false') : 'false';
$http_root = isset($_PJ_http_root) ? $_PJ_http_root : '';
$root_path = isset($_PJ_root) ? $_PJ_root : '/var/www/html';

// Generate .env content
$env_content = <<<ENV
# TimeEffect Environment Configuration
# Generated from existing config.inc.php on " . date('Y-m-d H:i:s') . "

# Database Configuration
DB_HOST=$db_host
DB_DATABASE=$db_name
DB_USERNAME=$db_user
DB_PASSWORD=$db_password
DB_PREFIX=$db_prefix

# Application Settings
APP_ENV=production
APP_DEBUG=$debug
APP_HTTP_ROOT=$http_root
APP_ROOT_PATH=$root_path

# Logging
LOG_LEVEL=error
LOG_CHANNEL=single

# Session Configuration
SESSION_LIFETIME=3600
SESSION_SECURE=false

# Security
APP_KEY=base64:$(base64_encode(random_bytes(32)))

ENV;

// Write .env file
if (file_put_contents($env_file, $env_content)) {
    echo "✅ .env file generated successfully: $env_file\n";
    echo "📋 Configuration extracted:\n";
    echo "   - Database Host: $db_host\n";
    echo "   - Database Name: $db_name\n";
    echo "   - Database User: $db_user\n";
    echo "   - Database Prefix: $db_prefix\n";
    echo "   - Debug Mode: $debug\n";
    echo "   - HTTP Root: $http_root\n";
    echo "\n🔧 Please review and adjust the .env file as needed.\n";
} else {
    echo "❌ Failed to write .env file: $env_file\n";
}

function base64_encode_random($length = 32) {
    return base64_encode(random_bytes($length));
}
?>
