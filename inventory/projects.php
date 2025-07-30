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

	// Auto-lookup CID from database if pid is provided but cid is missing
	if ($pid && !$cid) {
		$db = new Database();
		$db->connect();
		$safePid = DatabaseSecurity::escapeString($pid, $db->Link_ID);
		$query = "SELECT customer_id FROM " . $GLOBALS['_PJ_project_table'] . " WHERE id='$safePid'";
		$db->query($query);
		if ($db->next_record()) {
			$cid = $db->Record['customer_id'];
			debugLog("LOG_CID_LOOKUP", "Auto-loaded cid=$cid for pid=$pid");
		} else {
			debugLog("LOG_CID_LOOKUP", "Failed to find project with pid=$pid");
		}
	}

	// Only create Customer object if valid cid is provided
	$customer = $cid ? new Customer($_PJ_auth, $cid) : null;
	
	// LOG_CUSTOMER_INIT: Log customer initialization status
	if (!$customer) {
		debugLog("LOG_CUSTOMER_INIT", "No customer ID provided, customer object is null");
	}

	// Create Project object only if valid customer and pid are provided
	if ($customer && $pid) {
		// LOG_PROJECT_INIT: Loading existing project
		debugLog("LOG_PROJECT_INIT", "Loading existing project with pid=$pid for customer $cid");
		$project = new Project($customer, $_PJ_auth, $pid);
	} else {
		// LOG_PROJECT_INIT: No project object for new projects or missing data
		debugLog("LOG_PROJECT_INIT", "No project object created - new project or missing customer/pid");
		$project = null;
	}

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

			// LOG_PROJECT_SAVE: Set defaults for empty fields
			if($data['gid'] == '') {
				if ($project && $project->giveValue('gid')) {
					// Use existing project's gid
					$data['gid'] = $project->giveValue('gid');
					debugLog("LOG_PROJECT_SAVE", "Using existing project gid: " . $data['gid']);
				} else {
					// Use user's default gid for new projects
					$data['gid'] = $_PJ_auth->giveValue('gid');
					debugLog("LOG_PROJECT_SAVE", "Using user default gid for new project: " . $data['gid']);
				}
			}
			if($data['access'] == '') {
				if ($project && $project->giveValue('access')) {
					// Use existing project's access
					$data['access'] = $project->giveValue('access');
					debugLog("LOG_PROJECT_SAVE", "Using existing project access: " . $data['access']);
				} else {
					// Use default access for new projects (owner: read/write, group: read, world: none)
					$data['access'] = 'rw-r-----';
					debugLog("LOG_PROJECT_SAVE", "Using default access for new project: " . $data['access']);
				}
			}
			if($data['user'] == '') {
				if ($project && $project->giveValue('user')) {
					// Use existing project's user
					$data['user'] = $project->giveValue('user');
					debugLog("LOG_PROJECT_SAVE", "Using existing project user: " . $data['user']);
				} else {
					// Use current user for new projects
					$data['user'] = $_PJ_auth->giveValue('id');
					debugLog("LOG_PROJECT_SAVE", "Using current user for new project: " . $data['user']);
				}
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

	// LOG_TITLE_GENERATION: Set appropriate title based on customer context
	if ($customer) {
		// Single customer view
		debugLog("LOG_TITLE_GENERATION", "Generating title for single customer: " . $customer->giveValue('customer_name'));
		$center_title = $GLOBALS['_PJ_strings']['inventory'] . ': ' . $GLOBALS['_PJ_strings']['project_list'] . " " . $customer->giveValue('customer_name');
	} else {
		// All projects view
		debugLog("LOG_TITLE_GENERATION", "Generating title for all projects view");
		$center_title = $GLOBALS['_PJ_strings']['inventory'] . ': ' . $GLOBALS['_PJ_strings']['project_list'];
	}
	include("$_PJ_root/templates/list.ihtml");

	include_once("$_PJ_include_path/degestiv.inc.php");
?>