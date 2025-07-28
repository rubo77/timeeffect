<?php
/**
 * Database Migration Runner for TimeEffect
 * 
 * This script applies database migrations in the correct order.
 * Run this script after updating to ensure your database schema is up to date.
 */

require_once('../include/fix_php7.php');
require_once('../include/db_mysql.inc.php');

// Get database configuration
$config_file = '../include/config.inc.php';
if (!file_exists($config_file)) {
    die("Database configuration not found in include/config.inc.php\n");
}

include($config_file);

$db = new DB_Sql();
$db->Database = $db_name;
$db->Host = $db_host;
$db->User = $db_user;
$db->Password = $db_password;

if (!$db->connect()) {
    die("Database connection failed!\n");
}

echo "TimeEffect Database Migration Runner\n";
echo "====================================\n\n";

// Create migrations tracking table if it doesn't exist
$migrations_table = $db_prefix . 'migrations';
$create_migrations_table = "
CREATE TABLE IF NOT EXISTS `{$migrations_table}` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `migration` varchar(255) NOT NULL,
  `applied_at` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `migration` (`migration`)
) ENGINE=MyISAM COMMENT='Tracks applied database migrations'
";

$db->query($create_migrations_table);
if ($db->Errno) {
    die("Failed to create migrations table: " . $db->Error . "\n");
}

// Get list of applied migrations
$db->query("SELECT migration FROM `{$migrations_table}`");
$applied_migrations = [];
while ($db->next_record()) {
    $applied_migrations[] = $db->f('migration');
}

// Get list of migration files
$migration_files = glob('./migrations/*.sql');
sort($migration_files);

$applied_count = 0;

foreach ($migration_files as $file) {
    $migration_name = basename($file, '.sql');
    
    if (in_array($migration_name, $applied_migrations)) {
        echo "Skipping {$migration_name} (already applied)\n";
        continue;
    }
    
    echo "Applying {$migration_name}...\n";
    
    // Read migration file
    $sql = file_get_contents($file);
    if ($sql === false) {
        echo "Error reading migration file: {$file}\n";
        continue;
    }
    
    // Replace template variables
    $sql = str_replace('<%db_prefix%>', $db_prefix, $sql);
    
    // Execute migration
    $db->query($sql);
    if ($db->Errno) {
        echo "Error applying {$migration_name}: " . $db->Error . "\n";
        continue;
    }
    
    // Record migration as applied
    $record_query = sprintf(
        "INSERT INTO `%s` (migration) VALUES ('%s')",
        $migrations_table,
        mysqli_real_escape_string($db->Link_ID, $migration_name)
    );
    
    $db->query($record_query);
    if ($db->Errno) {
        echo "Error recording {$migration_name}: " . $db->Error . "\n";
    } else {
        echo "Successfully applied {$migration_name}\n";
        $applied_count++;
    }
}

echo "\nMigration complete! Applied {$applied_count} migrations.\n";
?>