<?php
/**
 * TimeEffect Database Migration System
 * Automatically checks and runs pending migrations
 */

class MigrationManager {
    private $db;
    private $current_version = 2; // Current schema version
    
    public function __construct() {
        $this->db = new Database();
        $this->ensureMigrationTable();
    }
    
    /**
     * Ensure migration tracking table exists
     */
    private function ensureMigrationTable() {
        $query = "CREATE TABLE IF NOT EXISTS " . $GLOBALS['_PJ_db_prefix'] . "migrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            version INT NOT NULL UNIQUE,
            migration_name VARCHAR(255) NOT NULL,
            executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX(version)
        ) ENGINE=MyISAM";
        
        $this->db->query($query);
    }
    
    /**
     * Get current database schema version
     */
    public function getCurrentVersion() {
        $query = "SELECT MAX(version) as version FROM " . $GLOBALS['_PJ_db_prefix'] . "migrations";
        $this->db->query($query);
        
        if ($this->db->next_record()) {
            return (int)$this->db->Record['version'];
        }
        
        return 0; // No migrations run yet
    }
    
    /**
     * Check if migrations are needed
     */
    public function needsMigration() {
        return $this->getCurrentVersion() < $this->current_version;
    }
    
    /**
     * Run all pending migrations
     */
    public function runPendingMigrations() {
        $current_version = $this->getCurrentVersion();
        $migrations_run = array();
        
        // Migration 1: User registration fields (if not already present)
        if ($current_version < 1) {
            if ($this->runMigration1()) {
                $migrations_run[] = 'User registration fields';
                $this->recordMigration(1, 'User registration fields');
            }
        }
        
        // Migration 2: Theme preference field
        if ($current_version < 2) {
            if ($this->runMigration2()) {
                $migrations_run[] = 'Theme preference field';
                $this->recordMigration(2, 'Theme preference field');
            }
        }
        
        return $migrations_run;
    }
    
    /**
     * Record a completed migration
     */
    private function recordMigration($version, $name) {
        $query = "INSERT INTO " . $GLOBALS['_PJ_db_prefix'] . "migrations (version, migration_name) 
                  VALUES (" . intval($version) . ", '" . addslashes($name) . "')";
        return $this->db->query($query);
    }
    
    /**
     * Migration 1: User registration fields
     */
    private function runMigration1() {
        try {
            // Check if fields already exist
            $query = "SHOW COLUMNS FROM " . $GLOBALS['_PJ_auth_table'] . " LIKE 'reset_token'";
            $this->db->query($query);
            if ($this->db->next_record()) {
                return true; // Already exists
            }
            
            // Add registration and password reset fields
            $queries = array(
                "ALTER TABLE " . $GLOBALS['_PJ_auth_table'] . " ADD COLUMN reset_token VARCHAR(64) NULL AFTER facsimile",
                "ALTER TABLE " . $GLOBALS['_PJ_auth_table'] . " ADD COLUMN reset_expires DATETIME NULL AFTER reset_token",
                "ALTER TABLE " . $GLOBALS['_PJ_auth_table'] . " ADD COLUMN email_confirmed TINYINT(1) DEFAULT 1 AFTER reset_expires",
                "ALTER TABLE " . $GLOBALS['_PJ_auth_table'] . " ADD COLUMN confirmation_token VARCHAR(64) NULL AFTER email_confirmed"
            );
            
            foreach ($queries as $query) {
                if (!$this->db->query($query)) {
                    return false;
                }
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Migration 1 failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Migration 2: Theme preference field
     */
    private function runMigration2() {
        try {
            // Check if field already exists
            $query = "SHOW COLUMNS FROM " . $GLOBALS['_PJ_auth_table'] . " LIKE 'theme_preference'";
            $this->db->query($query);
            if ($this->db->next_record()) {
                return true; // Already exists
            }
            
            // Add theme preference field
            $query = "ALTER TABLE " . $GLOBALS['_PJ_auth_table'] . " ADD COLUMN theme_preference VARCHAR(10) DEFAULT 'system' AFTER facsimile";
            return $this->db->query($query);
            
        } catch (Exception $e) {
            error_log("Migration 2 failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get migration history
     */
    public function getMigrationHistory() {
        $query = "SELECT * FROM " . $GLOBALS['_PJ_db_prefix'] . "migrations ORDER BY version";
        $this->db->query($query);
        
        $history = array();
        while ($this->db->next_record()) {
            $history[] = $this->db->Record;
        }
        
        return $history;
    }
}

/**
 * Check and run migrations if needed
 * Call this function during login/bootstrap
 */
function checkAndRunMigrations() {
    try {
        $migration_manager = new MigrationManager();
        
        if ($migration_manager->needsMigration()) {
            $migrations_run = $migration_manager->runPendingMigrations();
            
            // Log successful migrations
            if (!empty($migrations_run)) {
                error_log("TimeEffect: Automatic migrations completed: " . implode(', ', $migrations_run));
            }
            
            return $migrations_run;
        }
        
        return array(); // No migrations needed
        
    } catch (Exception $e) {
        error_log("TimeEffect: Migration check failed: " . $e->getMessage());
        return false;
    }
}
?>
