#!/bin/bash

# Integrate bootstrap.php into TimeEffect main files
# This ensures modern PHP 8.4 compatibility and Composer autoloading

echo "Integrating bootstrap.php into TimeEffect main files..."

# Find all PHP files that include config.inc.php
FILES=$(find /var/www/timeeffect -name "*.php" -type f -exec grep -l "config\.inc\.php" {} \; | grep -v "/dev/" | grep -v "/vendor/" | grep -v "/install/")

for FILE in $FILES; do
    echo "Processing: $FILE"
    
    # Check if bootstrap is already included
    if grep -q "bootstrap\.php" "$FILE"; then
        echo "  ✅ Bootstrap already included"
        continue
    fi
    
    # Check if config.inc.php is included
    if grep -q "config\.inc\.php" "$FILE"; then
        # Add bootstrap before config.inc.php
        sed -i '/config\.inc\.php/i\
    require_once(__DIR__ . "/../bootstrap.php"); // Modern PHP 8.4 compatibility' "$FILE"
        echo "  ✅ Bootstrap added"
    else
        echo "  ⚠️  No config.inc.php found"
    fi
done

echo "✅ Bootstrap integration completed!"
echo "📋 Files processed: $(echo "$FILES" | wc -l)"
