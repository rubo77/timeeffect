<?php
	switch($GLOBALS['PHP_SELF']) {
		case $GLOBALS['_PJ_customer_inventory_script']:
		case $GLOBALS['_PJ_projects_inventory_script']:
		case $GLOBALS['_PJ_efforts_inventory_script']:
			include("$_PJ_root/templates/shared/inventory/left.ihtml.php");
			break;
		case $GLOBALS['_PJ_customer_statistics_script']:
		case $GLOBALS['_PJ_projects_statistics_script']:
		case $GLOBALS['_PJ_efforts_statistics_script']:
			include("$_PJ_root/templates/shared/statistic/left.ihtml.php");
			break;
		case $GLOBALS['_PJ_reports_script']:
			include("$_PJ_root/templates/shared/report/left.ihtml.php");
			break;
		default:
			include("$_PJ_root/templates/shared/empty/left.ihtml.php");
			break;
	}
?>
