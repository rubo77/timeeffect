<?php
	include_once("../include/aperetiv.inc.php");

	$customer	= new Customer($cid);
	$project	= new Project($pid);

	$center_template	= "statistic/pdf";
	include("$_PJ_root/templates/statistic/pdf/list.ihtml");

	include_once("$_PJ_include_path/degestiv.inc.php");
?>