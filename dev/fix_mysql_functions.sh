#!/bin/bash

# Fix deprecated MySQL functions for PHP 8.4 compatibility
# Replace old mysql_* functions with mysqli_* equivalents

FILE="/var/www/timeeffect/include/db_mysql.inc.php"

echo "Fixing MySQL functions in $FILE for PHP 8.4 compatibility..."

# Create backup
cp "$FILE" "$FILE.backup"

# Fix mysql functions to mysqli equivalents
sed -i 's/@mysql_free_result(\$this->Query_ID)/@mysqli_free_result(\$this->Query_ID)/g' "$FILE"
sed -i 's/@mysql_query(\$Query_String,\$this->Link_ID)/@mysqli_query(\$this->Link_ID, \$Query_String)/g' "$FILE"
sed -i 's/@mysql_fetch_array(\$this->Query_ID)/@mysqli_fetch_array(\$this->Query_ID)/g' "$FILE"
sed -i 's/@mysql_data_seek(\$this->Query_ID, \$pos)/@mysqli_data_seek(\$this->Query_ID, \$pos)/g' "$FILE"
sed -i 's/@mysql_data_seek(\$this->Query_ID, \$this->num_rows())/@mysqli_data_seek(\$this->Query_ID, \$this->num_rows())/g' "$FILE"
sed -i 's/@mysql_query(\$query, \$this->Link_ID)/@mysqli_query(\$this->Link_ID, \$query)/g' "$FILE"
sed -i 's/@mysql_query("unlock tables", \$this->Link_ID)/@mysqli_query(\$this->Link_ID, "unlock tables")/g' "$FILE"
sed -i 's/@mysql_affected_rows(\$this->Link_ID)/@mysqli_affected_rows(\$this->Link_ID)/g' "$FILE"
sed -i 's/@mysql_insert_id(\$this->Link_ID)/@mysqli_insert_id(\$this->Link_ID)/g' "$FILE"
sed -i 's/@mysql_num_rows(\$this->Query_ID)/@mysqli_num_rows(\$this->Query_ID)/g' "$FILE"
sed -i 's/@mysql_num_fields(\$this->Query_ID)/@mysqli_num_fields(\$this->Query_ID)/g' "$FILE"
sed -i 's/@mysql_query(\$q, \$this->Link_ID)/@mysqli_query(\$this->Link_ID, \$q)/g' "$FILE"
sed -i 's/@mysql_fetch_array(\$id)/@mysqli_fetch_array(\$id)/g' "$FILE"
sed -i 's/@mysql_list_fields(\$this->Database, \$table)/@mysqli_query(\$this->Link_ID, "SHOW COLUMNS FROM \$table")/g' "$FILE"
sed -i 's/@mysql_num_fields(\$id)/@mysqli_num_fields(\$id)/g' "$FILE"
sed -i 's/@mysql_free_result(\$id)/@mysqli_free_result(\$id)/g' "$FILE"
sed -i 's/@mysql_error(\$this->Link_ID)/@mysqli_error(\$this->Link_ID)/g' "$FILE"
sed -i 's/@mysql_errno(\$this->Link_ID)/@mysqli_errno(\$this->Link_ID)/g' "$FILE"
sed -i 's/mysql_fetch_row(\$this->Query_ID)/mysqli_fetch_row(\$this->Query_ID)/g' "$FILE"

# Fix comment
sed -i 's/automatic mysql_free_result()/automatic mysqli_free_result()/g' "$FILE"

echo "✅ MySQL functions updated successfully!"
echo "📄 Backup created: $FILE.backup"
echo "🔍 Checking for remaining mysql_ functions..."

# Check for remaining mysql_ functions
REMAINING=$(grep -n "mysql_" "$FILE" | grep -v "mysqli_" | grep -v "automatic mysqli_free_result" || true)
if [ -n "$REMAINING" ]; then
    echo "⚠️  Remaining mysql_ functions found:"
    echo "$REMAINING"
else
    echo "✅ All mysql_ functions have been converted to mysqli_!"
fi
