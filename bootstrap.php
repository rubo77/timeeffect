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

// Bestimme den Logging-Pfad basierend auf Berechtigungen
$logDirectories = [
    __DIR__ . '/logs',                  // Bevorzugter Ort
    sys_get_temp_dir() . '/timeeffect',  // Fallback: Temporäres Verzeichnis
];

$logDir = null;
$logPath = null;

// Prüfe, ob eines der Verzeichnisse bereits existiert und schreibbar ist
foreach ($logDirectories as $dir) {
    if (is_dir($dir) && is_writable($dir)) {
        $logDir = $dir;
        $logPath = $dir . '/app.log';
        break;
    }
}

// Falls kein Verzeichnis gefunden, versuche eines zu erstellen
if ($logDir === null) {
    foreach ($logDirectories as $dir) {
        if ((is_dir($dir) || @mkdir($dir, 0755, true)) && is_writable($dir)) {
            $logDir = $dir;
            $logPath = $dir . '/app.log';
            break;
        }
    }
}

// Füge File-Handler hinzu, wenn ein Logging-Verzeichnis verfügbar ist
if ($logPath !== null) {
    try {
        $logger->pushHandler(new RotatingFileHandler($logPath, 0, Logger::INFO));
    } catch (Exception $e) {
        // Ignoriere Fehler bei der Erstellung des Log-Handlers
    }
}

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

// Check for required database migration and configuration
function checkTimeEffectMigration() {
    global $logger;
    
    // Skip checks for migration page itself and during installation
    $current_script = basename($_SERVER['SCRIPT_NAME']);
    if ($current_script === 'migrate.php' || 
        strpos($_SERVER['REQUEST_URI'], '/install/') !== false ||
        strpos($_SERVER['REQUEST_URI'], '/sql/') !== false) {
        return;
    }
    
    try {
        // Only check if we have database connection configured
        if (!isset($GLOBALS['_PJ_auth_table']) || !isset($GLOBALS['_PJ_db_host'])) {
            return; // No database configured yet
        }
        
        include_once(__DIR__ . '/include/aperetiv.inc.php');
        include_once(__DIR__ . '/include/migrations.inc.php');
        
        $migration_needed = false;
        $config_needed = false;
        
        // Check database migration status using MigrationManager  
        try {
            $migrationManager = new MigrationManager();
            $migration_needed = $migrationManager->migrationsNeeded();
        } catch (Exception $e) {
            $logger->warning('Database migration check failed', ['error' => $e->getMessage()]);
            return; // Skip if database not accessible
        }
        
        // Check configuration options
        if (!isset($GLOBALS['_PJ_allow_registration']) || 
            !isset($GLOBALS['_PJ_registration_email_confirm']) || 
            !isset($GLOBALS['_PJ_allow_password_recovery'])) {
            $config_needed = true;
        }
        
        // Show migration notice if needed
        if ($migration_needed || $config_needed) {
            $logger->info('TimeEffect migration required', [
                'database_migration' => $migration_needed,
                'config_update' => $config_needed
            ]);
            
            // Only show notice for web requests, not CLI
            if (php_sapi_name() !== 'cli' && !headers_sent()) {
                echo '<div style="background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 15px; margin: 10px; border-radius: 4px; font-family: Arial, sans-serif;">';
                echo '<strong>TimeEffect Migration Required</strong><br>';
                echo 'New features require database and configuration updates. ';
                echo '<a href="' . (isset($_SERVER['REQUEST_URI']) ? dirname($_SERVER['REQUEST_URI']) : '') . '/migrate.php" style="color: #007bff; text-decoration: none;">Click here to run migration</a>';
                echo '</div>';
                
                // Don't show the notice on every page - set a session flag
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                if (!isset($_SESSION['migration_notice_shown'])) {
                    $_SESSION['migration_notice_shown'] = true;
                }
            }
        }
    } catch (Exception $e) {
        $logger->error('Migration check error', ['error' => $e->getMessage()]);
    }
}

// Run migration check
checkTimeEffectMigration();

// Load Session/Auth compatibility layer for PHP 8.4
require_once __DIR__ . '/include/auth_compatibility.php';

// Note: Automatic database migrations are loaded after Database class is available
// See include/config.inc.php for migration execution

// Legacy PEAR compatibility is automatically loaded via composer.json files section
