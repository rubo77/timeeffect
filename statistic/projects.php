<?php
	include_once("../include/aperetiv.inc.php");

	$customer	= new Customer($cid);
	$project	= new Project($pid);

	if(isset($eid)) {
		$effort = new Effort($eid);
	}

	$center_template	= "statistic/project";

	if($expand) {
		$efforts = new EffortList($pid);
	}
	$projects = new ProjectList($cid, $shown['cp']);

	$center_title		= $GLOBALS['_PJ_strings']['statistics'] . ': ' . 'Projektliste';
	if($customer->giveValue('id')) {
		$center_title .= ': ' . $customer->giveValue('customer_name');
	}

	if(isset($pdf)) {
		$efforts = new EffortList($pid, $shown['be'], $cid);
		include("$_PJ_root/templates/statistic/project/pdf.ihtml");
		exit;
	}

	include("$_PJ_root/templates/list.ihtml");

	include_once("$_PJ_include_path/degestiv.inc.php");
?>