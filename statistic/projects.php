<?php
	include_once("../include/aperetiv.inc.php");

	$customer	= new Customer($cid, $_PJ_auth);
	$project	= new Project($cutomer, $_PJ_auth, $pid);

	if(isset($eid)) {
		$effort = new Effort($eid, $_PJ_auth);
	}

	$center_template	= "statistic/project";

	if($expand) {
		$efforts = new EffortList($customer, $project, $_PJ_auth);
	}
	$projects = new ProjectList($customer, $_PJ_auth, $shown['cp']);

	$center_title		= $GLOBALS['_PJ_strings']['statistics'] . ': ' . 'Projektliste';
	if($customer->giveValue('id')) {
		$center_title .= ': ' . $customer->giveValue('customer_name');
	}

	if(isset($pdf)) {
		$efforts = new EffortList($customer, $project, $_PJ_auth, $shown['be']);
		include("$_PJ_root/templates/statistic/project/pdf.ihtml");
		exit;
	}

	include("$_PJ_root/templates/list.ihtml");

	include_once("$_PJ_include_path/degestiv.inc.php");
?>