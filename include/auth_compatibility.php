<?php
/**
 * PEAR Auth Compatibility Layer for PHP 8.4
 * 
 * Diese Datei bietet Abwärtskompatibilität für den Legacy PEAR Auth-Code
 * mit modernen PHP 8.4 Session-Mechanismen
 */

// Debug-Log-Funktion für Session-Diagnose
function debug_session_log($message, $data = []) {
    $log_dir = __DIR__ . '/../logs';
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    
    $log_file = $log_dir . '/session_debug.log';
    $timestamp = date('Y-m-d H:i:s');
    $data_str = !empty($data) ? ' | ' . json_encode($data, JSON_UNESCAPED_SLASHES) : '';
    $log_entry = "[$timestamp] $message$data_str\n";
    
    file_put_contents($log_file, $log_entry, FILE_APPEND);
}

// Session-Cookie-Kompatibilität für PHP 8.4 sicherstellen
if (!function_exists('ensure_session_compatibility')) {
    function ensure_session_compatibility() {
        // Log session status before changes
        debug_session_log('Session Status Before', [
            'session_status' => session_status(),
            'session_name' => session_name(),
            'session_id' => session_id(),
            'cookie_params' => session_get_cookie_params(),
            'session_module' => session_module_name(),
            'session_save_path' => session_save_path(),
            'php_version' => PHP_VERSION
        ]);
        
        // Stelle sicher, dass der Session-Cookie-Pfad korrekt ist
        if (session_status() == PHP_SESSION_NONE) {
            // Setze sichere Session-Parameter für PHP 8.4
            ini_set('session.use_strict_mode', 1);
            ini_set('session.use_cookies', 1);
            ini_set('session.use_only_cookies', 1);
            ini_set('session.gc_maxlifetime', $GLOBALS['_PJ_session_length'] ?? 3600);
            
            // Session-Cookie-Parameter setzen
            $cookie_params = [
                'lifetime' => $GLOBALS['_PJ_session_length'] ?? 3600,
                'path' => '/',
                'domain' => '',
                'secure' => isset($_SERVER['HTTPS']),
                'httponly' => true,
            ];
            
            // SameSite für PHP 7.3+ hinzufügen
            if (version_compare(PHP_VERSION, '7.3.0', '>=')) {
                $cookie_params['samesite'] = 'Lax';
            }
            
            debug_session_log('Setting cookie params', $cookie_params);
            session_set_cookie_params($cookie_params);
        }
        
        // Ensure correct session.save_path is set
        $save_path = ini_get('session.save_path');
        if (empty($save_path) || !is_writable($save_path)) {
            $tmp_dir = sys_get_temp_dir();
            debug_session_log('Setting session save path', ['old' => $save_path, 'new' => $tmp_dir]);
            session_save_path($tmp_dir);
        }
        
        // Force session_start if not already started
        if (session_status() == PHP_SESSION_NONE) {
            debug_session_log('Starting session explicitly');
            @session_start();
        }
        
        // Log session status after changes
        debug_session_log('Session Status After', [
            'session_status' => session_status(),
            'session_name' => session_name(),
            'session_id' => session_id(),
            'cookie_params' => session_get_cookie_params(),
            'session_module' => session_module_name(),
            'session_save_path' => session_save_path(),
            'cookies' => isset($_COOKIE) ? array_keys($_COOKIE) : [],
            'session_data' => !empty($_SESSION) ? array_keys($_SESSION) : []
        ]);
    }
    
    // Session-Debugging Hook
    function add_session_debug_hooks() {
        // Monitor Session Headers
        header_register_callback(function() {
            $headers = headers_list();
            $cookie_headers = array_filter($headers, function($h) { return stripos($h, 'Set-Cookie') === 0; });
            debug_session_log('Headers being sent', ['cookies' => $cookie_headers]);
        });
    }
    
    // Automatisch ausführen vor jeder Session-Initialisierung
    debug_session_log('Auth compatibility layer loaded');
    ensure_session_compatibility();
    add_session_debug_hooks();
}
