<?php
/**
 * PEAR Auth Compatibility Layer for PHP 8.4
 * 
 * Diese Datei bietet Abwärtskompatibilität für den Legacy PEAR Auth-Code
 * mit modernen PHP 8.4 Session-Mechanismen
 */

// Session-Cookie-Kompatibilität für PHP 8.4 sicherstellen
if (!function_exists('ensure_session_compatibility')) {
    function ensure_session_compatibility() {
        // Stelle sicher, dass der Session-Cookie-Pfad korrekt ist
        if (session_status() == PHP_SESSION_NONE) {
            // Setze sichere Session-Parameter für PHP 8.4
            ini_set('session.use_strict_mode', 1);
            ini_set('session.use_cookies', 1);
            ini_set('session.use_only_cookies', 1);
            
            // Der SameSite-Parameter ist wichtig in PHP 8.4
            if (version_compare(PHP_VERSION, '7.3.0', '>=')) {
                session_set_cookie_params([
                    'lifetime' => $GLOBALS['_PJ_session_length'] ?? 3600,
                    'path' => '/',
                    'domain' => '',
                    'secure' => isset($_SERVER['HTTPS']),
                    'httponly' => true,
                    'samesite' => 'Lax'
                ]);
            } else {
                // Fallback für ältere PHP-Versionen
                session_set_cookie_params(
                    $GLOBALS['_PJ_session_length'] ?? 3600,
                    '/',
                    '',
                    isset($_SERVER['HTTPS']),
                    true
                );
            }
        }
    }
    
    // Automatisch ausführen vor jeder Session-Initialisierung
    ensure_session_compatibility();
}
