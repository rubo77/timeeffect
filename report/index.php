<?php
	include_once("../include/aperetiv.inc.php");

	$customer 			= new Customer($cid);
	$project 			= new Project($pid);
	$effort 			= new Effort($eid);

	$center_template	= "report";

	$center_title		= $GLOBALS['_PJ_strings']['report'];

	if(!isset($report)) {
		$customers = new CustomerList($shown['ic']);
		$projects = new ProjectList($cid, $shown['cp'], 0);
		include("$_PJ_root/templates/edit.ihtml");
		exit;
	} elseif(!$syear || !$eyear) {
		include("$_PJ_root/templates/edit.ihtml");
		exit;
	} elseif($pdf) {
		include("$_PJ_root/templates/statistic/pdf/list.ihtml");
		exit;
	}

	if(!$smonth) {
		$smonth = 1;
	}
	if(!$sday) {
		$sday = 1;
	}
	if(!$emonth) {
		$emonth = intval(date('m'));
	}
	if(!$eday) {
		$eday = intval(date('d'));
	}

	$statistic	= new Statistics(false, $cid, $pid, ($mode));
	$statistic->loadTime("$syear-$smonth-$sday", "$eyear-$emonth-$eday", $mode);
	include("$_PJ_root/templates/list.ihtml");

	include_once("$_PJ_include_path/degestiv.inc.php");
?>