#!/bin/bash

# Fix PHP 8.4 compatibility: Replace deprecated curly brace string access with square brackets
# This script fixes the syntax error: unexpected token "{"

echo "Fixing PHP 8.4 compatibility issues in PEAR library..."

# Find all PHP files in the PEAR directory and fix the deprecated syntax
find /var/www/timeeffect/include/pear -name "*.php" -type f -exec sed -i 's/\$\([a-zA-Z_][a-zA-Z0-9_]*\){\([0-9]\+\)}/\$\1[\2]/g' {} \;

echo "Fixed deprecated curly brace string access syntax in PEAR library"
echo "All \$variable{index} occurrences have been replaced with \$variable[index]"
