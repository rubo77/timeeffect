<?php
    require_once(__DIR__ . "/../bootstrap.php"); // Modern PHP 8.4 compatibility
	include_once("../include/config.inc.php");
	include_once($_PJ_include_path . '/scripts.inc.php');

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
			$data['gids']				= isset($gids) && is_array($gids) ? implode(',', $gids) : '';
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
