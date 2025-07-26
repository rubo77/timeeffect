<?php
    require_once(__DIR__ . "/../bootstrap.php"); // Modern PHP 8.4 compatibility
    include_once("../include/config.inc.php");
    include_once($_PJ_include_path . '/scripts.inc.php');

    // Initialize variables from request
    $save_settings = $_REQUEST['save_settings'] ?? null;
    $theme_preference = $_REQUEST['theme_preference'] ?? null;
    $change_password = $_REQUEST['change_password'] ?? null;
    $current_password = $_REQUEST['current_password'] ?? null;
    $new_password = $_REQUEST['new_password'] ?? null;
    $confirm_password = $_REQUEST['confirm_password'] ?? null;
    $firstname = $_REQUEST['firstname'] ?? null;
    $lastname = $_REQUEST['lastname'] ?? null;
    $email = $_REQUEST['email'] ?? null;
    $telephone = $_REQUEST['telephone'] ?? null;

    $center_template = "user";
    $center_title = $GLOBALS['_PJ_strings']['user_settings'] ?? 'User Settings';
    $message = '';
    $error_message = '';

    // Get current user data
    $current_user = $_PJ_auth;
    $user_id = $current_user->giveValue('id');

    if(isset($save_settings)) {
        $updates = array();
        
        // Handle theme preference update
        if($theme_preference && in_array($theme_preference, ['light', 'dark', 'system'])) {
            $updates['theme_preference'] = $theme_preference;
        }
        
        // Handle profile updates
        if($firstname !== null) {
            $updates['firstname'] = add_slashes($firstname);
        }
        if($lastname !== null) {
            $updates['lastname'] = add_slashes($lastname);
        }
        if($email !== null) {
            $updates['email'] = $email;
        }
        if($telephone !== null) {
            $updates['telephone'] = $telephone;
        }
        
        // Handle password change
        if($change_password && $current_password && $new_password && $confirm_password) {
            // Verify current password
            if(md5($current_password) === $current_user->giveValue('password')) {
                if($new_password === $confirm_password) {
                    if(strlen($new_password) >= 6) {
                        $updates['password'] = md5($new_password);
                    } else {
                        $error_message = 'New password must be at least 6 characters long.';
                    }
                } else {
                    $error_message = 'New passwords do not match.';
                }
            } else {
                $error_message = 'Current password is incorrect.';
            }
        }
        
        // Apply updates if no errors
        if(empty($error_message) && !empty($updates)) {
            $db = new Database();
            $set_clauses = array();
            
            foreach($updates as $field => $value) {
                $set_clauses[] = "`{$field}` = '" . $db->escape($value) . "'";
            }
            
            $query = "UPDATE " . $GLOBALS['_PJ_auth_table'] . " SET " . implode(', ', $set_clauses) . " WHERE id = " . intval($user_id);
            
            if($db->query($query)) {
                $message = 'Settings updated successfully.';
                
                // Refresh auth object to reflect changes
                $_PJ_auth = new Auth($GLOBALS['_PJ_auth_table'], $GLOBALS['_PJ_db_type'], $db);
                $_PJ_auth->start();
            } else {
                $error_message = 'Failed to update settings.';
            }
        }
    }

    // Load current settings for display
    $current_theme = $current_user->giveValue('theme_preference') ?: 'system';
    $current_firstname = $current_user->giveValue('firstname');
    $current_lastname = $current_user->giveValue('lastname');
    $current_email = $current_user->giveValue('email');
    $current_telephone = $current_user->giveValue('telephone');

    include("$_PJ_root/templates/user/settings.ihtml");
    include_once("$_PJ_include_path/degestiv.inc.php");
?>