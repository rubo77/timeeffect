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
			if($data['user'] == '') {
				$data['user']	= $effort->giveValue('user');
			}
			if($data['user'] == '') {
				$data['user']	= $_PJ_auth->giveValue('id');
			}
			if($data['gid'] == '') {
				$data['gid']	= $effort->giveValue('gid');
			}
			if($data['access'] == '') {
				$data['access']	= $effort->giveValue('access');
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

	if($pid && !$project->checkUserAccess('read')) {
		$error_message		= $GLOBALS['_PJ_strings']['error_access'];
		include("$_PJ_root/templates/error.ihtml");
		include_once("$_PJ_include_path/degestiv.inc.php");
		exit;
	}
	$sort_order = $_GET['sort'] ?? 'desc';
	$efforts			= new EffortList($customer, $project, $_PJ_auth, isset($shown['be']) ? $shown['be'] : false, NULL, $sort_order);
	$center_title		= $GLOBALS['_PJ_strings']['inventory'] . ': ' . $GLOBALS['_PJ_strings']['effort_list'] . " " . $project->giveValue('project_name');
	include("$_PJ_root/templates/list.ihtml");

	include_once("$_PJ_include_path/degestiv.inc.php");
?>
