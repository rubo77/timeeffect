<?php
    require_once(__DIR__ . "/../bootstrap.php"); // Modern PHP 8.4 compatibility
	include_once("../include/config.inc.php");
	include_once($_PJ_include_path . '/scripts.inc.php');

	// Fix: Initialize request variables to prevent undefined variable warnings
	$cid = isset($_REQUEST['cid']) ? $_REQUEST['cid'] : '';
	$pid = isset($_REQUEST['pid']) ? $_REQUEST['pid'] : '';
	$eid = isset($_REQUEST['eid']) ? $_REQUEST['eid'] : '';
	$shown = isset($_REQUEST['shown']) ? $_REQUEST['shown'] : array();
	$detail = isset($_REQUEST['detail']) ? $_REQUEST['detail'] : '';
	$pdf = isset($_REQUEST['pdf']) ? $_REQUEST['pdf'] : '';

	$customer = new Customer($_PJ_auth, $cid);

	$effort = new Effort($eid, $_PJ_auth);
	if($pid == '') {
		if(isset($effort) && is_object($effort)) {
			$pid = $effort->giveValue('project_id');
		} else {
			exit;
		}
	}
	$project = new Project($customer, $_PJ_auth, $pid);

	if($cid == '') {
		if(isset($project) && is_object($project)) {
			$cid = $project->giveValue('customer_id');
		} else {
			exit;
		}
	}
	$center_template	= "statistic/effort";

	if(!empty($detail)) {
		$center_title		= $GLOBALS['_PJ_strings']['statistics'] . ': ' . $effort->giveValue('description');
		include("$_PJ_root/templates/note.ihtml");
		exit;
	}

	if(!empty($pdf)) {
		// Fix: Add isset check for array key 'be' to prevent array offset warning
		$be_value = isset($shown['be']) ? $shown['be'] : false;
		$efforts = new EffortList($customer, $project, $_PJ_auth, $be_value);
		include("$_PJ_root/templates/statistic/effort/pdf.ihtml");
		exit;
	}

	// Fix: Add isset check for array key 'be' to prevent array offset warning
	$be_value = isset($shown['be']) ? $shown['be'] : false;
	$efforts			= new EffortList($customer, $project, $_PJ_auth, $be_value);
	$center_title		= $GLOBALS['_PJ_strings']['statistics'] . ': ' . $GLOBALS['_PJ_strings']['efforts'];
	if(!empty($pid)) {
		$center_title		= $GLOBALS['_PJ_strings']['statistics'] . ': ' . $GLOBALS['_PJ_strings']['effort_for'] . " '" . $project->giveValue('project_name') . "'";
	}
	include("$_PJ_root/templates/list.ihtml");

	include_once("$_PJ_include_path/degestiv.inc.php");
?>