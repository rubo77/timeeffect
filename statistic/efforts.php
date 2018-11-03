<?php
	include_once("../include/aperetiv.inc.php");
	include_once($_PJ_include_path . '/scripts.inc.php');

	$customer = new Customer($cid, $_PJ_auth);

	$effort = new Effort($eid, $_PJ_auth);
	if($pid == '') {
		if(is_object($effort)) {
			$pid = $effort->giveValue('project_id');
		} else {
			exit;
		}
	}
	$project = new Project($customer, $_PJ_auth, $pid);

	if($cid == '') {
		if(is_object($project)) {
			$cid = $project->giveValue('customer_id');
		} else {
			exit;
		}
	}
	$center_template	= "statistic/effort";

	if(isset($detail)) {
		$center_title		= $GLOBALS['_PJ_strings']['statistics'] . ': ' . $effort->giveValue('description');
		include("$_PJ_root/templates/note.ihtml");
		exit;
	}

	if(isset($pdf)) {
		$efforts = new EffortList($customer, $project, $_PJ_auth, $shown['be']);
		include("$_PJ_root/templates/statistic/effort/pdf.ihtml");
		exit;
	}

	$efforts			= new EffortList($customer, $project, $_PJ_auth, $shown['be']);
	$center_title		= $GLOBALS['_PJ_strings']['statistics'] . ': ' . $GLOBALS['_PJ_strings']['efforts'];
	if(!empty($pid)) {
		$center_title		= $GLOBALS['_PJ_strings']['statistics'] . ': ' . $GLOBALS['_PJ_strings']['effort_for'] . " '" . $project->giveValue('project_name') . "'";
	}
	include("$_PJ_root/templates/list.ihtml");

	include_once("$_PJ_include_path/degestiv.inc.php");
?>