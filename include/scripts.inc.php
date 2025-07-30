<?php
	$GLOBALS['_PJ_timeeffect_version']			= '2.0';
	$GLOBALS['_PJ_timeeffect_revision']			= '24';
	$GLOBALS['_PJ_timeeffect_date']				= '07/25/2025 15:14:32';

	$GLOBALS['_PJ_customer_inventory_script']	= $GLOBALS['_PJ_http_root'] . '/inventory/customer.'	. $GLOBALS['_PJ_php_suffix'];
	$GLOBALS['_PJ_customer_script']				= $GLOBALS['_PJ_customer_inventory_script']; // Alias for template compatibility
	$GLOBALS['_PJ_projects_inventory_script']	= $GLOBALS['_PJ_http_root'] . '/inventory/projects.'	. $GLOBALS['_PJ_php_suffix'];
	$GLOBALS['_PJ_project_script']				= $GLOBALS['_PJ_projects_inventory_script']; // Alias for template compatibility
	$GLOBALS['_PJ_efforts_inventory_script']	= $GLOBALS['_PJ_http_root'] . '/inventory/efforts.'		. $GLOBALS['_PJ_php_suffix'];

	$GLOBALS['_PJ_customer_statistics_script']	= $GLOBALS['_PJ_http_root'] . '/statistic/customer.'	. $GLOBALS['_PJ_php_suffix'];
	$GLOBALS['_PJ_projects_statistics_script']	= $GLOBALS['_PJ_http_root'] . '/statistic/projects.'	. $GLOBALS['_PJ_php_suffix'];
	$GLOBALS['_PJ_efforts_statistics_script']	= $GLOBALS['_PJ_http_root'] . '/statistic/efforts.'		. $GLOBALS['_PJ_php_suffix'];
	$GLOBALS['_PJ_csv_statistics_script']		= $GLOBALS['_PJ_http_root'] . '/statistic/csv.'			. $GLOBALS['_PJ_php_suffix'];
	$GLOBALS['_PJ_pdf_statistics_script']		= $GLOBALS['_PJ_http_root'] . '/statistic/pdf.'			. $GLOBALS['_PJ_php_suffix'];
	$GLOBALS['_PJ_statistics_script']			= $GLOBALS['_PJ_customer_statistics_script'];
	$GLOBALS['_PJ_pdf_admin_script']			= $GLOBALS['_PJ_http_root'] . '/admin/pdflayout.'		. $GLOBALS['_PJ_php_suffix'];
	$GLOBALS['_PJ_admin_script']				= $GLOBALS['_PJ_pdf_admin_script'];

	$GLOBALS['_PJ_help_script']					= $GLOBALS['_PJ_http_root'] . '/help.'			. $GLOBALS['_PJ_php_suffix'];

	$GLOBALS['_PJ_reports_script']				= $GLOBALS['_PJ_http_root'] . '/report/index.'	. $GLOBALS['_PJ_php_suffix'];

	$GLOBALS['_PJ_billing_script']				= $GLOBALS['_PJ_http_root'] . '/billing/index.' . $GLOBALS['_PJ_php_suffix'];

	$GLOBALS['_PJ_user_script']					= $GLOBALS['_PJ_http_root'] . '/user/index.'	. $GLOBALS['_PJ_php_suffix'];
	$GLOBALS['_PJ_own_user_script']				= $GLOBALS['_PJ_http_root'] . '/user/settings.'		. $GLOBALS['_PJ_php_suffix'];

	$GLOBALS['_PJ_group_script']				= $GLOBALS['_PJ_http_root'] . '/groups/index.'	. $GLOBALS['_PJ_php_suffix'];