# TIMEEFFECT Installation Manual

## 1. System Requirements

### Minimum Requirements
- **PHP**: 8.1 or higher (tested with PHP 8.4)
- **Database**: MySQL 5.7+ or MariaDB 10.3+
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **Memory**: 512MB RAM minimum
- **Disk Space**: 100MB for application files

### Recommended Setup
- **PHP**: 8.4 with MySQLi extension
- **Database**: MariaDB 10.11+ or MySQL 8.0+
- **Web Server**: Apache 2.4+ with mod_rewrite
- **Memory**: 1GB+ RAM
- **SSL**: HTTPS certificate for production

## 2. Installation Methods

### Option A: Docker Installation (Recommended)

#### Prerequisites
Install Docker and Docker Compose:

```bash
# Minimal Docker installation (Ubuntu/Debian)
sudo apt install docker-compose --no-install-recommends
sudo apt install docker-compose bridge-utils cgroupfs-mount containerd docker.io pigz runc
```

#### Quick Start

1. **Prepare Environment**:
```bash
# Start in tmux session (recommended)
tmux
sudo su

# Stop conflicting services
systemctl stop mysql
systemctl stop nginx
```

2. **Automated Setup** (Recommended):
```bash
cd /var/www/timeeffect/docker/
./setup.sh
```

3. **Manual Setup** (Alternative):
```bash
cd /var/www/timeeffect/docker/
sudo docker-compose up --build
```

#### Access Application

Open in web browser:
- `http://localhost/install` (fresh installation)
- `http://localhost` (existing installation)
- `http://timeeffect.lvh.me` (alternative)

**Default Docker Credentials:**
- Database: `timeeffect_db` / `timeeffect` / `very_unsecure_timeeffect_PW1`
- Root DB: `root` / `very_unsecure_timeeffect_PW1`
- Demo User: `pirates` / `vt8yhnan`

#### Database Setup Options

**Option 1: Fresh Installation**
Use the web installer at `http://localhost/install`

**Option 2: Import Existing Database**
```bash
# SSH into container
sudo docker exec -i -t timeeffect_app_1 bash -l

# Create database
echo "CREATE database timeeffect_db;"|mysql -u root -pvery_unsecure_timeeffect_PW1 --protocol tcp

# Import SQL files
cd /var/www/html/dev/db
for SQL in timeeffect*.sql; do
  echo "importing $SQL ...";
  mysql timeeffect_db -u root -pvery_unsecure_timeeffect_PW1 --protocol tcp < $SQL
done

# Grant permissions
echo "GRANT ALL PRIVILEGES ON timeeffect_db.* TO 'timeeffect'@'%' WITH GRANT OPTION;"|mysql timeeffect_db -u root -pvery_unsecure_timeeffect_PW1 --protocol tcp
```

#### Docker Management

**Start/Stop Services:**
```bash
# Start (subsequent runs)
docker-compose up

# Stop
docker-compose down

# Restart Apache in container
sudo docker exec -i -t timeeffect_app_1 bash -c 'apache2ctl restart'
```

**Debugging:**
```bash
# SSH into app container
sudo docker exec -i -t timeeffect_app_1 bash -l

# View Apache logs
tail -f /var/log/apache2/error.log

# Test database connection
mysql timeeffect_db -u timeeffect -pvery_unsecure_timeeffect_PW1 --protocol tcp

# View application logs
tail -f /var/www/html/logs/app.log
```

### Option B: Manual Installation

#### 2.1 Modern Infrastructure Setup

**Install Composer Dependencies:**
```bash
# Navigate to application directory
cd /var/www/timeeffect

# Install modern dependencies
composer install --no-dev --optimize-autoloader
```

**Configure Environment:**
```bash
# Copy environment template
cp .env.example .env

# Edit configuration
nano .env
```

**Environment Configuration (.env):**
```bash
# Application Environment
APP_ENV=production
APP_DEBUG=false

# Database Configuration
DB_HOST=localhost
DB_PORT=3306
DB_NAME=timeeffect_db
DB_USER=timeeffect
DB_PASS=your_secure_password

# Legacy PEAR DB DSN (for compatibility)
PEAR_DB_DSN=mysqli://timeeffect:your_secure_password@localhost:3306/timeeffect_db

# Logging
LOG_LEVEL=info
LOG_PATH=/var/www/timeeffect/logs

# Session Configuration
SESSION_LIFETIME=7200
SESSION_NAME=TIMEEFFECT_SESS

# Security
CSRF_TOKEN_NAME=_token
HASH_ALGO=sha256
```

#### 2.2 Database Setup

Create a MySQL/MariaDB database and user:

```sql
CREATE DATABASE timeeffect_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'timeeffect'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER ON timeeffect_db.* TO 'timeeffect'@'localhost';
FLUSH PRIVILEGES;
```

#### 2.3 PHP Configuration

Ensure the following PHP extensions are enabled:
- `mysqli` (required for database connectivity)
- `session` (required for user sessions)
- `json` (required for API responses)
- `mbstring` (recommended for UTF-8 support)

**Important**: Modern PHP versions have `register_globals` disabled by default (which is secure). TIMEEFFECT has been updated to work without `register_globals`.

#### 2.4 Bootstrap Integration

The modern infrastructure is automatically loaded. No manual integration required for standard installations.

## 3. Setup

#### 3.1 File Permissions

Ensure the web server has read access to all application files:

```bash
# Set appropriate permissions
chmod -R 644 /path/to/timeeffect
chmod -R 755 /path/to/timeeffect/directories

# Make sure config files are readable
chmod 644 include/config.php
```

#### 3.2 Web Server Configuration

**Apache (.htaccess)**:
The application includes `.htaccess` files for URL rewriting. Ensure `mod_rewrite` is enabled.

**Nginx**:
Add appropriate rewrite rules to your server configuration.

#### 3.3 Installation Interface

Access the installation interface at:
```
http://your-domain.com/timeeffect/install/
```

The installation process includes:
1. **Database Configuration** - Enter your database credentials
2. **Admin User Setup** - Create the first administrator account
3. **System Configuration** - Configure basic settings
4. **Completion** - Verify installation success

**Security Note**: After installation, remove the `install/` directory:
```bash
rm -rf install/
```

## 4. Configuration

### 4.1 Database Configuration

Edit `include/config.php` for database settings:

```php
$_PJ_db_host = 'localhost';        // Database host
$_PJ_db_database = 'timeeffect_db';   // Database name
$_PJ_db_user = 'timeeffect';       // Database user
$_PJ_db_password = 'your_password'; // Database password
$_PJ_db_prefix = '';               // Table prefix (optional)
```

### 4.2 Application Settings

```php
$_PJ_http_root = '/timeeffect';    // Web root path
$_PJ_language = 'en';              // Language (en, de)
$_PJ_root = '/var/www/timeeffect'; // File system path
```

### 4.3 Modern Infrastructure

**Composer Dependencies**: The application uses modern PHP libraries:
- `doctrine/dbal` - Database abstraction layer
- `monolog/monolog` - Advanced logging
- `symfony/dotenv` - Environment configuration
- `vlucas/phpdotenv` - Environment variable loading

**PEAR Compatibility Layer**: Seamless migration from legacy PEAR DB to modern Doctrine DBAL while maintaining backward compatibility.

**Database Migrations**: Automatic schema updates are handled by the migration system. New migrations are applied automatically on user login.

**Dark Mode**: Users can select their preferred theme (light/dark/system) in their profile settings.

**Security**: Modern PHP security practices are implemented, including:
- Prepared statements for database queries
- CSRF protection
- Secure session handling
- Input validation and sanitization

## 5. Production Deployment

### 5.1 Required Files

**Include in Production:**
- All root-level application files
- `composer.json`, `composer.lock`, `vendor/`
- `bootstrap.php` - Modern infrastructure initialization
- `include/compatibility.php` - PEAR DB compatibility layer
- `.env` - Environment configuration (created from `.env.example`)
- `logs/` directory - Application logging

**Exclude from Production:**
- `dev/` directory - Development tools only
- `.env.example` - Template file only
- `tests/` directory - Test files
- Development and analysis files

### 5.2 Deployment Steps

1. **Environment Setup:**
```bash
# Copy environment template
cp .env.example .env

# Configure database and security settings
nano .env
```

2. **Install Dependencies:**
```bash
composer install --no-dev --optimize-autoloader
```

3. **Bootstrap Integration:**
```php
// Add to main entry points (index.php, etc.)
require_once __DIR__ . '/bootstrap.php';
```

4. **Test Application:**
- Verify database connectivity
- Check application functionality
- Monitor `logs/app.log` for compatibility issues

### 5.3 Development Tools

The `dev/` directory contains development resources:
- **[dev/TODO.md](../dev/TODO.md)** - Development roadmap and pending tasks
- **[dev/ICON_FIX.md](../dev/ICON_FIX.md)** - Icon-related fixes and documentation
- **[dev/memories-for-AI.md](../dev/memories-for-AI.md)** - AI context for future development
- **[dev/plan.md](../dev/plan.md)** - Current development plan for the windsurf A.I. and progress
- **[dev/generate_env_from_config.php](../dev/generate_env_from_config.php)** - Environment configuration generator
- `dev/logo/` - Logo assets and branding materials

**Note**: Development tools are excluded from production deployments.

### 4.4 Customization

**Themes**: Modify `css/modern.css` for visual customization. The application supports CSS custom properties for easy theming.

**PDF Reports**: Configure PDF layout in `include/layout.inc.php`:
- Logo settings: `$_PJ_pdf_logo`, `$_PJ_pdf_logo_width`, `$_PJ_pdf_logo_height`
- Layout measurements in points (not pixels)

## 6. Additional Documentation

### Core Documentation
- **[DATABASE_MIGRATIONS.md](DATABASE_MIGRATIONS.md)** - Complete guide for database schema migrations, best practices, and examples
- **[DARK_MODE_README.md](DARK_MODE_README.md)** - Dark mode implementation details and CSS customization
- **[PRODUCTION_FILES.md](PRODUCTION_FILES.md)** - Production deployment file overview and exclusions

### Development Resources
- **[dev/modernize_pear_db.md](../dev/modernize_pear_db.md)** - PEAR DB modernization strategy and implementation details
- **[dev/TODO.md](../dev/TODO.md)** - Development roadmap and pending tasks

### Docker & Deployment
- **[docker/docker-README.md](../docker/docker-README.md)** - Detailed Docker setup (integrated into this manual)
- **[DEPLOYMENT.md](../DEPLOYMENT.md)** - Advanced deployment configurations

### Project Information
- **[README.md](../README.md)** - Project overview and quick start
- **[NOTES.md](../NOTES.md)** - Development notes and changelog

## 7. Troubleshooting

### Common Issues

**Database Connection**: Verify credentials and ensure MySQL/MariaDB is running.

**File Permissions**: Ensure web server can read application files.

**PHP Extensions**: Verify required extensions (mysqli, session, json) are enabled.

**Migration Errors**: Check database logs and ensure proper permissions for schema changes.

**Docker Issues**: 
- Ensure ports 80 and 3306 are not in use by other services
- Check container logs: `docker-compose logs`
- Restart containers: `docker-compose restart`

**Composer Issues**:
- Run `composer install` if vendor directory is missing
- Check PHP version compatibility (requires PHP 8.1+)
- Verify write permissions for vendor directory

### Support

For technical support or questions about TIMEEFFECT:
- **GitHub Issues**: https://github.com/rubo77/timeeffect/issues
- **Documentation**: Refer to the linked documentation above
- **Development**: Check `dev/` directory for analysis tools