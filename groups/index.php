<?php
    require_once(__DIR__ . "/../bootstrap.php"); // Modern PHP 8.4 compatibility
	include_once("../include/config.inc.php");
	include_once($_PJ_include_path . '/scripts.inc.php');

	// Initialize variables from request
	$gid = $_REQUEST['gid'] ?? null;
	$new = $_REQUEST['new'] ?? null;
	$edit = $_REQUEST['edit'] ?? null;
	$altered = $_REQUEST['altered'] ?? null;
	$delete = $_REQUEST['delete'] ?? null;
	$cancel = $_REQUEST['cancel'] ?? null;
	$confirm = $_REQUEST['confirm'] ?? null;
	$id = $_REQUEST['id'] ?? null;

	$center_template	= "group";
	$center_title		= $GLOBALS['_PJ_strings']['groups'];

	if(!$_PJ_auth->checkPermission('admin')) {
		$error_message		= $GLOBALS['_PJ_strings']['error_access'];
		include("$_PJ_root/templates/error.ihtml");
		include_once("$_PJ_include_path/degestiv.inc.php");
		exit;
	}

	if(isset($gid) and intval($gid)) {
		$group = new Group($gid);
	}

	if(isset($new)) {
		$center_title		= $GLOBALS['_PJ_strings']['new_group'];
		include("$_PJ_root/templates/add.ihtml");
		include_once("$_PJ_include_path/degestiv.inc.php");
		exit;
	}

	if(isset($edit)) {
		if(isset($altered)) {
			$data = array();
			$data['id']		= $id;
			$data['name']	= add_slashes($name);

			$new_group = new Group($data);
			$message = $new_group->save();
			if($message != '') {
				$center_title		= $GLOBALS['_PJ_strings']['edit_group'];
				include("$_PJ_root/templates/edit.ihtml");
				include_once("$_PJ_include_path/degestiv.inc.php");
				exit;
			}
				
			$list = 1;
		} else {
			$center_title		= $GLOBALS['_PJ_strings']['edit_group'];
			include("$_PJ_root/templates/edit.ihtml");
			include_once("$_PJ_include_path/degestiv.inc.php");
			exit;
		}
	}

	if(isset($delete) && !isset($cancel)) {
		if(isset($confirm)) {
			$group->delete();
			$list = 1;
		} else {
			$center_title		= $GLOBALS['_PJ_strings']['group'] . " '" . $group->giveValue('name') . "' " . $GLOBALS['_PJ_strings']['action_delete'];
			include("$_PJ_root/templates/delete.ihtml");
			include_once("$_PJ_include_path/degestiv.inc.php");
			exit;
		}
	}

	$groups = new GroupList();
	$center_title		= $GLOBALS['_PJ_strings']['group_list'];
	include("$_PJ_root/templates/list.ihtml");

	include_once("$_PJ_include_path/degestiv.inc.php");

?>
