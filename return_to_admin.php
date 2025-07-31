<?php
    require_once(__DIR__ . "/bootstrap.php"); // Modern PHP 8.4 compatibility
	include_once("include/config.inc.php");
	include_once($_PJ_include_path . '/scripts.inc.php');

	// Check if user was switched by admin
	if(!isset($_SESSION['switched_by_admin']) || !$_SESSION['switched_by_admin'] || 
	   !isset($_SESSION['original_admin_id'])) {
		$error_message = 'Not logged in via admin switch';
		include("$_PJ_root/templates/error.ihtml.php");
		include_once("$_PJ_include_path/degestiv.inc.php");
		exit;
	}

	$original_admin_id = $_SESSION['original_admin_id'];
	
	// Get original admin user
	$admin_user = new User($original_admin_id);
	if(!$admin_user->giveValue('id')) {
		$error_message = 'Original admin user not found';
		include("$_PJ_root/templates/error.ihtml.php");
		include_once("$_PJ_include_path/degestiv.inc.php");
		exit;
	}
	
	// Clear admin switch session variables
	unset($_SESSION['switched_by_admin']);
	unset($_SESSION['original_admin_id']);
	unset($_SESSION['original_admin_username']);

	// Force logout current user
	$_PJ_auth->logout();
	
	// Re-login as original admin
	$_PJ_auth->setAuth($admin_user->giveValue('username'));
	$_PJ_auth->start();

	// Redirect to user management page
	header('Location: ' . $_PJ_http_root . '/user/');
	exit;
?>