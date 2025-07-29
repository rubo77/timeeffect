# Database Migration System

This document describes the database migration system implemented in TimeEffect to handle schema changes and upgrades automatically.

## Overview

The migration system allows for automatic database schema updates when users log in, ensuring that all installations can be upgraded seamlessly without manual intervention.

## Architecture

### Core Components

1. **MigrationManager Class** (`include/migrations.inc.php`)
   - Manages migration execution and tracking
   - Tracks current schema version
   - Executes pending migrations automatically

2. **Migration Table** (`{prefix}migrations`)
   - Stores executed migration history
   - Tracks version numbers and execution timestamps
   - Prevents duplicate execution of migrations

3. **Integration Points**
   - Automatically executed during login process (`database.inc.php`)
   - Runs after database connection is established
   - Only executes when configuration is properly loaded

## How It Works

### Migration Execution Flow

1. **Login Process**: When a user logs in, migrations are checked automatically
2. **Version Check**: System compares current DB version with target version
3. **Execution**: Only pending migrations are executed in sequence
4. **Tracking**: Each successful migration is recorded in the migrations table
5. **Error Handling**: Failed migrations are logged and prevent further execution

### Migration Versioning

- Each migration has a unique version number (integer)
- Migrations are executed in sequential order
- Current target version is defined in `MigrationManager::$current_version`
- Database tracks the highest executed migration version

## Creating New Migrations

### Step 1: Update MigrationManager Class

1. **Increment Version Number**:
   ```php
   private $current_version = 3; // Increment this number in migrations.inc.php
   ```

2. **Add Migration Method**:
   ```php
   private function runMigration3() {
       try {
           // Check if migration is already applied
           $query = "SHOW COLUMNS FROM " . $GLOBALS['_PJ_auth_table'] . " LIKE 'new_field'";
           $this->db->query($query);
           if ($this->db->next_record()) {
               return true; // Already applied
           }
           
           // Execute migration SQL
           $query = "ALTER TABLE " . $GLOBALS['_PJ_auth_table'] . " 
                     ADD COLUMN new_field VARCHAR(50) DEFAULT 'default_value' 
                     AFTER existing_field";
           return $this->db->query($query);
           
       } catch (Exception $e) {
           error_log("Migration 3 failed: " . $e->getMessage());
           return false;
       }
   }
   ```

3. **Add to Execution Logic**:
   ```php
   public function runPendingMigrations() {
       $current_version = $this->getCurrentVersion();
       $migrations_run = array();
       
       // Add your new migration here
       if ($current_version < 3) {
           if ($this->runMigration3()) {
               $migrations_run[] = 'Description of Migration 3';
               $this->recordMigration(3, 'Description of Migration 3');
           }
       }
       
       return $migrations_run;
   }
   ```

### Step 2: Migration Best Practices

#### Safety Checks
- Always check if the migration is already applied
- Use `SHOW COLUMNS`, `SHOW TABLES`, or similar to detect existing changes
- Return `true` if migration is already applied

#### Error Handling
- Wrap all SQL operations in try-catch blocks
- Log detailed error messages with `error_log()` and also show the ERROR directly on screen
- Return `false` on failure to prevent further migrations

#### SQL Guidelines
- Use global variables for table names (`$GLOBALS['_PJ_auth_table']`)
- Use `addslashes()` for string escaping (not `add_slashes()`)
- Specify column positions with `AFTER column_name` when relevant
- Use appropriate data types and defaults

#### Example Migration Patterns

**Adding a Column**:
```php
private function runMigrationX() {
    try {
        // Check if column exists
        $query = "SHOW COLUMNS FROM " . $GLOBALS['_PJ_auth_table'] . " LIKE 'new_column'";
        $this->db->query($query);
        if ($this->db->next_record()) {
            return true; // Already exists
        }
        
        // Add column
        $query = "ALTER TABLE " . $GLOBALS['_PJ_auth_table'] . " 
                  ADD COLUMN new_column VARCHAR(255) DEFAULT NULL 
                  AFTER existing_column";
        return $this->db->query($query);
        
    } catch (Exception $e) {
        error_log("Migration X failed: " . $e->getMessage());
        return false;
    }
}
```

**Creating a Table**:
```php
private function runMigrationY() {
    try {
        // Check if table exists
        $query = "SHOW TABLES LIKE '" . $GLOBALS['_PJ_db_prefix'] . "new_table'";
        $this->db->query($query);
        if ($this->db->next_record()) {
            return true; // Already exists
        }
        
        // Create table
        $query = "CREATE TABLE " . $GLOBALS['_PJ_db_prefix'] . "new_table (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX(name)
        ) ENGINE=MyISAM";
        return $this->db->query($query);
        
    } catch (Exception $e) {
        error_log("Migration Y failed: " . $e->getMessage());
        return false;
    }
}
```

**Modifying Existing Data**:
```php
private function runMigrationZ() {
    try {
        // Check if migration was already applied (use a marker or check data state)
        $query = "SELECT COUNT(*) as count FROM " . $GLOBALS['_PJ_auth_table'] . " 
                  WHERE some_field = 'new_value'";
        $this->db->query($query);
        $this->db->next_record();
        if ($this->db->Record['count'] > 0) {
            return true; // Already applied
        }
        
        // Update data
        $query = "UPDATE " . $GLOBALS['_PJ_auth_table'] . " 
                  SET some_field = 'new_value' 
                  WHERE some_condition = 'old_value'";
        return $this->db->query($query);
        
    } catch (Exception $e) {
        error_log("Migration Z failed: " . $e->getMessage());
        return false;
    }
}
```

## Testing Migrations

### Local Testing
1. **Backup Database**: Always backup before testing migrations
2. **Test on Clean Install**: Verify migrations work on fresh installations
3. **Test on Existing Data**: Ensure migrations work with existing data
4. **Test Rollback Scenarios**: Verify behavior when migrations fail

### Verification Steps
1. Check that migration executes without errors
2. Verify database schema changes are applied correctly
3. Confirm application functionality works with new schema
4. Test that migration doesn't run twice (idempotent)

## Deployment Considerations

### Production Deployment
- Migrations run automatically on first user login after deployment
- No manual intervention required
- Failed migrations prevent application access until resolved

### Rollback Strategy
- Currently no automatic rollback mechanism
- Manual database restoration required for failed migrations
- Plan rollback procedures before deploying schema changes

### Performance Considerations
- Large table alterations may cause downtime
- Consider maintenance windows for significant schema changes
- Test migration performance on production-sized datasets

## Troubleshooting

### Common Issues

**Migration Fails to Execute**:
- Check error logs for detailed error messages
- Verify database permissions
- Ensure all required global variables are set

**Migration Runs Multiple Times**:
- Check that safety checks are properly implemented
- Verify migration table is being updated correctly
- Ensure idempotent migration design

**Application Breaks After Migration**:
- Check that all code is compatible with new schema
- Verify default values are appropriate
- Test all application features after migration

### Debug Information
- Migration history: Check `{prefix}migrations` table
- Error logs: Check PHP error logs and application logs
- Current version: Use `MigrationManager::getCurrentVersion()`

## Examples from TimeEffect

### Migration 1: User Registration Fields
```php
// Added password reset and email confirmation fields
ALTER TABLE auth ADD COLUMN reset_token VARCHAR(64) NULL AFTER facsimile;
ALTER TABLE auth ADD COLUMN reset_expires DATETIME NULL AFTER reset_token;
ALTER TABLE auth ADD COLUMN email_confirmed TINYINT(1) DEFAULT 1 AFTER reset_expires;
ALTER TABLE auth ADD COLUMN confirmation_token VARCHAR(64) NULL AFTER email_confirmed;
```

### Migration 2: Theme Preference
```php
// Added user theme preference for dark/light mode
ALTER TABLE auth ADD COLUMN theme_preference VARCHAR(10) DEFAULT 'system' AFTER facsimile;
```

## Future Enhancements

### Potential Improvements
- Rollback mechanism for failed migrations
- Migration file-based system (separate files per migration)
- Database backup before migration execution
- Migration dry-run mode for testing
- Better error reporting and recovery options

### Integration Opportunities
- CI/CD pipeline integration
- Automated testing of migrations
- Migration documentation generation
- Schema versioning and comparison tools

---

## Quick Reference

### Adding a New Migration Checklist
- [ ] Increment `$current_version` in MigrationManager
- [ ] Create `runMigrationX()` method with safety checks
- [ ] Add migration to `runPendingMigrations()` method
- [ ] Test on clean database
- [ ] Test on existing database
- [ ] Verify idempotent behavior
- [ ] Document the migration purpose
- [ ] Test application functionality after migration

### Key Files
- `include/migrations.inc.php` - Migration manager class
- `include/database.inc.php` - Migration execution trigger
- `docs/DATABASE_MIGRATIONS.md` - This documentation

### Migration Table Schema
```sql
CREATE TABLE {prefix}migrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    version INT NOT NULL UNIQUE,
    migration_name VARCHAR(255) NOT NULL,
    executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX(version)
) ENGINE=MyISAM;
```
