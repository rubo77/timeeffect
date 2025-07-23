# TimeEffect Development Tools

This directory contains development and analysis tools that are not needed in production.

## Files Overview

### Documentation
- `modernize_pear_db.md` - Complete modernization documentation and options analysis

### Analysis Tools
- `integrate_modern_db.php` - Analyzes PEAR DB usage and creates integration plan
- `migrate_to_doctrine.php` - Creates migration plan from PEAR DB to Doctrine DBAL
- `integration_analysis.json` - Generated analysis results
- `migration_plan.json` - Generated migration plan

### Legacy Fix Scripts
- `fix_php84_syntax.sh` - Script that fixed PHP 8.4 syntax compatibility issues

## Usage

### Run Integration Analysis
```bash
cd /var/www/timeeffect/dev
php integrate_modern_db.php
```

### Run Migration Planning
```bash
cd /var/www/timeeffect/dev
php migrate_to_doctrine.php
```

### View Documentation
```bash
cd /var/www/timeeffect/dev
cat modernize_pear_db.md
```

## Production Files

The following files remain in the root directory as they are needed for production:

- `composer.json` - Dependency management
- `bootstrap.php` - Application initialization
- `include/compatibility.php` - PEAR DB compatibility layer
- `.env.example` - Environment configuration template
- `vendor/` - Composer dependencies

## Notes

- All tools work from the dev directory and reference the parent directory correctly
- Analysis results are stored in this directory
- These tools can be safely excluded from production deployments

# Install

see [DEPLOYMENT.md](../DEPLOYMENT.md)
