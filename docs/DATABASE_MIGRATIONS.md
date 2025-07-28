# Database Migrations

This document describes the database migration system for TimeEffect.

## Overview

The migration system allows you to:
- Apply database schema changes in a controlled manner
- Track which migrations have been applied
- Ensure all environments have the same database structure
- Roll back changes if needed

## Migration Files

Migration files are stored in `sql/migrations/` and follow the naming convention:
```
XXX_description_of_change.sql
```

Where:
- `XXX` is a three-digit sequence number (001, 002, etc.)
- `description_of_change` describes what the migration does
- Files are processed in alphabetical order

## Running Migrations

### For New Installations

New installations automatically include all tables through `install/timeeffect.sql`. No additional migration is needed.

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