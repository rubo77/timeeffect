<?php
    require_once(__DIR__ . "/../bootstrap.php"); // Modern PHP 8.4 compatibility
	include_once("../include/config.inc.php");
	include_once($_PJ_include_path . '/scripts.inc.php');

	// Initialize variables from request
	$cid = $_REQUEST['cid'] ?? null;
	$pid = $_REQUEST['pid'] ?? null;
	$eid = $_REQUEST['eid'] ?? null;
	$shown = $_REQUEST['shown'] ?? [];
	$new = $_REQUEST['new'] ?? null;
	$edit = $_REQUEST['edit'] ?? null;
	$delete = $_REQUEST['delete'] ?? null;
	$cancel = $_REQUEST['cancel'] ?? null;
	$confirm = $_REQUEST['confirm'] ?? null;
	$altered = $_REQUEST['altered'] ?? null;
	$project_name = $_REQUEST['project_name'] ?? '';
	$project_desc = $_REQUEST['project_desc'] ?? '';
	$project_budget = $_REQUEST['project_budget'] ?? '';
	$project_budget_currency = $_REQUEST['project_budget_currency'] ?? '';
	$closed = $_REQUEST['closed'] ?? '';
	$gid = $_REQUEST['gid'] ?? '';
	$user = $_REQUEST['user'] ?? '';
	$access_owner = $_REQUEST['access_owner'] ?? '';
	$access_group = $_REQUEST['access_group'] ?? '';
	$access_world = $_REQUEST['access_world'] ?? '';
	$expand = $_REQUEST['expand'] ?? '';

	// Only create Customer object if valid cid is provided
	$customer = $cid ? new Customer($_PJ_auth, $cid) : null;

	// Only create Project object if valid customer and pid are provided
	$project = ($customer && $pid) ? new Project($customer, $_PJ_auth, $pid) : null;

	if(isset($eid)) {
		$effort = new Effort($eid, $_PJ_auth);
	}

	$center_template	= "inventory/project";
	if(isset($new)) {
		if(!$customer->checkUserAccess('new')) {
			$error_message		= $GLOBALS['_PJ_strings']['error_access'];
			include("$_PJ_root/templates/error.ihtml");
			include_once("$_PJ_include_path/degestiv.inc.php");
			exit;
		}
		$center_title		= $GLOBALS['_PJ_strings']['inventory'] . ': ' . $GLOBALS['_PJ_strings']['new_project'];
		include("$_PJ_root/templates/add.ihtml");
		exit;
	}
	if(isset($edit)) {
		if($pid && !$project->checkUserAccess('write')) {
			$error_message		= $GLOBALS['_PJ_strings']['error_access'];
			include("$_PJ_root/templates/error.ihtml");
			include_once("$_PJ_include_path/degestiv.inc.php");
			exit;
		}
		if(isset($altered)) {
			$data = array();
			$data['id']							= $pid;
			$data['customer_id']				= $cid;
			$data['project_name']				= add_slashes($project_name);
			$data['project_desc']				= add_slashes($project_desc);
			$data['project_budget']				= intval($project_budget);
			$data['project_budget_currency']	= $project_budget_currency;
			$data['closed']						= $closed;
			$data['gid']						= $gid;
			$data['user']						= $user;
			$data['access']						= $access_owner . $access_group . $access_world;

			if($data['gid'] == '') {
				$data['gid']	= $project->giveValue('gid');
			}
			if($data['access'] == '') {
				$data['access'] = $project->giveValue('access');
			}
			if($data['user'] == '') {
				$data['user']	= $project->giveValue('user');
			}
			if($data['user'] == '') {
				$data['user']	= $_PJ_auth->giveValue('id');
			}
			$project = new Project($customer, $_PJ_auth, $data);
			$project->save();
			$list = 1;
		} else {
			$project_id 		= $pid;
			$center_title		= $GLOBALS['_PJ_strings']['inventory'] . ': ' . $GLOBALS['_PJ_strings']['edit_project'];
			include("$_PJ_root/templates/edit.ihtml");
			exit;
		}
	}

	if(isset($delete) && !isset($cancel)) {
		if(!$project->checkUserAccess('write') || (!$_PJ_auth->checkPermission('accountant') && !$GLOBALS['_PJ_agents_allow_delete'])) {
			$error_message		= $GLOBALS['_PJ_strings']['error_access'];
			include("$_PJ_root/templates/error.ihtml");
			include_once("$_PJ_include_path/degestiv.inc.php");
			exit;
		}
		if(isset($confirm)) {
			$project->delete();
			$list = 1;
		} else {
			$center_title		= $GLOBALS['_PJ_strings']['inventory'] . ': ' . $GLOBALS['_PJ_strings']['project'] . " '" . $project->giveValue('project_name') . "' " . $GLOBALS['_PJ_strings']['action_delete'];
			include("$_PJ_root/templates/delete.ihtml");
			exit;
		}
	}

	if($cid && !$customer->checkUserAccess('read')) {
		$error_message		= $GLOBALS['_PJ_strings']['error_access'];
		include("$_PJ_root/templates/error.ihtml");
		include_once("$_PJ_include_path/degestiv.inc.php");
		exit;
	}
	if(!empty($expand)) {
		$efforts = new EffortList($customer, $project, $_PJ_auth);
	}
	$projects = new ProjectList($customer, $_PJ_auth, isset($shown['cp']) ? $shown['cp'] : false);

	$center_title		= $GLOBALS['_PJ_strings']['inventory'] . ': ' . $GLOBALS['_PJ_strings']['project_list'] . " " . $customer->giveValue('customer_name');
	include("$_PJ_root/templates/list.ihtml");

	include_once("$_PJ_include_path/degestiv.inc.php");
?>