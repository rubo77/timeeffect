<?php
    require_once(__DIR__ . "/../bootstrap.php"); // Modern PHP 8.4 compatibility
	include_once("../include/config.inc.php");
	include_once($_PJ_include_path . '/scripts.inc.php');

	// Initialize variables from request
	$cid = $_REQUEST['cid'] ?? '';
	$pid = $_REQUEST['pid'] ?? '';
	$eid = $_REQUEST['eid'] ?? null;
	$expand = $_REQUEST['expand'] ?? null;
	$pdf = $_REQUEST['pdf'] ?? null;
	$shown = $_REQUEST['shown'] ?? [];

	$customer	= new Customer($cid, $_PJ_auth);
	$project	= new Project($customer, $_PJ_auth, $pid);

	if(isset($eid)) {
		$effort = new Effort($eid, $_PJ_auth);
	}

	$center_template	= "statistic/project";

	if(!empty($expand)) {
		$efforts = new EffortList($customer, $project, $_PJ_auth);
	}
	$projects = new ProjectList($customer, $_PJ_auth, isset($shown['cp']) ? $shown['cp'] : false);

	$center_title		= $GLOBALS['_PJ_strings']['statistics'] . ': ' . 'Projektliste';
	if($customer->giveValue('id')) {
		$center_title .= ': ' . $customer->giveValue('customer_name');
	}

	if(isset($pdf)) {
		$efforts = new EffortList($customer, $project, $_PJ_auth, $shown['be']);
		include("$_PJ_root/templates/statistic/project/pdf.ihtml.php");
		exit;
	}

	include("$_PJ_root/templates/list.ihtml.php");

	include_once("$_PJ_include_path/degestiv.inc.php");
?>