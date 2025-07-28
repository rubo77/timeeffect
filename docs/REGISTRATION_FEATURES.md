# TimeEffect - User Registration and Password Recovery Features

This document describes the new user registration, password recovery, and admin user switching features added to TimeEffect.

## New Features

### 1. User Registration

Allows users to register themselves for new accounts without admin intervention.

**Configuration:**
- `$_PJ_allow_registration` - Enable/disable user registration (default: 1)
- `$_PJ_registration_email_confirm` - Require email confirmation for new registrations (default: 1)

**Access:** Visit `/register.php` or click "Register new account" on the login page.

**Features:**
- Self-service registration form
- Email confirmation workflow
- Automatically restricted to 'agent' role for security
- Multi-group selection support
- Comprehensive input validation

### 2. Password Recovery

Secure password reset functionality for users who have forgotten their passwords.

**Configuration:**
- `$_PJ_allow_password_recovery` - Enable/disable password recovery (default: 1)

**Access:** Visit `/password_reset.php` or click "Forgot password?" on the login page.

**Features:**
- Email-based password reset
- Secure random tokens (64 characters)
- Time-limited reset links (24 hour expiry)
- Protection against email enumeration attacks
- HTML and plain text email support

### 3. Admin User Switching

Allows administrators to switch to any user account for troubleshooting and support.

**Access:** User management page (`/user/`) - click "Switch to user" button.

**Features:**
- Switch to any user account
- Opens in new window to preserve admin session
- Clear visual indicators when logged in via admin switch
- Easy "Return to admin" functionality
- Session isolation and security

## Installation

### For New Installations
1. Use the updated `sql/timeffect.sql` which includes the new database fields
2. Configure the new settings in `include/config.inc.php`

### For Existing Installations

TimeEffect uses a comprehensive migration system that automatically detects when database and configuration updates are required. The system follows the patterns described in `DATABASE_MIGRATIONS.md`.

**Migration System Overview:**
- **MigrationManager Class**: Manages version-based sequential migrations with proper tracking
- **Migrations Table**: Tracks executed migrations to prevent duplicate execution 
- **Automatic Detection**: Bootstrap checks for pending migrations on each request
- **Integration**: Migrations run automatically during login process after database connection

**Automatic Migration Process:**
1. Visit your TimeEffect installation - you'll see a migration notice if needed
2. Click the migration link to go to `/migrate.php`
3. The system will show current schema version and target version
4. Click "Run Database Migration" to execute pending migrations
5. Add configuration options automatically or manually as guided

**Migration Features:**
- Version tracking prevents duplicate execution
- Safety checks ensure migrations are idempotent
- Comprehensive error handling and logging
- Detailed migration history in the migrations table
- Configuration auto-detection and guided setup

```php
// User registration settings
$_PJ_allow_registration = 1;
$_PJ_registration_email_confirm = 1;
$_PJ_allow_password_recovery = 1;
```

## Database Schema Changes

The migration system (version 1) automatically adds the following fields to the `auth` table:

- `confirmed` - TINYINT(1) - Whether the user's email is confirmed (default: 1)
- `confirmation_token` - VARCHAR(64) - Token for email confirmation
- `reset_token` - VARCHAR(64) - Token for password reset
- `reset_expires` - DATETIME - Expiration time for password reset token

**Migration Details:**
- Migration is tracked in the `{prefix}migrations` table
- Includes performance indexes on token fields
- Safe to run multiple times (idempotent)
- Follows DATABASE_MIGRATIONS.md specification

## Security Features

- **Email Confirmation:** Prevents unauthorized registrations
- **Secure Tokens:** Cryptographically secure random tokens for all operations
- **Time Limits:** Password reset tokens expire after 24 hours
- **Role Restrictions:** Self-registration limited to 'agent' role only
- **Anti-Enumeration:** Password reset doesn't reveal if email exists
- **Session Security:** Admin switching preserves security context

## Multilingual Support

All new features support the existing language system:
- English (en)
- German (de) 
- French (fr)

New language strings added:
- `register`, `register_new_account`
- `forgot_password`, `reset_password`
- `password_reset_sent`, `password_reset_success`, `password_reset_error`
- `email_confirm_sent`, `email_confirm_success`, `email_confirm_error`
- `registration_success`, `registration_disabled`
- `switch_to_user`, `return_to_admin`, `logged_in_as_admin`

## File Structure

### New Files
- `register.php` - User registration script
- `password_reset.php` - Password recovery script
- `switch_user.php` - Admin user switching script
- `return_to_admin.php` - Return to admin script
- `templates/user/register.ihtml` - Registration form template
- `sql/migration_add_registration_features.sql` - Database migration
- `migrate.php` - Automated migration page for existing installations

### Modified Files
- `include/config.inc.php.sample` - Added new configuration options
- `include/user.inc.php` - Updated User class for new fields
- `include/auth.inc.php` - Added email confirmation check during login
- `templates/shared/login.ihtml` - Added registration and password recovery links
- `templates/shared/top.ihtml` - Added admin switch indicator
- `templates/shared/topnav.ihtml` - Added return to admin button
- `templates/user/list.ihtml` - Added actions column header
- `templates/user/row.ihtml` - Added switch to user button
- `sql/timeffect.sql` - Updated schema with new fields
- `include/languages/en.inc.php` - Added English strings
- `include/languages/de.inc.php` - Added German strings
- `include/languages/fr.inc.php` - Added French strings

## Configuration Examples

### Enable All Features
```php
$_PJ_allow_registration = 1;
$_PJ_registration_email_confirm = 1;
$_PJ_allow_password_recovery = 1;
```

### Disable Registration, Keep Password Recovery
```php
$_PJ_allow_registration = 0;
$_PJ_registration_email_confirm = 0;
$_PJ_allow_password_recovery = 1;
```

### Registration Without Email Confirmation
```php
$_PJ_allow_registration = 1;
$_PJ_registration_email_confirm = 0;
$_PJ_allow_password_recovery = 1;
```

## Email Configuration

For email functionality to work, ensure your PHP installation has the `mail()` function configured correctly, or implement a custom email handler in the registration and password reset scripts.

## Troubleshooting

### Registration Issues
- Check that `$_PJ_allow_registration` is set to 1
- Verify database has the new fields (run migration script)
- Ensure at least one group exists in the system
- Check PHP error logs for detailed error messages

### Email Issues
- Verify PHP `mail()` function is configured
- Check server email logs
- Test with a simple PHP mail script first
- Consider implementing SMTP for production use

### Admin Switching Issues
- Ensure user has 'admin' permission
- Check that sessions are working correctly
- Verify JavaScript is enabled for popup windows
- Check PHP session configuration

## Support

For issues related to these new features, please check:
1. PHP error logs
2. Database connection and schema
3. Configuration file settings
4. Server email configuration (for email features)