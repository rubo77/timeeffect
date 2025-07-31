<?php
    require_once(__DIR__ . "/bootstrap.php"); // Modern PHP 8.4 compatibility
	
	// Start output buffering to prevent "headers already sent" errors
	ob_start();
	
	include_once("include/config.inc.php");
	include_once($_PJ_include_path . '/scripts.inc.php');

	// Check admin permission
	if(!$_PJ_auth->checkPermission('admin')) {
		$error_message = $GLOBALS['_PJ_strings']['error_access'];
		include("$_PJ_root/templates/error.ihtml.php");
		include_once("$_PJ_include_path/degestiv.inc.php");
		exit;
	}

	$uid = $_REQUEST['uid'] ?? null;
	
	if(!$uid || !intval($uid)) {
		$error_message = 'Invalid user ID';
		include("$_PJ_root/templates/error.ihtml.php");
		include_once("$_PJ_include_path/degestiv.inc.php");
		exit;
	}

	// Store original admin user info in session
	$_SESSION['original_admin_id'] = $_PJ_auth->giveValue('id');
	$_SESSION['original_admin_username'] = $_PJ_auth->giveValue('username');
	$_SESSION['switched_by_admin'] = true;

	// Get target user
	$user = new User($uid);
	if(!$user->giveValue('id')) {
		$error_message = 'User not found';
		include("$_PJ_root/templates/error.ihtml.php");
		include_once("$_PJ_include_path/degestiv.inc.php");
		exit;
	}

	// Force login as the target user
	$_PJ_auth->logout();
	
	// Create new auth session for target user
	$_PJ_auth->setAuth($user->giveValue('username'));
	$_PJ_auth->start();
	
	// Restore the admin switch info
	$_SESSION['original_admin_id'] = $_SESSION['original_admin_id'] ?? null;
	$_SESSION['original_admin_username'] = $_SESSION['original_admin_username'] ?? null;
	$_SESSION['switched_by_admin'] = true;

	error_log("[USER_SWITCH] Switched to user " . $user->giveValue('username') . " with ID " . $user->giveValue('id') . " output: '" . ob_get_clean() . "'");

	// Redirect to main page
	header('Location: ' . $_PJ_http_root . '/');
	exit;