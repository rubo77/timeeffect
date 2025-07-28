<?php
/**
 * TimeEffect Migration Manager
 * 
 * Handles database schema migrations according to DATABASE_MIGRATIONS.md specification
 * Provides version-based sequential migration execution with proper tracking and error handling
 */

class MigrationManager {
    private $db;
    private $current_version = 1; // Current target version - increment for new migrations
    private $migrations_table;

    public function __construct() {
        $this->db = new Database();
        $this->migrations_table = $GLOBALS['_PJ_db_prefix'] . 'migrations';
    }

    /**
     * Get the current database version from migrations table
     */
    public function getCurrentVersion() {
        try {
            // Check if migrations table exists
            $query = "SHOW TABLES LIKE '" . $this->migrations_table . "'";
            $this->db->query($query);
            
            if (!$this->db->next_record()) {
                return 0; // No migrations table exists yet
            }
            
            // Get highest executed migration version
            $query = "SELECT MAX(version) as max_version FROM " . $this->migrations_table;
            $this->db->query($query);
            
            if ($this->db->next_record()) {
                return (int)$this->db->Record['max_version'];
            }
            
            return 0;
        } catch (Exception $e) {
            error_log("Error getting current migration version: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Check if migrations are needed
     */
    public function migrationsNeeded() {
        return $this->getCurrentVersion() < $this->current_version;
    }

    /**
     * Create the migrations tracking table
     */
    private function createMigrationsTable() {
        try {
            $query = "CREATE TABLE " . $this->migrations_table . " (
                id INT AUTO_INCREMENT PRIMARY KEY,
                version INT NOT NULL UNIQUE,
                migration_name VARCHAR(255) NOT NULL,
                executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX(version)
            ) ENGINE=MyISAM";
            
            return $this->db->query($query);
        } catch (Exception $e) {
            error_log("Failed to create migrations table: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Record a successful migration in the tracking table
     */
    private function recordMigration($version, $migration_name) {
        try {
            // Ensure migrations table exists
            $query = "SHOW TABLES LIKE '" . $this->migrations_table . "'";
            $this->db->query($query);
            
            if (!$this->db->next_record()) {
                if (!$this->createMigrationsTable()) {
                    return false;
                }
            }
            
            $query = "INSERT INTO " . $this->migrations_table . " (version, migration_name) 
                      VALUES (" . (int)$version . ", '" . addslashes($migration_name) . "')";
            
            return $this->db->query($query);
        } catch (Exception $e) {
            error_log("Failed to record migration: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Migration 1: Add user registration and password recovery fields
     */
    private function runMigration1() {
        try {
            // Check if migration is already applied
            $query = "SHOW COLUMNS FROM " . $GLOBALS['_PJ_auth_table'] . " LIKE 'confirmed'";
            $this->db->query($query);
            if ($this->db->next_record()) {
                return true; // Already applied
            }

            // Execute migration SQL - add all required fields
            $migrations = [
                "ALTER TABLE " . $GLOBALS['_PJ_auth_table'] . " 
                 ADD COLUMN reset_token VARCHAR(64) NULL AFTER facsimile",
                
                "ALTER TABLE " . $GLOBALS['_PJ_auth_table'] . " 
                 ADD COLUMN reset_expires DATETIME NULL AFTER reset_token",
                
                "ALTER TABLE " . $GLOBALS['_PJ_auth_table'] . " 
                 ADD COLUMN confirmed TINYINT(1) DEFAULT 1 AFTER reset_expires",
                
                "ALTER TABLE " . $GLOBALS['_PJ_auth_table'] . " 
                 ADD COLUMN confirmation_token VARCHAR(64) NULL AFTER confirmed"
            ];

            foreach ($migrations as $sql) {
                if (!$this->db->query($sql)) {
                    throw new Exception("Failed to execute: " . $sql);
                }
            }

            // Add indexes for performance
            $this->db->query("ALTER TABLE " . $GLOBALS['_PJ_auth_table'] . " ADD INDEX idx_reset_token (reset_token)");
            $this->db->query("ALTER TABLE " . $GLOBALS['_PJ_auth_table'] . " ADD INDEX idx_confirm_token (confirmation_token)");

            return true;
        } catch (Exception $e) {
            error_log("Migration 1 failed: " . $e->getMessage());
            echo "<div style='background: #fee; border: 1px solid #fcc; padding: 15px; margin: 10px; border-radius: 4px;'>";
            echo "<strong>ERROR:</strong> Migration 1 failed: " . htmlspecialchars($e->getMessage());
            echo "</div>";
            return false;
        }
    }

    /**
     * Run all pending migrations
     */
    public function runPendingMigrations() {
        $current_version = $this->getCurrentVersion();
        $migrations_run = array();

        try {
            // Migration 1: User registration and password recovery fields
            if ($current_version < 1) {
                if ($this->runMigration1()) {
                    $migrations_run[] = 'Added user registration and password recovery database fields';
                    $this->recordMigration(1, 'Added user registration and password recovery database fields');
                } else {
                    return false; // Stop if migration fails
                }
            }

            return $migrations_run;
        } catch (Exception $e) {
            error_log("Error running migrations: " . $e->getMessage());
            echo "<div style='background: #fee; border: 1px solid #fcc; padding: 15px; margin: 10px; border-radius: 4px;'>";
            echo "<strong>ERROR:</strong> Migration system error: " . htmlspecialchars($e->getMessage());
            echo "</div>";
            return false;
        }
    }

    /**
     * Get migration status for display
     */
    public function getMigrationStatus() {
        $current = $this->getCurrentVersion();
        $target = $this->current_version;
        
        return [
            'current_version' => $current,
            'target_version' => $target,
            'migrations_needed' => $current < $target,
            'migrations_table_exists' => $this->migrationsTableExists()
        ];
    }

    /**
     * Check if migrations table exists
     */
    private function migrationsTableExists() {
        try {
            $query = "SHOW TABLES LIKE '" . $this->migrations_table . "'";
            $this->db->query($query);
            return $this->db->next_record();
        } catch (Exception $e) {
            return false;
        }
    }
}
?>