<?php
/**
 * Einfaches Script zum Generieren der .env-Datei aus den Konfigurationsparametern
 * Dieses Script vermeidet Abhängigkeiten von anderen Dateien und Sessions
 */

echo "🔄 Generiere .env aus Konfigurationsparametern...\n";

// Verzeichnisse festlegen
$base_dir = realpath(dirname(__DIR__));
$env_file = $base_dir . '/.env';

// Konfigurationswerte extrahieren
$config_file = $base_dir . '/include/config.inc.php';
if (!file_exists($config_file)) {
    die("❌ Konfigurationsdatei nicht gefunden: $config_file\n");
}

// Datei manuell parsen, um Abhängigkeiten zu vermeiden
$config_content = file_get_contents($config_file);

// Konfigurationswerte mit Standardwerten
$config = array(
    'db_host' => 'localhost',
    'db_name' => 'timeeffect',
    'db_user' => 'root',
    'db_password' => '',
    'db_prefix' => 'te_',
    'debug' => 'false',
    'http_root' => '',
    'root_path' => $base_dir
);

// Extrahiere Werte mit regulären Ausdrücken
if (preg_match('/_PJ_db_host\s*=\s*[\'"](.*?)[\'"]/i', $config_content, $matches)) {
    $config['db_host'] = $matches[1];
}

if (preg_match('/_PJ_db_name\s*=\s*[\'"](.*?)[\'"]/i', $config_content, $matches)) {
    $config['db_name'] = $matches[1];
}

if (preg_match('/_PJ_db_user\s*=\s*[\'"](.*?)[\'"]/i', $config_content, $matches)) {
    $config['db_user'] = $matches[1];
}

if (preg_match('/_PJ_db_password\s*=\s*[\'"](.*?)[\'"]/i', $config_content, $matches)) {
    $config['db_password'] = $matches[1];
}

if (preg_match('/_PJ_db_prefix\s*=\s*[\'"](.*?)[\'"]/i', $config_content, $matches)) {
    $config['db_prefix'] = $matches[1];
}

if (preg_match('/\$debug\s*=\s*(true|false|1|0)/i', $config_content, $matches)) {
    $config['debug'] = ($matches[1] === 'true' || $matches[1] === '1') ? 'true' : 'false';
}

if (preg_match('/_PJ_http_root\s*=\s*[\'"](.*?)[\'"]/i', $config_content, $matches)) {
    $config['http_root'] = $matches[1];
}

// Sicheren Key generieren
function generate_secure_key() {
    if (function_exists('random_bytes')) {
        try {
            return base64_encode(random_bytes(32));
        } catch (Exception $e) {
            // Fallback wenn random_bytes fehlschlägt
        }
    }
    
    // Fallback für ältere PHP-Versionen
    $bytes = '';
    for ($i = 0; $i < 32; $i++) {
        $bytes .= chr(mt_rand(0, 255));
    }
    return base64_encode($bytes);
}

// Sichere Schlüsselgenerierung durchführen
$secure_key = generate_secure_key();
$current_date = date('Y-m-d H:i:s');

// .env Inhalt generieren
$env_content = <<<EOT
# TimeEffect Environment Configuration
# Generiert am: {$current_date}

# Datenbank-Konfiguration
DB_HOST={$config['db_host']}
DB_DATABASE={$config['db_name']}
DB_USERNAME={$config['db_user']}
DB_PASSWORD={$config['db_password']}
DB_PREFIX={$config['db_prefix']}

# Anwendungseinstellungen
APP_ENV=production
APP_DEBUG={$config['debug']}
APP_HTTP_ROOT={$config['http_root']}
APP_ROOT_PATH={$config['root_path']}

# Logging
LOG_LEVEL=error
LOG_CHANNEL=single

# Session-Konfiguration
SESSION_LIFETIME=3600
SESSION_SECURE=false

# Sicherheit
APP_KEY=base64:{$secure_key}

EOT;

// .env-Datei schreiben
if (file_put_contents($env_file, $env_content)) {
    echo "\n✅ .env-Datei erfolgreich generiert: $env_file\n";
    echo "\n📋 Extrahierte Konfiguration:\n";
    echo "   - Datenbank-Host: {$config['db_host']}\n";
    echo "   - Datenbank-Name: {$config['db_name']}\n";
    echo "   - Datenbank-Benutzer: {$config['db_user']}\n";
    echo "   - Datenbank-Präfix: {$config['db_prefix']}\n";
    echo "   - Debug-Modus: {$config['debug']}\n";
    echo "   - HTTP-Root: {$config['http_root']}\n";
    echo "\n🔧 Bitte überprüfe und passe die .env-Datei bei Bedarf an.\n";
} else {
    echo "❌ Fehler beim Schreiben der .env-Datei: $env_file\n";
}
