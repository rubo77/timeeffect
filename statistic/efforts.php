<?php
	include_once("../include/aperetiv.inc.php");

	$effort = new Effort($eid);
	if($pid == '') {
		if(is_object($effort)) {
			$pid = $effort->giveValue('project_id');
		} else {
			exit;
		}
	}
	$project = new Project($pid);

	if($cid == '') {
		if(is_object($project)) {
			$cid = $project->giveValue('customer_id');
		} else {
			exit;
		}
	}
	$customer = new Customer($cid);
	$center_template	= "statistic/effort";

	if(isset($detail)) {
		$center_title		= $GLOBALS['_PJ_strings']['statistics'] . ': ' . $effort->giveValue('description');
		include("$_PJ_root/templates/note.ihtml");
		exit;
	}

	if(isset($pdf)) {
		$efforts = new EffortList($pid, $shown['be'], $cid);
		include("$_PJ_root/templates/statistic/effort/pdf.ihtml");
		exit;
	}

	$efforts			= new EffortList($pid, $shown['be'], $cid);
	$center_title		= $GLOBALS['_PJ_strings']['statistics'] . ': ' . $GLOBALS['_PJ_strings']['efforts'];
	if($pid) {
		$center_title		= $GLOBALS['_PJ_strings']['statistics'] . ': ' . $GLOBALS['_PJ_strings']['effort_for'] . " '" . $project->giveValue('project_name') . "'";
	}
	include("$_PJ_root/templates/list.ihtml");

	include_once("$_PJ_include_path/degestiv.inc.php");
?>