# Database Migrations

TimeEffect automatically updates the database schema every time a user logs in.
Only exceptional, non-additive operations (e.g. renaming a table) require a manual SQL script.

## Automatic flow
File | Role
---- | ----
`include/migrations.inc.php` | `MigrationManager` with incremental `runMigrationX()` methods.
`include/database.inc.php`  | Triggers migrations during login.

### Adding a migration
1. Increment `private $current_version` in `MigrationManager`.
2. Add `runMigrationX()` containing idempotent SQL (use `SHOW TABLES/COLUMNS`).
3. Deploy – the migration runs automatically on the next login.

## Manual scripts (rare)
Place SQL files in `sql/migrations/` only when an automatic PHP migration is impossible.
Run `php sql/migrate.php` once; applied files are tracked in `{prefix}migrations`.

## Guidelines
* Prefer additive changes (new columns/tables); they never need rollback.
* Avoid destructive changes; if unavoidable, provide a rollback script.
* Use clear sequential names like `002_add_invoice_number.sql`.
* Always test on a copy of production data.

- **Trigger**: Automatic during login via `database.inc.php`
- **Tracking**: `{prefix}migrations` table
- **Safety**: Idempotent with rollback support

### Adding New Automatic Migration

1. **Increment version** in `MigrationManager::$current_version`
2. **Add migration method**:
```php
private function runMigrationX() {
    try {
        // Safety check
        $query = "SHOW TABLES LIKE '" . $GLOBALS['_PJ_db_prefix'] . "new_table'";
        $this->db->query($query);
        if ($this->db->next_record()) return true;
        
        // Create table
        $query = "CREATE TABLE " . $GLOBALS['_PJ_db_prefix'] . "new_table (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX(name)
        ) ENGINE=MyISAM";
        return $this->db->query($query);
    } catch (Exception $e) {
        error_log("Migration X failed: " . $e->getMessage());
        return false;
    }
}
```
3. **Add to execution logic** in `runPendingMigrations()`

### Manual Migrations

### When to Use
- Complex schema changes
- Data migrations
- One-time administrative tasks

### File Structure
- **Location**: `sql/migrations/`
- **Naming**: `XXX_description.sql`
- **Execution**: `cd sql && php migrate.php`

### Template
```sql
-- Migration: Description
-- Date: YYYY-MM-DD
-- Rollback: DROP TABLE `<%db_prefix%>table_name`;

CREATE TABLE IF NOT EXISTS `<%db_prefix%>table_name` (
  id INT AUTO_INCREMENT PRIMARY KEY,
  -- columns here
) ENGINE=MyISAM;
```

### Testing Migrations

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

### Deployment Considerations

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

### Manual Migration Example
```sql
-- File: sql/migration_add_registration_features.sql
-- Execute manually: mysql -u user -p database < sql/migration_add_registration_features.sql
-- Replace <%table_prefix%> with your actual table prefix (e.g. te_)
ALTER TABLE `<%table_prefix%>auth` 
ADD COLUMN `confirmed` tinyint(1) NOT NULL DEFAULT '1' AFTER `facsimile`,
ADD COLUMN `confirmation_token` varchar(64) DEFAULT NULL AFTER `confirmed`;
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

### For Existing Installations

To apply pending migrations to an existing database:

1. Navigate to the sql directory:
   ```bash
   cd sql/
   ```

2. Run the migration script:
   ```bash
   php migrate.php
   ```

3. The script will:
   - Create a `migrations` tracking table if it doesn't exist
   - Check which migrations have already been applied
   - Apply any pending migrations in order
   - Record successful migrations to prevent re-application

## Migration Structure

Each migration file should:
- Use the `<%db_prefix%>` placeholder for table names
- Include appropriate comments describing the change
- Be idempotent (safe to run multiple times)
- Use `CREATE TABLE IF NOT EXISTS` for new tables
- Use `ALTER TABLE` for schema modifications

Example migration:
```sql
-- Migration: Add login attempts table for brute force protection
-- Date: 2025-01-01
-- Description: Creates the login_attempts table to track failed login attempts

CREATE TABLE IF NOT EXISTS `<%db_prefix%>login_attempts` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `ip_address` varchar(45) NOT NULL COMMENT 'IP address of the attempt',
  `username` varchar(50) NOT NULL default '' COMMENT 'Username attempted',
  `attempt_time` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `success` tinyint(1) NOT NULL default 0,
  PRIMARY KEY  (`id`),
  KEY `ip_time` (`ip_address`, `attempt_time`),
  KEY `username_time` (`username`, `attempt_time`)
) ENGINE=MyISAM COMMENT='Tracks login attempts for brute force protection';
```

## Current Migrations

| Migration | Description | Status |
|-----------|-------------|---------|
| 001_add_login_attempts_table.sql | Adds brute force protection table | Available |

## Best Practices

1. **Never modify existing migrations** - Create new ones to make changes
2. **Test migrations** on a copy of production data before applying
3. **Backup your database** before running migrations in production
4. **Use descriptive names** for migration files
5. **Include rollback instructions** in migration comments when possible

## Troubleshooting

### Migration Fails
- Check database connectivity and permissions
- Verify the migration SQL syntax
- Look for conflicts with existing data or schema

### Migration Already Applied
- The system tracks applied migrations automatically
- Check the `migrations` table to see what has been applied
- Manually remove entries from the `migrations` table to re-run a migration (use with caution)

### Manual Migration Rollback
Currently, rollbacks must be done manually by:
1. Writing reverse SQL statements
2. Removing the migration entry from the `migrations` table
3. Testing thoroughly in a development environment first
