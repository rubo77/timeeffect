# Brute Force Protection

This document describes the brute force protection mechanism implemented in TimeEffect to prevent unauthorized access through flooding login attempts.

## Overview

The brute force protection system tracks failed login attempts by IP address and username, temporarily locking out accounts after too many consecutive failures. This helps protect against automated attacks and unauthorized access attempts.

## Features

### Protection Mechanisms
- **IP-based protection**: Tracks attempts per IP address
- **User-based protection**: Tracks attempts per username  
- **Time-based lockout**: Temporary account suspension
- **Progressive warnings**: Users are warned about remaining attempts
- **Automatic cleanup**: Old attempt records are automatically removed

### User Experience
- Clear warning messages in multiple languages (EN/DE/FR)
- Visual indicators showing remaining attempts before lockout
- Lockout duration clearly displayed to users
- Seamless integration with existing login flow

## Configuration

The protection settings are defined as constants in `LoginAttemptTracker` class:

```php
const MAX_ATTEMPTS_PER_IP = 10;       // Max attempts per IP address
const MAX_ATTEMPTS_PER_USER = 3;      // Max attempts per username
const LOCKOUT_DURATION = 60;          // Lockout duration in seconds (1 minute)
const CLEANUP_INTERVAL = 3600;        // Clean old records every hour
```

## Database Schema

The system creates a `te_login_attempts` table (using the configured table prefix):

```sql
CREATE TABLE `te_login_attempts` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) NOT NULL COMMENT 'IP address (IPv4 or IPv6)',
  `username` varchar(50) NOT NULL DEFAULT '' COMMENT 'Username attempted',
  `attempt_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `success` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1 for success, 0 for failure',
  PRIMARY KEY (`id`),
  KEY `ip_time` (`ip_address`, `attempt_time`),
  KEY `username_time` (`username`, `attempt_time`),
  KEY `attempt_time` (`attempt_time`)
) ENGINE=MyISAM;
```

## Implementation Details

### Classes

#### LoginAttemptTracker
Located in `include/login_attempts.inc.php`, this class handles:
- Recording login attempts (successful and failed)
- Checking lockout status for IP addresses and usernames
- Clearing attempts after successful login
- Automatic cleanup of old records
- Database table creation if needed

#### PJAuth (Modified)
The existing authentication class in `include/auth.inc.php` has been enhanced to:
- Initialize the LoginAttemptTracker
- Check for lockouts before processing login attempts
- Record failed and successful attempts
- Clear attempts on successful authentication

### Login Template Updates
The login form (`templates/shared/login.ihtml`) now displays:
- Lockout warnings with countdown timers
- Progressive attempt warnings
- Multi-language security messages
- User-friendly error styling

## Security Features

### IP Address Detection
The system detects client IP addresses while handling proxy scenarios:
- Checks multiple HTTP headers (X-Forwarded-For, Client-IP, etc.)
- Validates IP addresses to prevent spoofing
- Handles comma-separated proxy chains
- Falls back to REMOTE_ADDR

### Time-based Protection
- Lockouts are time-based, automatically expiring after the configured duration
- Old attempt records are periodically cleaned up
- Lockout status is checked in real-time

### Data Protection
- All user input is properly escaped before database queries
- No sensitive information is logged
- IP addresses are stored securely
- Automatic cleanup prevents table growth

## Usage

The protection is automatically active once the files are in place. No configuration changes are required for basic operation.

### For Users
- Login normally - the protection is transparent for legitimate users
- After failed attempts, users see warnings about remaining attempts
- Locked users see clear messages about when they can try again

### For Administrators
- Monitor the `te_login_attempts` table for security patterns
- Review application logs for security events
- Adjust constants in `LoginAttemptTracker` class if needed

## Language Support

Security messages are available in:
- **English** (`include/languages/en.inc.php`)
- **German** (`include/languages/de.inc.php`) 
- **French** (`include/languages/fr.inc.php`)

Additional languages can be added by extending the language files with the required string keys.

## Troubleshooting

### Common Issues

**Database table not created automatically**
- Ensure the web server has CREATE TABLE permissions
- Check that the database connection is working
- Verify the table prefix configuration

**Lockouts not working as expected**
- Check system time synchronization
- Verify database timestamps are correct
- Review IP detection if behind proxies

**Users locked out unexpectedly**
- Review cleanup interval settings
- Check for shared IP addresses (NAT scenarios)
- Verify time zone configuration

### Monitoring

Monitor these areas for security insights:
- High frequency of failed attempts from single IPs
- Attempts against non-existent usernames
- Patterns of attempts across different accounts
- Geographical distribution of failed attempts

## Files Modified

- `include/auth.inc.php` - Enhanced authentication class
- `templates/shared/login.ihtml` - Updated login form
- `include/languages/en.inc.php` - English security messages
- `include/languages/de.inc.php` - German security messages  
- `include/languages/fr.inc.php` - French security messages

## Files Added

- `include/login_attempts.inc.php` - LoginAttemptTracker class
- `sql/login_attempts.sql` - Database schema for login attempts table

## Testing

The implementation includes comprehensive testing:
- Logic validation tests
- Integration tests with existing authentication
- Multi-language message verification
- Template integration validation

Run tests using the provided test scripts in `/tmp/` directory during development.