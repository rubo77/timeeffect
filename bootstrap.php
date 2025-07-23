<?php
/**
 * TimeEffect Application Bootstrap
 * 
 * This file initializes the modern infrastructure while maintaining
 * backward compatibility with legacy PEAR code
 */

// Load Composer autoloader
require_once __DIR__ . '/vendor/autoload.php';

// Load environment variables if .env exists
if (file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

// Set up error reporting for development
if (isset($_ENV['APP_ENV']) && $_ENV['APP_ENV'] === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ERROR | E_WARNING | E_PARSE);
    ini_set('display_errors', 0);
}

// Set up logging
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;

$logger = new Logger('timeeffect');

// Add file handler
$logPath = __DIR__ . '/logs/app.log';
if (!is_dir(dirname($logPath))) {
    mkdir(dirname($logPath), 0755, true);
}
$logger->pushHandler(new RotatingFileHandler($logPath, 0, Logger::INFO));

// Add error log handler for critical errors
$logger->pushHandler(new StreamHandler('php://stderr', Logger::ERROR));

// Make logger globally available
$GLOBALS['logger'] = $logger;

// Log bootstrap completion
$logger->info('TimeEffect application bootstrap completed', [
    'php_version' => PHP_VERSION,
    'memory_limit' => ini_get('memory_limit'),
    'max_execution_time' => ini_get('max_execution_time')
]);

// Legacy PEAR compatibility is automatically loaded via composer.json files section
