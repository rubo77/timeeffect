<?php
    require_once(__DIR__ . "/../bootstrap.php"); // Modern PHP 8.4 compatibility
	include_once("../include/config.inc.php");
	include_once($_PJ_include_path . '/scripts.inc.php');

	$center_template	= "user";
	$center_title		= 'Benutzer';

	if(isset($altered)) {
		$data['id']					= $id;
		$data['telephone']			= $telephone;
		$data['facsimile']			= $facsimile;
		$data['email']				= $email;
		$data['password']			= $password;
		$data['password_retype']	= $password_retype;
		$data['permissions']		= $_PJ_auth->giveValue('permissions');
		$data['gids']				= $_PJ_auth->giveValue('gids');
		$data['allow_nc']			= $_PJ_auth->giveValue('allow_nc');
		if($error = $_PJ_auth->save($data)) {
			$message = "<FONT COLOR=\"red\"><B>$error</B></FONT>";
		}
	}
	$form_action = $GLOBALS['_PJ_own_user_script'];
	$user			= $_PJ_auth;
	$center_title	= $GLOBALS['_PJ_strings']['edit_user'];
	include("$_PJ_root/templates/edit.ihtml");

	include_once("$_PJ_include_path/degestiv.inc.php");
?>