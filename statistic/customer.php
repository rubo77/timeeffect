<?php
	include_once("../include/aperetiv.inc.php");

	if(isset($cid)) {
		$customer 			= new Customer($cid);
	}
	$center_template	= "statistic/customer";

	if(isset($expand)) {
		$projects	= new ProjectList($cid);
		if(isset($pid)) {
			$efforts		= new EffortList($pid);
		}
	}

	$customer_list = new CustomerList($shown['ic']);
	$center_template	= "statistic/customer";
	$center_title		= $GLOBALS['_PJ_strings']['statistics'] . ': ' . 'Kundenliste';
	include("$_PJ_root/templates/list.ihtml");

	include_once("$_PJ_include_path/degestiv.inc.php");
?>