<?php
/**
 * Login Attempt Tracker for Brute Force Protection
 * 
 * This class handles tracking and limiting login attempts to prevent
 * brute force attacks on the authentication system.
 */

if(!isset($_PJ_root)) {
    print "<b>FEHLER:</b> \$_PJ_root ist <b>nicht festgelegt</b>! (" . __FILE__ . ", Zeile: " . __LINE__ . ")";
    exit;
}

require_once($_PJ_root . '/include/database.inc.php');

class LoginAttemptTracker {
    
    // Configuration constants
    const MAX_ATTEMPTS_PER_IP = 10;       // Max attempts per IP address
    const MAX_ATTEMPTS_PER_USER = 3;      // Max attempts per username
    const LOCKOUT_DURATION = 60;          // Lockout duration in seconds (1 minute)
    const CLEANUP_INTERVAL = 3600;        // Clean old records every hour
    
    private $db;
    private $table_name;
    public $table_exists = false;
    
    public function __construct() {
        $this->db = new Database();
        $this->table_name = $GLOBALS['_PJ_table_prefix'] . 'login_attempts';
        
        // Check if table exists
        $this->table_exists = $this->ensureTableExists();
        
        // Clean up old records periodically (only if table exists)
        if ($this->table_exists) {
            $this->cleanupOldAttempts();
        }
    }
    
    /**
     * Get client IP address (handles proxy scenarios)
     */
    private function getClientIP() {
        // Check for various headers that might contain the real IP
        $headers = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];
        
        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = $_SERVER[$header];
                // Handle comma-separated IPs (X-Forwarded-For can contain multiple IPs)
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                // Validate IP address
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    
    /**
     * Check if IP or username is currently locked out
     */
    public function isLockedOut($username = '') {
        // If table doesn't exist, no lockout protection is available
        if (!$this->table_exists) {
            return ['locked' => false];
        }
        
        $ip = $this->getClientIP();
        $lockout_time = date('Y-m-d H:i:s', time() - self::LOCKOUT_DURATION);
        
        // Check IP-based lockout
        $ip_query = sprintf(
            "SELECT COUNT(*) as attempt_count FROM `%s` WHERE ip_address = '%s' AND success = 0 AND attempt_time > '%s'",
            $this->table_name,
            mysqli_real_escape_string($this->db->Link_ID, $ip),
            $lockout_time
        );
        
        $this->db->query($ip_query);
        if ($this->db->next_record()) {
            if ($this->db->f('attempt_count') >= self::MAX_ATTEMPTS_PER_IP) {
                return [
                    'locked' => true,
                    'reason' => 'ip',
                    'attempts' => $this->db->f('attempt_count'),
                    'lockout_until' => date('H:i:s', time() + self::LOCKOUT_DURATION)
                ];
            }
        }
        
        // Check username-based lockout if username provided
        if (!empty($username)) {
            $user_query = sprintf(
                "SELECT COUNT(*) as attempt_count FROM `%s` WHERE username = '%s' AND success = 0 AND attempt_time > '%s'",
                $this->table_name,
                mysqli_real_escape_string($this->db->Link_ID, $username),
                $lockout_time
            );
            
            $this->db->query($user_query);
            if ($this->db->next_record()) {
                if ($this->db->f('attempt_count') >= self::MAX_ATTEMPTS_PER_USER) {
                    return [
                        'locked' => true,
                        'reason' => 'username',
                        'attempts' => $this->db->f('attempt_count'),
                        'lockout_until' => date('H:i:s', time() + self::LOCKOUT_DURATION)
                    ];
                }
            }
        }
        
        return ['locked' => false];
    }
    
    /**
     * Record a login attempt
     */
    public function recordAttempt($username, $success = false) {
        // If table doesn't exist, cannot record attempts
        if (!$this->table_exists) {
            return;
        }
        
        $ip = $this->getClientIP();
        
        $query = sprintf(
            "INSERT INTO `%s` (ip_address, username, attempt_time, success) VALUES ('%s', '%s', NOW(), %d)",
            $this->table_name,
            mysqli_real_escape_string($this->db->Link_ID, $ip),
            mysqli_real_escape_string($this->db->Link_ID, $username),
            $success ? 1 : 0
        );
        
        $this->db->query($query);
        
        // Log the attempt for security monitoring
        if (isset($GLOBALS['logger'])) {
            $GLOBALS['logger']->info('Login attempt recorded', [
                'ip' => $ip,
                'username' => $username,
                'success' => $success,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
            ]);
        }
    }
    
    /**
     * Clear successful attempts for user (called on successful login)
     */
    public function clearAttempts($username) {
        // If table doesn't exist, nothing to clear
        if (!$this->table_exists) {
            return;
        }
        
        $ip = $this->getClientIP();
        
        // Clear attempts for both IP and username
        $queries = [
            sprintf(
                "DELETE FROM `%s` WHERE ip_address = '%s' AND success = 0",
                $this->table_name,
                mysqli_real_escape_string($this->db->Link_ID, $ip)
            ),
            sprintf(
                "DELETE FROM `%s` WHERE username = '%s' AND success = 0",
                $this->table_name,
                mysqli_real_escape_string($this->db->Link_ID, $username)
            )
        ];
        
        foreach ($queries as $query) {
            $this->db->query($query);
        }
    }
    
    /**
     * Get remaining attempts before lockout
     */
    public function getRemainingAttempts($username = '') {
        // If table doesn't exist, return max attempts (no restrictions)
        if (!$this->table_exists) {
            return min(self::MAX_ATTEMPTS_PER_IP, self::MAX_ATTEMPTS_PER_USER);
        }
        
        $lockout_status = $this->isLockedOut($username);
        
        if ($lockout_status['locked']) {
            return 0;
        }
        
        $ip = $this->getClientIP();
        $lockout_time = date('Y-m-d H:i:s', time() - self::LOCKOUT_DURATION);
        
        // Get current IP attempts
        $ip_query = sprintf(
            "SELECT COUNT(*) as attempt_count FROM `%s` WHERE ip_address = '%s' AND success = 0 AND attempt_time > '%s'",
            $this->table_name,
            mysqli_real_escape_string($this->db->Link_ID, $ip),
            $lockout_time
        );
        
        $this->db->query($ip_query);
        $ip_attempts = 0;
        if ($this->db->next_record()) {
            $ip_attempts = $this->db->f('attempt_count');
        }
        
        $remaining_ip = max(0, self::MAX_ATTEMPTS_PER_IP - $ip_attempts);
        
        if (!empty($username)) {
            // Get current username attempts
            $user_query = sprintf(
                "SELECT COUNT(*) as attempt_count FROM `%s` WHERE username = '%s' AND success = 0 AND attempt_time > '%s'",
                $this->table_name,
                mysqli_real_escape_string($this->db->Link_ID, $username),
                $lockout_time
            );
            
            $this->db->query($user_query);
            $user_attempts = 0;
            if ($this->db->next_record()) {
                $user_attempts = $this->db->f('attempt_count');
            }
            
            $remaining_user = max(0, self::MAX_ATTEMPTS_PER_USER - $user_attempts);
            return min($remaining_ip, $remaining_user);
        }
        
        return $remaining_ip;
    }
    
    /**
     * Clean up old login attempt records
     */
    private function cleanupOldAttempts() {
        // If table doesn't exist, nothing to clean up
        if (!$this->table_exists) {
            return;
        }
        
        // Only run cleanup occasionally to avoid performance impact
        if (rand(1, 100) <= 5) { // 5% chance on each instantiation
            $cleanup_time = date('Y-m-d H:i:s', time() - (self::CLEANUP_INTERVAL * 24 * 20)); // Keep 20 days of data
            
            $query = sprintf(
                "DELETE FROM `%s` WHERE attempt_time < '%s'",
                $this->table_name,
                $cleanup_time
            );
            
            $this->db->query($query);
        }
    }
    
    /**
     * Check if login attempts table exists
     */
    public function ensureTableExists() {
        $table_check = sprintf("SHOW TABLES LIKE '%s'", $this->table_name);
        $this->db->query($table_check);
        
        if (!$this->db->next_record()) {
            // Table doesn't exist - this should be created through proper installation
            if (isset($GLOBALS['logger'])) {
                $GLOBALS['logger']->error('Login attempts table missing. Please run database migration/installation.');
            }
            return false;
        }
        return true;
    }
}
?>