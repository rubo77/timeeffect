#!/bin/bash

# Fix TimeEffect config.inc.php path issues for Docker environment

CONFIG_FILE="/var/www/timeeffect/include/config.inc.php"

echo "🔧 Fixing path configuration in config.inc.php..."

# Create backup
cp "$CONFIG_FILE" "$CONFIG_FILE.backup"

# Fix $_PJ_root path - set to /var/www/html for Docker
sed -i "s|\$_PJ_root.*=.*\$_SERVER\['DOCUMENT_ROOT'\].*''.*|\$_PJ_root = '/var/www/html';|g" "$CONFIG_FILE"

# Fix $_PJ_http_root if needed
sed -i "s|\$_PJ_http_root.*=.*''.*|\$_PJ_http_root = '';|g" "$CONFIG_FILE"

echo "✅ Path configuration fixed!"
echo "📄 Backup created: $CONFIG_FILE.backup"

# Show the changes
echo "🔍 Updated paths:"
grep -n "_PJ_root\|_PJ_http_root" "$CONFIG_FILE" | head -5
