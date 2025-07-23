#!/bin/bash

# Fix TimeEffect installation SQL to use CREATE TABLE IF NOT EXISTS
# This prevents errors when tables already exist

FILE="/var/www/timeeffect/install/timeeffect.sql"

echo "Fixing CREATE TABLE statements in $FILE..."

# Create backup
cp "$FILE" "$FILE.backup"

# Replace CREATE TABLE with CREATE TABLE IF NOT EXISTS
sed -i 's/CREATE TABLE `/CREATE TABLE IF NOT EXISTS `/g' "$FILE"

echo "✅ SQL statements updated successfully!"
echo "📄 Backup created: $FILE.backup"

# Show the changes
echo "🔍 Modified statements:"
grep -n "CREATE TABLE IF NOT EXISTS" "$FILE" | head -5
