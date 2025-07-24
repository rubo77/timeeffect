<?php
    require_once(__DIR__ . "/../bootstrap.php"); // Modern PHP 8.4 compatibility
	include_once("../include/config.inc.php");
	include_once($_PJ_include_path . '/scripts.inc.php');

	// Initialisierung von Variablen für PHP 8.4 Kompatibilität
	$pid = $_REQUEST['pid'] ?? '';
	$cid = $_REQUEST['cid'] ?? '';
	$shown = $_REQUEST['shown'] ?? [];
	$report = $_REQUEST['report'] ?? null;
	$syear = $_REQUEST['syear'] ?? null;
	$eyear = $_REQUEST['eyear'] ?? null;

	$customer 			= new Customer($cid, $_PJ_auth);
	$project 			= new Project($customer, $_PJ_auth, $pid);

	$center_template	= "report";

	$center_title		= $GLOBALS['_PJ_strings']['report'];

	if(!isset($report)) {
		$customers = new CustomerList($_PJ_auth, @$shown['ic']);
		$projects = new ProjectList($customer, $_PJ_auth, isset($shown['cp']) ? $shown['cp'] : false, 0);
		include("$_PJ_root/templates/edit.ihtml");
		exit;
	} elseif(!$syear || !$eyear) {
		include("$_PJ_root/templates/edit.ihtml");
		exit;
	} elseif(!empty($pdf)) {
		include("$_PJ_root/templates/statistic/pdf/list.ihtml");
		exit;
	}

	if(empty($smonth)) {
		$smonth = 1;
	}
	if(empty($sday)) {
		$sday = 1;
	}
	if(empty($emonth)) {
		$emonth = intval(date('m'));
	}
	if(empty($eday)) {
		$eday = intval(date('d'));
	}

	$statistic	= new Statistics($_PJ_auth, false, $customer, $project, $users, $mode);
	if($_PJ_auth->checkPermission('accountant') && is_array($charge)) {
		$statistic->billEfforts(date('Y-m-d'), implode(',', array_keys($charge)));
	}

	$statistic->loadTime("$syear-$smonth-$sday", "$eyear-$emonth-$eday", $mode);
	include("$_PJ_root/templates/list.ihtml");

	include_once("$_PJ_include_path/degestiv.inc.php");
?>