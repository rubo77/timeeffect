<?php
    require_once(__DIR__ . "/../bootstrap.php"); // Modern PHP 8.4 compatibility
	include_once("../include/config.inc.php");
	include_once($_PJ_include_path . '/scripts.inc.php');
	include_once($_PJ_include_path . '/auth.inc.php');

	// Initialize variables from request
	$altered = $_REQUEST['altered'] ?? null;
	$id = $_REQUEST['id'] ?? null;
	$telephone = $_REQUEST['telephone'] ?? '';
	$facsimile = $_REQUEST['facsimile'] ?? '';
	$email = $_REQUEST['email'] ?? '';
	$password = $_REQUEST['password'] ?? '';
	$password_retype = $_REQUEST['password_retype'] ?? '';
	$theme_preference = $_REQUEST['theme_preference'] ?? null;

	$center_template	= "user";
	$center_title		= 'Benutzer';

	if(isset($altered)) {
		// Handle theme preference update separately via direct database update
		if($theme_preference && in_array($theme_preference, ['light', 'dark', 'system'])) {
			$db = new Database();
			$user_id = $_PJ_auth->giveValue('id');
			$theme_escaped = add_slashes($theme_preference);
			$query = "UPDATE " . $GLOBALS['_PJ_auth_table'] . " SET theme_preference = '$theme_escaped' WHERE id = " . intval($user_id);
			
			if($db->query($query)) {
				// Refresh auth data to reflect theme changes
				$_PJ_auth->fetchAdditionalData();
			}
		}
		
		// Handle regular user data updates
		$data['id']					= $id;
		$data['telephone']			= $telephone;
		$data['facsimile']			= $facsimile;
		$data['email']				= $email;
		// on user edit no password is needed (no change)
		if(!empty($password)) {
			$data['password']			= $password;
			$data['password_retype']	= $password_retype;
		}
		$data['permissions']		= $_PJ_auth->giveValue('permissions');
		$data['gids']				= $_PJ_auth->giveValue('gids');
		$data['allow_nc']			= $_PJ_auth->giveValue('allow_nc');
		
		if($error = $_PJ_auth->save($data)) {
			$message = "<FONT COLOR=\"red\"><B>$error</B></FONT>";
		} else {
			$message = "<FONT COLOR=\"green\"><B>Settings updated successfully.</B></FONT>";
		}
	}
	$form_action = $GLOBALS['_PJ_own_user_script'];
	$user			= $_PJ_auth;
	$center_title	= $GLOBALS['_PJ_strings']['edit_user'];
	include("$_PJ_root/templates/edit.ihtml");

	include_once("$_PJ_include_path/degestiv.inc.php");
?>