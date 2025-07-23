<?php
    require_once(__DIR__ . "/../bootstrap.php"); // Modern PHP 8.4 compatibility
	include_once("../include/config.inc.php");
	include_once($_PJ_include_path . '/scripts.inc.php');

	$customer	= new Customer($cid, $_PJ_auth);
	$project	= new Project($customer, $_PJ_auth, $pid);

	$center_template	= "statistic/pdf";
	include("$_PJ_root/templates/statistic/pdf/list.ihtml");

	include_once("$_PJ_include_path/degestiv.inc.php");
?>