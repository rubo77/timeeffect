<?php
	include_once("../include/config.inc.php");
	include_once($_PJ_include_path . '/scripts.inc.php');

	$customer	= new Customer($cid, $_PJ_auth);
	$project	= new Project($customer, $_PJ_auth, $pid);

	$center_template	= "statistic/csv";
	include("$_PJ_root/templates/statistic/csv/list.ihtml");

	include_once("$_PJ_include_path/degestiv.inc.php");
?>