<?php
	include_once("../include/aperetiv.inc.php");
	include_once($_PJ_include_path . '/scripts.inc.php');

	if(isset($cid)) {
		$customer 			= new Customer($cid, $_PJ_auth);
	}
	$center_template	= "statistic/customer";

	if(isset($expand)) {
		$projects	= new ProjectList($customer, $_PJ_auth);
		if(isset($pid)) {
			$project = new Project($customer, $_PJ_auth, $pid);
			$efforts		= new EffortList($customer, $project, $_PJ_auth);
		}
	}

	$customer_list = new CustomerList($_PJ_auth, $shown['ic']);
	$center_template	= "statistic/customer";
	$center_title		= $GLOBALS['_PJ_strings']['statistics'] . ': ' . 'Kundenliste';
	include("$_PJ_root/templates/list.ihtml");

	include_once("$_PJ_include_path/degestiv.inc.php");
?>