<?php
    // Fix: Start output buffering immediately to prevent 'headers already sent' error
    ob_start();
    
    require_once(__DIR__ . "/../bootstrap.php"); // Modern PHP 8.4 compatibility
	include_once("../include/config.inc.php");
	include_once($_PJ_include_path . '/scripts.inc.php');

	// Fix: Initialize variables to prevent undefined variable warnings
	$cid = isset($cid) ? $cid : (isset($_REQUEST['cid']) ? $_REQUEST['cid'] : '');
	$pid = isset($pid) ? $pid : (isset($_REQUEST['pid']) ? $_REQUEST['pid'] : '');
	$eid = isset($eid) ? $eid : (isset($_REQUEST['eid']) ? $_REQUEST['eid'] : '');

	$customer	= new Customer($cid, $_PJ_auth);
	$project	= new Project($customer, $_PJ_auth, $pid);

	$center_template	= "statistic/csv";
	include("$_PJ_root/templates/statistic/csv/list.ihtml.php");

	include_once("$_PJ_include_path/degestiv.inc.php");
?>