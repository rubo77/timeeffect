<?php
require_once(__DIR__ . "/../bootstrap.php");
include_once("../include/config.inc.php");
include_once($_PJ_include_path . '/scripts.inc.php');

$center_title = "Theme Preference Migration";
$errors = array();
$migration_success = false;
$migration_required = false;

// Check if theme_preference column exists
$db = new Database();
$query = "SHOW COLUMNS FROM " . $GLOBALS['_PJ_auth_table'] . " LIKE 'theme_preference'";
$db->query($query);
$theme_field_exists = $db->next_record();

$migration_required = !$theme_field_exists;

// Handle migration execution
if (isset($_POST['run_migration']) && $_POST['run_migration'] == '1') {
    try {
        if (!$theme_field_exists) {
            // Add theme_preference column
            $alter_query = "ALTER TABLE " . $GLOBALS['_PJ_auth_table'] . " ADD COLUMN theme_preference VARCHAR(10) DEFAULT 'system' AFTER facsimile";
            
            if ($db->query($alter_query)) {
                $migration_success = true;
                $migration_required = false;
                $theme_field_exists = true;
            } else {
                $errors[] = "Failed to add theme_preference column to auth table.";
            }
        }
    } catch (Exception $e) {
        $errors[] = "Migration error: " . $e->getMessage();
    }
}

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
                <strong>Success!</strong> Theme preference database migration completed successfully.
            </div>
            <?php endif; ?>
            
            <p>TimeEffect has been updated with theme preference support (Light/Dark/System mode). 
               To use this feature, your database needs to be updated.</p>
            
            <h3>Migration Status</h3>
            
            <div style="margin: 1rem 0;">
                <strong>Theme Preference Field:</strong>
                <?php if ($theme_field_exists): ?>
                    <span style="color: green;">✓ Up to date</span>
                <?php else: ?>
                    <span style="color: red;">✗ Migration required</span>
                <?php endif; ?>
            </div>
            
            <?php if ($migration_required): ?>
            <div style="margin: 2rem 0;">
                <h3>Required Actions</h3>
                
                <div style="margin: 1rem 0; padding: 1rem; background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 4px;">
                    <h4>Database Migration</h4>
                    <p>A new database field needs to be added to support user theme preferences (Light/Dark/System mode).</p>
                    <p><strong>What will be added:</strong></p>
                    <ul>
                        <li><code>theme_preference</code> column to the auth table (VARCHAR(10), default: 'system')</li>
                    </ul>
                    <form method="POST" style="margin-top: 1rem;">
                        <button type="submit" name="run_migration" value="1" class="btn btn-primary">
                            Run Database Migration
                        </button>
                    </form>
                </div>
            </div>
            <?php else: ?>
            <div style="margin: 2rem 0; padding: 1rem; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 4px;">
                <h3 style="color: #155724;">✓ Migration Complete</h3>
                <p style="color: #155724; margin: 0;">Your TimeEffect installation is ready to use theme preferences!</p>
                <div style="margin-top: 1rem;">
                    <a href="<?= $GLOBALS['_PJ_http_root'] ?>/user/settings.php" class="btn btn-success">Go to User Settings</a>
                    <a href="<?= $GLOBALS['_PJ_http_root'] ?>/" class="btn btn-secondary" style="margin-left: 0.5rem;">Continue to Login</a>
                </div>
            </div>
            <?php endif; ?>
            
            <div style="margin-top: 2rem; padding-top: 1rem; border-top: 1px solid #ddd;">
                <h3>New Feature Available</h3>
                <ul>
                    <li><strong>Theme Preferences:</strong> Users can now choose between Light, Dark, or System default themes</li>
                    <li><strong>User Settings:</strong> Theme preference is available in the user profile settings</li>
                    <li><strong>Automatic Detection:</strong> System mode automatically follows the user's OS theme preference</li>
                </ul>
                
                <p><strong>Usage:</strong> After migration, users can change their theme preference in their user profile settings.</p>
            </div>
        </div>
    </div>
</div>
</BODY>
</HTML>
<?php
include_once("$_PJ_include_path/degestiv.inc.php");
?>
