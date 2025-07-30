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
	$name = $_REQUEST['name'] ?? null;

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
		// Check if group has assigned users or objects before allowing deletion
		require_once($_PJ_include_path . '/group_assignments.inc.php');
		$counts = Group_getAssignmentCounts($group);
		
		if($counts['users'] > 0 || $counts['customers'] > 0 || $counts['projects'] > 0 || $counts['efforts'] > 0) {
			// Group has assignments, prevent deletion
			$error_message = sprintf(
				"Cannot delete group '%s'. It has %d users, %d customers, %d projects, and %d efforts assigned. Remove all assignments first.",
				$group->giveValue('name'),
				$counts['users'],
				$counts['customers'],
				$counts['projects'],
				$counts['efforts']
			);
			include("$_PJ_root/templates/error.ihtml");
			include_once("$_PJ_include_path/degestiv.inc.php");
			exit;
		}
		
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
