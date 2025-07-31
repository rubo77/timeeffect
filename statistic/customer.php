<?php
    require_once(__DIR__ . "/../bootstrap.php"); // Modern PHP 8.4 compatibility
	include_once("../include/config.inc.php");
	include_once($_PJ_include_path . '/scripts.inc.php');

	// Initialize variables from request
	$cid = $_REQUEST['cid'] ?? null;
	$expand = $_REQUEST['expand'] ?? null;
	$pid = $_REQUEST['pid'] ?? null;

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

	$customer_list = new CustomerList($_PJ_auth, @$shown['ic']);
	$center_template	= "statistic/customer";
	$center_title		= $GLOBALS['_PJ_strings']['statistics'] . ': ' . 'Kundenliste';
	include("$_PJ_root/templates/list.ihtml.php");

	include_once("$_PJ_include_path/degestiv.inc.php");
	
