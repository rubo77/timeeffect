<?php
    require_once(__DIR__ . "/../bootstrap.php"); // Modern PHP 8.4 compatibility
	include_once("../include/config.inc.php");
	include_once($_PJ_include_path . '/scripts.inc.php');

	$eid = $_REQUEST['eid'] ?? null;
	$stop = $_REQUEST['stop'] ?? null;
	$pid = $_REQUEST['pid'] ?? null;
	$cid = $_REQUEST['cid'] ?? null;
	$cont = $_REQUEST['cont'] ?? null;
	$new = $_REQUEST['new'] ?? null;
	$edit = $_REQUEST['edit'] ?? null;
	$altered = $_REQUEST['altered'] ?? null;
	$year = $_REQUEST['year'] ?? null;
	$month = $_REQUEST['month'] ?? null;
	$day = $_REQUEST['day'] ?? null;
	$hour = $_REQUEST['hour'] ?? null;
	$minute = $_REQUEST['minute'] ?? null;
	$second = $_REQUEST['second'] ?? null;
	$description = $_REQUEST['description'] ?? null;
	$note = $_REQUEST['note'] ?? null;
	$rate = $_REQUEST['rate'] ?? null;
	$user = $_REQUEST['user'] ?? null;
	$gid = $_REQUEST['gid'] ?? null;
	$access_owner = $_REQUEST['access_owner'] ?? null;
	$access_group = $_REQUEST['access_group'] ?? null;
	$access_world = $_REQUEST['access_world'] ?? null;
	$billing_day = $_REQUEST['billing_day'] ?? null;
	$billing_month = $_REQUEST['billing_month'] ?? null;
	$billing_year = $_REQUEST['billing_year'] ?? null;
	$hours = $_REQUEST['hours'] ?? null;
	$minutes = $_REQUEST['minutes'] ?? null;
	$detail = $_REQUEST['detail'] ?? null;
	$pdf = $_REQUEST['pdf'] ?? null;
	$delete = $_REQUEST['delete'] ?? null;
	$cancel = $_REQUEST['cancel'] ?? null;
	$confirm = $_REQUEST['confirm'] ?? null;
	$shown = $_REQUEST['shown'] ?? array();
	$list = $_REQUEST['list'] ?? null;

	// Initialize Customer and Project objects (required for efforts)
	$customer = $cid ? new Customer($_PJ_auth, $cid) : null;
	
	// LOG_CUSTOMER_INIT: Log customer initialization status in efforts.php
	if (!$customer) {
		error_log("LOG_CUSTOMER_INIT: No customer ID provided in efforts.php, customer object is null");
	}
	
	// Create Project object only if valid customer and pid are provided
	if ($customer && $pid) {
		// LOG_PROJECT_INIT: Loading existing project in efforts.php
		error_log("LOG_PROJECT_INIT: Loading existing project with pid=$pid for customer $cid in efforts.php");
		$project = new Project($customer, $_PJ_auth, $pid);
	} else {
		// LOG_PROJECT_INIT: No project object for new efforts or missing data
		error_log("LOG_PROJECT_INIT: No project object created in efforts.php - new effort or missing customer/pid");
		$project = null;
	}

	// Only create Effort object if valid eid is provided
	$effort = $eid ? new Effort($eid, $_PJ_auth) : null;
	if(!empty($stop)) {
		if($eid && !$effort->checkUserAccess('write')) {
			$error_message		= $GLOBALS['_PJ_strings']['error_access'];
			include("$_PJ_root/templates/error.ihtml");
			include_once("$_PJ_include_path/degestiv.inc.php");
			exit;
		}
		$effort->stop();
	}
	// Fix object creation order: Customer first, then Project
	if($pid === null) {
		if($effort && is_object($effort)) {
			$pid = $effort->giveValue('project_id');
		}
	}
	
	if($cid === null) {
		if($effort && is_object($effort)) {
			// Get cid from effort's project
			$temp_pid = $effort->giveValue('project_id');
			if($temp_pid) {
				$temp_db = new Database();
				$temp_db->query("SELECT customer_id FROM " . $GLOBALS['_PJ_project_table'] . " WHERE id='$temp_pid'");
				if($temp_db->next_record()) {
					$cid = $temp_db->f('customer_id');
				}
			}
		}
	}
	
	// Only create objects if valid IDs are provided
	$customer = $cid ? new Customer($_PJ_auth, $cid) : null;
	$project = ($customer && $pid) ? new Project($customer, $_PJ_auth, $pid) : null;
	$center_template	= "inventory/effort";

	if(!empty($cont)) {
		if($eid && !$project->checkUserAccess('new')) {
			$error_message		= $GLOBALS['_PJ_strings']['error_access'];
			include("$_PJ_root/templates/error.ihtml");
			include_once("$_PJ_include_path/degestiv.inc.php");
			exit;
		}
		$new_effort = $effort->copy($_PJ_auth);
		$new_effort->save();
	}

	if(isset($new)) {
		$center_title		= $GLOBALS['_PJ_strings']['inventory'] . ': ' . $GLOBALS['_PJ_strings']['new_effort'];
		include("$_PJ_root/templates/add.ihtml");
		exit;
	}

	if(isset($edit)) {
		if($eid && !$effort->checkUserAccess('write')) {
			$error_message		= $GLOBALS['_PJ_strings']['error_access'];
			include("$_PJ_root/templates/error.ihtml");
			include_once("$_PJ_include_path/degestiv.inc.php");
			exit;
		}
		if(isset($altered)) {
			// last_description mod by Ruben Barkow -- START
			$_SESSION['last_description'] = $description;
			// last_description mod by Ruben Barkow -- END

			$data = array();
			$data['id']				= $eid;
			$data['project_id']		= $pid;
			$data['date']			= "$year-$month-$day";
			$data['begin']			= sprintf('%02d:%02d:%02d', intval($hour), intval($minute), intval($second));
			$data['description']	= add_slashes($description);
			$data['note']			= add_slashes($note);
			$data['rate']			= $rate;
			$data['user']			= $user;
			$data['gid']			= $gid;
			$data['access']			= $access_owner . $access_group . $access_world;
			// LOG_EFFORT_SAVE: Set defaults for empty fields
			if($data['user'] == '') {
				if ($effort && $effort->giveValue('user')) {
					// Use existing effort's user
					$data['user'] = $effort->giveValue('user');
					error_log("LOG_EFFORT_SAVE: Using existing effort user: " . $data['user']);
				} else {
					// Use current user for new efforts
					$data['user'] = $_PJ_auth->giveValue('id');
					error_log("LOG_EFFORT_SAVE: Using current user for new effort: " . $data['user']);
				}
			}
			if($data['user'] == '') {
				$data['user']	= $_PJ_auth->giveValue('id');
			}
			if($data['gid'] == '') {
				if ($effort && $effort->giveValue('gid')) {
					// Use existing effort's gid
					$data['gid'] = $effort->giveValue('gid');
					error_log("LOG_EFFORT_SAVE: Using existing effort gid: " . $data['gid']);
				} else {
					// Use user's default gid for new efforts
					$data['gid'] = $_PJ_auth->giveValue('gid');
					error_log("LOG_EFFORT_SAVE: Using user default gid for new effort: " . $data['gid']);
				}
			}
			if($data['access'] == '') {
				if ($effort && $effort->giveValue('access')) {
					// Use existing effort's access
					$data['access'] = $effort->giveValue('access');
					error_log("LOG_EFFORT_SAVE: Using existing effort access: " . $data['access']);
				} else {
					// Use default access for new efforts (owner: read/write, group: read, world: none)
					$data['access'] = 'rw-r-----';
					error_log("LOG_EFFORT_SAVE: Using default access for new effort: " . $data['access']);
				}
			}
			if(date("Y", strtotime("$billing_day/$billing_month/$billing_year")) > 1970) {
				$data['billed']			= "'$billing_year-$billing_month-$billing_day'";
			} else {
				$data['billed']			= "NULL";
			}
	
			$new_effort = new Effort($data, $_PJ_auth);
			$new_effort->setEndTime("$hours:$minutes");
			$message = $new_effort->save();
			if($message != '') {
				if(!$new_effort->giveValue('id')) {
					$center_title		= $GLOBALS['_PJ_strings']['inventory'] . ': ' . $GLOBALS['_PJ_strings']['new_effort'];
					include("$_PJ_root/templates/add.ihtml");
				} else {
					$center_title		= $GLOBALS['_PJ_strings']['inventory'] . ': ' . $GLOBALS['_PJ_strings']['edit_effort'];
					include("$_PJ_root/templates/edit.ihtml");
				}
				include_once("$_PJ_include_path/degestiv.inc.php");
				exit;
			}
			$list = 1;
		} else {
			$center_title		= $GLOBALS['_PJ_strings']['inventory'] . ': ' . $GLOBALS['_PJ_strings']['edit_effort'];
			include("$_PJ_root/templates/edit.ihtml");
			exit;
		}
	}

	if(isset($detail)) {
		$center_title		= $GLOBALS['_PJ_strings']['inventory'] . ': ' . $effort->giveValue('description');
		include("$_PJ_root/templates/note.ihtml");
		exit;
	}

	if(isset($pdf)) {
		$efforts = new EffortList($customer, $project, $_PJ_auth, $shown['be']);
		include("$_PJ_root/templates/effort/pdf.ihtml");
		exit;
	}

	if(isset($delete) && !isset($cancel)) {
		if(!$effort->checkUserAccess('write') || (!$_PJ_auth->checkPermission('accountant') && !$GLOBALS['_PJ_agents_allow_delete'])) {
			$error_message		= $GLOBALS['_PJ_strings']['error_access'];
			include("$_PJ_root/templates/error.ihtml");
			include_once("$_PJ_include_path/degestiv.inc.php");
			exit;
		}
		if(isset($confirm)) {
			$effort->delete();
			unset($effort);
			$list = 1;
		} else {
			$center_title		= $GLOBALS['_PJ_strings']['inventory'] . ': ' . $GLOBALS['_PJ_strings']['effort'] . " '" . $effort->giveValue('description') . "' " . $GLOBALS['_PJ_strings']['action_delete'];
			include("$_PJ_root/templates/delete.ihtml");
			exit;
		}
	}

	// LOG_PROJECT_ACCESS: Check project object before accessing checkUserAccess method
	if($pid && $project && !$project->checkUserAccess('read')) {
		error_log("LOG_PROJECT_ACCESS: Access denied for project $pid by user " . $_PJ_auth->giveValue('id'));
		$error_message		= $GLOBALS['_PJ_strings']['error_access'];
		include("$_PJ_root/templates/error.ihtml");
		include_once("$_PJ_include_path/degestiv.inc.php");
		exit;
	} elseif ($pid && !$project) {
		error_log("LOG_PROJECT_ACCESS: No project object available for pid=$pid, allowing access");
	}
	$sort_order = $_GET['sort'] ?? 'desc';
	$efforts			= new EffortList($customer, $project, $_PJ_auth, isset($shown['be']) ? $shown['be'] : false, NULL, $sort_order);
	// LOG_TITLE_GENERATION: Set appropriate title based on project context
	if ($project && $project->giveValue('project_name')) {
		// Single project view
		error_log("LOG_TITLE_GENERATION: Generating title for single project: " . $project->giveValue('project_name'));
		$center_title = $GLOBALS['_PJ_strings']['inventory'] . ': ' . $GLOBALS['_PJ_strings']['effort_list'] . " " . $project->giveValue('project_name');
	} elseif ($customer && $customer->giveValue('customer_name')) {
		// Customer-specific efforts view
		error_log("LOG_TITLE_GENERATION: Generating title for customer efforts: " . $customer->giveValue('customer_name'));
		$center_title = $GLOBALS['_PJ_strings']['inventory'] . ': ' . $GLOBALS['_PJ_strings']['effort_list'] . " " . $customer->giveValue('customer_name');
	} else {
		// All efforts view
		error_log("LOG_TITLE_GENERATION: Generating title for all efforts view");
		$center_title = $GLOBALS['_PJ_strings']['inventory'] . ': ' . $GLOBALS['_PJ_strings']['effort_list'];
	}
	include("$_PJ_root/templates/list.ihtml");

	include_once("$_PJ_include_path/degestiv.inc.php");
?>
