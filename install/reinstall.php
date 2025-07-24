<?php
/**
 * TimeEffect Reinstall Script
 * WARNING: This will DROP all existing TimeEffect tables!
 * Use only for development/testing purposes.
 */

require_once('../include/fix_php7.php');
require_once('../include/db_mysql.inc.php');

// Get database configuration from existing config or use defaults
$config_file = 'include/config.inc.php';
if (!file_exists($config_file)) {
    error_log("no database configuration found in include/config.inc.php");
    die('no database configuration found in include/config.inc.php');
}

include($config_file);

$db = new DB_Sql();
$db->Database = $db_name;
$db->Host = $db_host;
$db->User = $db_user;
$db->Password = $db_password;

if (!$db->connect()) {
    error_log("Database connection failed!");
    die("Database connection failed!");
}

echo "<h2>TimeEffect Reinstall Script</h2>";
echo "<p><strong>WARNING:</strong> This will delete all existing TimeEffect data!</p>";

if (isset($_GET['confirm']) && $_GET['confirm'] === 'yes') {
    echo "<h3>Dropping existing tables...</h3>";
    
    // Get list of tables with our prefix
    $db->query("SHOW TABLES LIKE '{$db_prefix}%'");
    $tables_dropped = 0;
    
    while ($db->next_record()) {
        $table_name = $db->Record[0];
        echo "Dropping table: $table_name<br>";
        $db->query("DROP TABLE IF EXISTS `$table_name`");
        if (!$db->Errno) {
            $tables_dropped++;
        } else {
            echo "Error dropping $table_name: " . $db->Error . "<br>";
        }
    }
    
    echo "<p><strong>$tables_dropped tables dropped successfully!</strong></p>";
    echo "<p><a href='index.php'>Start fresh installation</a></p>";
    
} else {
    // Show confirmation form
    echo "<form method='get'>";
    echo "<input type='hidden' name='confirm' value='yes'>";
    echo "<p>Are you sure you want to drop all TimeEffect tables?</p>";
    echo "<p><input type='submit' value='Yes, DROP ALL TABLES' style='background-color: red; color: white; padding: 10px;'></p>";
    echo "</form>";
    echo "<p><a href='index.php'>Cancel and go back to installation</a></p>";
}
?>
