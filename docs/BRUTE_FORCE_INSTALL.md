# Brute Force Protection - Installation Notes

## Automatic Installation

The brute force protection system is designed to work automatically without manual database setup. When a user first attempts to login, the system will:

1. Check if the `te_login_attempts` table exists
2. Create the table automatically if it doesn't exist
3. Begin tracking login attempts immediately

## Manual Installation (Optional)

If you prefer to create the table manually or need to troubleshoot, you can run:

```sql
-- Run this SQL command in your TimeEffect database
CREATE TABLE `te_login_attempts` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) NOT NULL COMMENT 'IP address of the attempt (IPv4 or IPv6)',
  `username` varchar(50) NOT NULL DEFAULT '' COMMENT 'Username attempted',
  `attempt_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'When the attempt occurred',
  `success` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1 for successful login, 0 for failed',
  PRIMARY KEY (`id`),
  KEY `ip_time` (`ip_address`, `attempt_time`),
  KEY `username_time` (`username`, `attempt_time`),
  KEY `attempt_time` (`attempt_time`)
) ENGINE=MyISAM COMMENT='Tracks login attempts for brute force protection';
```

**Note**: Replace `te_` with your configured table prefix if different.

## Verification

To verify the protection is working:

1. Go to your TimeEffect login page
2. Enter incorrect credentials 3 times with the same username
3. You should see a lockout message on the 4th attempt
4. Check your database - the `te_login_attempts` table should contain records

## Configuration

The default settings are:
- **5 attempts per IP address** before lockout
- **3 attempts per username** before lockout  
- **15 minutes** lockout duration
- **24 hours** data retention for cleanup

These can be modified in `include/login_attempts.inc.php` if needed.

## Security Benefits

✅ **Prevents brute force attacks**: Automated tools are blocked after few attempts
✅ **Protects against credential stuffing**: Limits attempts per username
✅ **IP-based protection**: Prevents distributed attacks from single sources
✅ **User-friendly**: Legitimate users see clear warnings and timeouts
✅ **Automatic cleanup**: No manual maintenance required
✅ **Multi-language support**: Works in German, English, and French

The protection activates immediately upon installation with no additional configuration required.