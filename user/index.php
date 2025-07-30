<?php
    require_once(__DIR__ . "/../bootstrap.php"); // Modern PHP 8.4 compatibility
	include_once("../include/config.inc.php");
	include_once($_PJ_include_path . '/scripts.inc.php');

	// Initialize variables from request
	$uid = $_REQUEST['uid'] ?? null;
	$new = $_REQUEST['new'] ?? null;
	$edit = $_REQUEST['edit'] ?? null;
	$altered = $_REQUEST['altered'] ?? null;
	$delete = $_REQUEST['delete'] ?? null;
	$cancel = $_REQUEST['cancel'] ?? null;
	$confirm = $_REQUEST['confirm'] ?? null;
	$id = $_REQUEST['id'] ?? null;
	
	// Initialize form field variables to prevent undefined variable warnings
	$telephone = $_REQUEST['telephone'] ?? '';
	$facsimile = $_REQUEST['facsimile'] ?? '';
	$email = $_REQUEST['email'] ?? '';
	$password = $_REQUEST['password'] ?? '';
	$password_retype = $_REQUEST['password_retype'] ?? '';
	$lastname = $_REQUEST['lastname'] ?? '';
	$firstname = $_REQUEST['firstname'] ?? '';
	$login = $_REQUEST['login'] ?? '';
	$allow_nc = $_REQUEST['allow_nc'] ?? '';
	$gids = $_REQUEST['gids'] ?? [];
	$permissions = $_REQUEST['permissions'] ?? [];

	$center_template	= "user";
	$center_title		= $GLOBALS['_PJ_strings']['user'];

	if(!$_PJ_auth->checkPermission('admin')) {
		$error_message		= $GLOBALS['_PJ_strings']['error_access'];
		include("$_PJ_root/templates/error.ihtml");
		include_once("$_PJ_include_path/degestiv.inc.php");
		exit;
	}

	if(isset($uid) && intval($uid)) {
		$user = new User($uid);
	}

	if(isset($new)) {
		$center_title		= $GLOBALS['_PJ_strings']['new_user'];
		include("$_PJ_root/templates/add.ihtml");
		include_once("$_PJ_include_path/degestiv.inc.php");
		exit;
	}

	if(isset($edit)) {
		if(isset($altered)) {
			$data = array();
			$data['id']					= $id;
			$data['telephone']			= $telephone;
			$data['facsimile']			= $facsimile;
			$data['email']				= $email;
			$data['password']			= $password;
			$data['password_retype']	= $password_retype;
			// FIX: Sicherstellung, dass $gids ein Array ist (verhindert implode-Fehler)
			// Filter out 'new_personal_group' placeholder - actual group will be created automatically
			if(isset($gids) && is_array($gids)) {
				$filtered_gids = array_filter($gids, function($gid) {
					return $gid !== 'new_personal_group';
				});
				$data['gids'] = implode(',', $filtered_gids);
			} else {
				$data['gids'] = '';
			}
			$data['lastname']			= add_slashes($lastname);
			$data['firstname']			= add_slashes($firstname);
			$data['username']			= add_slashes($login);
			// FIX: Sicherstellung, dass $permissions ein Array ist (verhindert implode-Fehler)
			$data['permissions']		= isset($permissions) && is_array($permissions) ? implode(',', $permissions) : '';
			$data['allow_nc']			= $allow_nc;

			$new_user = new User($data);
			$message = $new_user->save();
			if($message != '') {
				$center_title		= $GLOBALS['_PJ_strings']['edit_user'];
				include("$_PJ_root/templates/edit.ihtml");
				include_once("$_PJ_include_path/degestiv.inc.php");
				exit;
			}
				
			$list = 1;
		} else {
			$center_title		= $GLOBALS['_PJ_strings']['edit_user'];
			include("$_PJ_root/templates/edit.ihtml");
			include_once("$_PJ_include_path/degestiv.inc.php");
			exit;
		}
	}

	if(isset($delete) && !isset($cancel)) {
		if(isset($confirm)) {
			$user->delete();
			$list = 1;
		} else {
			$center_title		= $GLOBALS['_PJ_strings']['user'] . " '" . $user->giveValue('username') . "' " . $GLOBALS['_PJ_strings']['action_delete'];
			include("$_PJ_root/templates/delete.ihtml");
			include_once("$_PJ_include_path/degestiv.inc.php");
			exit;
		}
	}

	$users = new UserList();
	$center_title		= $GLOBALS['_PJ_strings']['user_list'];
	include("$_PJ_root/templates/list.ihtml");

	include_once("$_PJ_include_path/degestiv.inc.php");

?>
