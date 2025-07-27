<?php
/**
 * TimeEffect Database Migration Page
 * Handles automatic migration of database schema for new features
 */

$no_login = true; // Disable automatic login requirement
include_once("include/config.inc.php");
include_once($_PJ_include_path . '/scripts.inc.php');

$center_title = "Database Migration Required";
$migration_required = false;
$config_required = false;
$migration_success = false;
$config_success = false;
$errors = [];

// Check if migration is requested
$run_migration = $_REQUEST['run_migration'] ?? null;
$add_config = $_REQUEST['add_config'] ?? null;

// Function to check if database fields exist
function checkDatabaseFields() {
    global $errors;
    
    try {
        $db = new Database();
        
        // Check if the new fields exist in auth table
        $query = "SHOW COLUMNS FROM " . $GLOBALS['_PJ_auth_table'] . " LIKE 'confirmed'";
        $db->query($query);
        
        if (!$db->next_record()) {
            return false; // confirmed field doesn't exist
        }
        
        $query = "SHOW COLUMNS FROM " . $GLOBALS['_PJ_auth_table'] . " LIKE 'confirmation_token'";
        $db->query($query);
        
        if (!$db->next_record()) {
            return false; // confirmation_token field doesn't exist
        }
        
        $query = "SHOW COLUMNS FROM " . $GLOBALS['_PJ_auth_table'] . " LIKE 'reset_token'";
        $db->query($query);
        
        if (!$db->next_record()) {
            return false; // reset_token field doesn't exist
        }
        
        $query = "SHOW COLUMNS FROM " . $GLOBALS['_PJ_auth_table'] . " LIKE 'reset_expires'";
        $db->query($query);
        
        if (!$db->next_record()) {
            return false; // reset_expires field doesn't exist
        }
        
        return true; // All fields exist
    } catch (Exception $e) {
        $errors[] = "Database connection error: " . $e->getMessage();
        return false;
    }
}

// Function to check if configuration options exist
function checkConfigOptions() {
    return isset($GLOBALS['_PJ_allow_registration']) && 
           isset($GLOBALS['_PJ_registration_email_confirm']) && 
           isset($GLOBALS['_PJ_allow_password_recovery']);
}

// Function to run database migration
function runDatabaseMigration() {
    global $errors, $migration_success;
    
    try {
        $db = new Database();
        
        // Read and execute migration SQL
        $migration_sql = file_get_contents(__DIR__ . '/sql/migration_add_registration_features.sql');
        
        if ($migration_sql === false) {
            $errors[] = "Could not read migration file: sql/migration_add_registration_features.sql";
            return false;
        }
        
        // Split SQL into individual statements
        $statements = array_filter(array_map('trim', explode(';', $migration_sql)));
        
        foreach ($statements as $statement) {
            if (!empty($statement) && !preg_match('/^--/', $statement)) {
                $db->query($statement);
            }
        }
        
        $migration_success = true;
        return true;
    } catch (Exception $e) {
        $errors[] = "Migration error: " . $e->getMessage();
        return false;
    }
}

// Function to add configuration options
function addConfigOptions() {
    global $errors, $config_success;
    
    $config_file = __DIR__ . '/include/config.inc.php';
    
    if (!file_exists($config_file)) {
        $errors[] = "Configuration file not found: include/config.inc.php";
        return false;
    }
    
    if (!is_writable($config_file)) {
        $errors[] = "Configuration file is not writable. Please add the following options manually to include/config.inc.php:\n\n" .
                   "\$_PJ_allow_registration = 1;\n" .
                   "\$_PJ_registration_email_confirm = 1;\n" .
                   "\$_PJ_allow_password_recovery = 1;";
        return false;
    }
    
    try {
        $config_content = file_get_contents($config_file);
        
        // Check if options already exist
        if (strpos($config_content, '$_PJ_allow_registration') !== false) {
            $config_success = true;
            return true;
        }
        
        // Find insertion point (before the closing php tag or at the end)
        $insertion_point = strrpos($config_content, '?>');
        if ($insertion_point === false) {
            // No closing tag, append at end
            $insertion_point = strlen($config_content);
        }
        
        $new_config_lines = "\n\t/*\n\t   User registration and password recovery settings\n\t*/\n";
        $new_config_lines .= "\t\$_PJ_allow_registration = 1;\n";
        $new_config_lines .= "\t\$_PJ_registration_email_confirm = 1;\n";
        $new_config_lines .= "\t\$_PJ_allow_password_recovery = 1;\n\n";
        
        $new_content = substr($config_content, 0, $insertion_point) . $new_config_lines . substr($config_content, $insertion_point);
        
        if (file_put_contents($config_file, $new_content) === false) {
            $errors[] = "Failed to write configuration file";
            return false;
        }
        
        $config_success = true;
        return true;
    } catch (Exception $e) {
        $errors[] = "Configuration update error: " . $e->getMessage();
        return false;
    }
}

// Check current status
$db_fields_exist = checkDatabaseFields();
$config_options_exist = checkConfigOptions();

// Handle migration request
if (isset($run_migration) && !$db_fields_exist) {
    runDatabaseMigration();
    $db_fields_exist = checkDatabaseFields(); // Re-check
}

// Handle config addition request
if (isset($add_config) && !$config_options_exist) {
    addConfigOptions();
    $config_options_exist = checkConfigOptions(); // Re-check
}

$migration_required = !$db_fields_exist;
$config_required = !$config_options_exist;

?>
<HTML>
<HEAD>
<TITLE>TIMEEFFECT - <?= $center_title; ?></TITLE>
<LINK REL="stylesheet" HREF="<?php print $_PJ_css_path; ?>/project.css" TYPE="text/css">
<LINK REL="stylesheet" HREF="<?php print $_PJ_css_path; ?>/responsive.css" TYPE="text/css">
<LINK REL="stylesheet" HREF="<?php print $_PJ_css_path; ?>/modern.css" TYPE="text/css">
<LINK REL="stylesheet" HREF="<?php print $_PJ_css_path; ?>/layout.css" TYPE="text/css">
</HEAD>
<BODY>
<div class="container" style="max-width: 800px; margin: 2rem auto; padding: 2rem;">
    <div class="card">
        <div class="card-header">
            <h1 class="card-title" style="text-align: center;"><?= $center_title ?></h1>
        </div>
        
        <div class="card-body">
            <?php if (!empty($errors)): ?>
            <div class="alert alert-error" style="margin-bottom: 1rem; padding: 1rem; background: #fee; border: 1px solid #fcc; border-radius: 4px;">
                <strong>Errors:</strong>
                <ul style="margin: 0.5rem 0;">
                <?php foreach ($errors as $error): ?>
                    <li><?= nl2br(htmlspecialchars($error)) ?></li>
                <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
            
            <?php if ($migration_success): ?>
            <div class="alert alert-success" style="margin-bottom: 1rem; padding: 1rem; background: #efe; border: 1px solid #cfc; border-radius: 4px;">
                <strong>Success!</strong> Database migration completed successfully.
            </div>
            <?php endif; ?>
            
            <?php if ($config_success): ?>
            <div class="alert alert-success" style="margin-bottom: 1rem; padding: 1rem; background: #efe; border: 1px solid #cfc; border-radius: 4px;">
                <strong>Success!</strong> Configuration options added successfully.
            </div>
            <?php endif; ?>
            
            <p>TimeEffect has been updated with new user registration and password recovery features. 
               To use these features, your database and configuration need to be updated.</p>
            
            <h3>Migration Status</h3>
            
            <div style="margin: 1rem 0;">
                <strong>Database Fields:</strong>
                <?php if ($db_fields_exist): ?>
                    <span style="color: green;">✓ Up to date</span>
                <?php else: ?>
                    <span style="color: red;">✗ Migration required</span>
                <?php endif; ?>
            </div>
            
            <div style="margin: 1rem 0;">
                <strong>Configuration Options:</strong>
                <?php if ($config_options_exist): ?>
                    <span style="color: green;">✓ Up to date</span>
                <?php else: ?>
                    <span style="color: red;">✗ Configuration update required</span>
                <?php endif; ?>
            </div>
            
            <?php if ($migration_required || $config_required): ?>
            <div style="margin: 2rem 0;">
                <h3>Required Actions</h3>
                
                <?php if ($migration_required): ?>
                <div style="margin: 1rem 0; padding: 1rem; background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 4px;">
                    <h4>Database Migration</h4>
                    <p>New database fields need to be added to support user registration and password recovery.</p>
                    <form method="POST" style="margin-top: 1rem;">
                        <button type="submit" name="run_migration" value="1" class="btn btn-primary">
                            Run Database Migration
                        </button>
                    </form>
                </div>
                <?php endif; ?>
                
                <?php if ($config_required): ?>
                <div style="margin: 1rem 0; padding: 1rem; background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 4px;">
                    <h4>Configuration Update</h4>
                    <p>New configuration options need to be added to enable the new features.</p>
                    <form method="POST" style="margin-top: 1rem;">
                        <button type="submit" name="add_config" value="1" class="btn btn-primary">
                            Add Configuration Options
                        </button>
                    </form>
                    
                    <details style="margin-top: 1rem;">
                        <summary>Manual Configuration (if automatic fails)</summary>
                        <p>Add these lines to your <code>include/config.inc.php</code> file:</p>
                        <pre style="background: #f5f5f5; padding: 1rem; margin: 1rem 0; border-radius: 4px; overflow-x: auto;">
// User registration and password recovery settings
$_PJ_allow_registration = 1;
$_PJ_registration_email_confirm = 1;
$_PJ_allow_password_recovery = 1;</pre>
                    </details>
                </div>
                <?php endif; ?>
            </div>
            <?php else: ?>
            <div style="margin: 2rem 0; padding: 1rem; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 4px;">
                <h3 style="color: #155724;">✓ Migration Complete</h3>
                <p style="color: #155724; margin: 0;">Your TimeEffect installation is ready to use the new features!</p>
                <div style="margin-top: 1rem;">
                    <a href="<?= $GLOBALS['_PJ_http_root'] ?>/" class="btn btn-success">Continue to Login</a>
                </div>
            </div>
            <?php endif; ?>
            
            <div style="margin-top: 2rem; padding-top: 1rem; border-top: 1px solid #ddd;">
                <h3>New Features Available</h3>
                <ul>
                    <li><strong>User Registration:</strong> Self-service account registration with email confirmation</li>
                    <li><strong>Password Recovery:</strong> Secure password reset via email with 24-hour tokens</li>
                    <li><strong>Admin User Switching:</strong> Administrators can switch to any user account for support</li>
                </ul>
                
                <p><strong>Documentation:</strong> See <a href="docs/REGISTRATION_FEATURES.md">REGISTRATION_FEATURES.md</a> for complete feature documentation.</p>
            </div>
        </div>
    </div>
</div>
</BODY>
</HTML>
<?php
include_once("$_PJ_include_path/degestiv.inc.php");
?>