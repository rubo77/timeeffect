<?php
/* vim: set expandtab shiftwidth=4 softtabstop=4 tabstop=4: */

require_once('fix_php7.php');

session_name('timeeffect');

/* ******************************************************** */
/* customizable variables - START                           */
/* ******************************************************** */

	/*
	   change $_PJ_http_root to the http root directory to be entered in the browser
	   e.g for 'http://www.soin.de/timeffect' the $_PJ_http_root should be set to '/timeffect'
	*/
	$_PJ_http_root		= '/timeeffect';
	/*
	   change $_PJ_root to the directory where timeeffect is located on your web server
	*/
	$_PJ_root			= $DOCUMENT_ROOT . '/timeeffect';

	/*
	   select default language ('en' or 'de' are currently availlable
	*/
	$_PJ_default_language		= 'de';

	$_PJ_decimal_point			= ',';
	$_PJ_thousands_seperator	= '.';
	$_PJ_currency				= 'EUR';

	/*
	   enter database parameters ($_PJ_db_type is currently used for PEAR Module Auth only
	*/
	$_PJ_db_host		= 'localhost';
	$_PJ_db_database	= 'timeeffect';
	$_PJ_db_user		= 'timeeffect';
	$_PJ_db_password	= 'very_unsecure_timeeffect_PW1';

	/*
	   define maximum session length in seconds. Users will be automatically logged of after this period.
	   If set to 0 automated logout will be disabled.
	*/
	$_PJ_session_length			= 3600;

	/*
	   define database table prefix for TIMEEFFECT.
	   NOTE: you have to change the table names in 'sql/timeeffect.sql' according to the prefix
	   A more comfortable installation routine is issue of future releases
	*/
	$_PJ_table_prefix 	= 'te_';

	/*
	   agents are allow to delete efforts
	*/
	$_PJ_agents_allow_delete 	= 1;

/* ******************************************************** */
/* customizable variables - END                             */
/* ******************************************************** */

	$_PJ_budget_security_percentage		= 10;
	/*
	   if $lang is set set language to value of $lang. Otherwise select default language
	*/
	if(isset($lang)) {
		$_PJ_language = $lang;
	} else if(!isset($_PJ_language)) {
		$_PJ_language		= $_PJ_default_language;
	}

	/*
	   $_PJ_db_type is currently used for PEAR Module Auth only. Leave this untouched!
	*/
	$_PJ_db_type		= 'mysqli';
	$_PJ_include_path	= $_PJ_root . '/include';

	if(!isset($_PJ_root)) {
		print '<b>ERROR:</b> $_PJ_root is not defined! (' . __FILE__ . ', line: 21)';
		exit;
	}
	if(!isset($_PJ_http_root)) {
		print '<b>ERROR:</b> $_PJ_http_root is not defined! (' . __FILE__ . ', line: 17)';
		exit;
	}
	if(!@file_exists($_PJ_root)) {
		print '<b>ERROR:</b> the directory \'' . $_PJ_root . '\' does not exist! (' . __FILE__ . ', line: 21)';
		exit;
	}
	if(!@is_dir($_PJ_root)) {
		print '<b>ERROR:</b> \'' . $_PJ_root . '\' is not a directory! (' . __FILE__ . ', line: 21)';
		exit;
	}

	if(!file_exists($_PJ_include_path . '/languages/' . $_PJ_language . '.inc.php')) {
		print "<b>ERROR:</b> The language file '" . $_PJ_include_path . '/languages/' . $_PJ_language . ".inc.php' does not exist";
		exit;
	}

	// the following two lines must be activated if the PEAR packages
	// are located within the timeeffect include path
	$include_path = ini_get('include_path');
	ini_set('include_path', $_PJ_include_path . '/pear/:./:' . $include_path);

	require_once ('PEAR.php');
	// let timeefect complain when any PEAR error occurs
	PEAR::setErrorHandling(PEAR_ERROR_TRIGGER, E_USER_WARNING);

	define('FPDF_FONTPATH', $_PJ_include_path . '/font/');

	$_PJ_css_path		= $_PJ_http_root	. "/css";
	$_PJ_icon_path		= $_PJ_http_root	. "/icons";
	$_PJ_icon_root		= $_PJ_root			. "/icons";
	$_PJ_image_path		= $_PJ_http_root	. "/images";
	$_PJ_logo_path		= $_PJ_http_root	. "/logos";
	$_PJ_logo_root		= $_PJ_root			. "/logos";

	$_PJ_project_table	= $_PJ_table_prefix	. 'project';
	$_PJ_customer_table	= $_PJ_table_prefix	. 'customer';
	$_PJ_effort_table	= $_PJ_table_prefix	. 'effort';
	$_PJ_rate_table		= $_PJ_table_prefix	. 'rate';
	$_PJ_user_table		= $_PJ_table_prefix	. 'user';
	$_PJ_auth_table		= $_PJ_table_prefix	. 'auth';
	$_PJ_group_table	= $_PJ_table_prefix	. 'group';
	$_PJ_gid_table		= $_PJ_table_prefix	. 'gids';

	$_PJ_form_method	= 'POST';
	$_PJ_password_dummy	= 'nOcHaNgEs';
	$_PJ_php_suffix		= 'php';

	if(empty($last)) {
		$_PJ_last = date("YmdHis", time()-2*86400);
	} else {
		$_PJ_last = date("YmdHis", time()-$last*86400);
	}

	$_PJ_budget_security_percentage		= 10;

	$_PJ_day_counts = array(
							31,
							29,
							31,
							30,
							31,
							30,
							31,
							31,
							30,
							31,
							30,
							31);

	include_once($_PJ_include_path . '/database.inc.php');
	include_once($_PJ_include_path . '/functions.inc.php');
	include_once($_PJ_include_path . '/data.inc.php');
	include_once($_PJ_include_path . '/project.inc.php');
	include_once($_PJ_include_path . '/effort.inc.php');
	include_once($_PJ_include_path . '/customer.inc.php');
	include_once($_PJ_include_path . '/statistics.inc.php');
	include_once($_PJ_include_path . '/rates.inc.php');
	include_once($_PJ_include_path . '/user.inc.php');
	include_once($_PJ_include_path . '/group.inc.php');
	include_once($_PJ_include_path . '/layout.inc.php');
	include_once($_PJ_include_path . '/languages/' . $_PJ_language . '.inc.php');
	include_once($_PJ_include_path . '/print.inc.php');

	include_once($_PJ_include_path . '/auth.inc.php');

	if(isset($exc)) {
		$expanded['cid'][$exc] = 1;
	} else if(isset($coc)) {
		if(isset($expanded)) unset($expanded['cid'][$coc]);
	} else if(isset($exp)) {
		$expanded['pid'][$exp] = 1;
	} else if(isset($cop)) {
		if(isset($expanded)) unset($expanded['pid'][$cop]);
	} else if(isset($exca)) {
 		$expanded['cid']['all'] = 1;
 	} else if(isset($coca)) {
 		if(isset($expanded)) unset($expanded['cid']);
 		if(isset($expanded)) unset($expanded['pid']);
	} else if(isset($expa)) {
 		$expanded['pid']['all'] = 1;
 	} else if(isset($copa)) {
 		if(isset($expanded)) unset($expanded['pid']);
 	} else if(isset($sic) && $sic == 1) {
 		$shown['ic'] = 1;
 	} else if(isset($sic) && $sic == 0) {
 		unset($shown['ic']);
 	} else if(isset($scp) and $scp == 1) {
 		$shown['cp'] = 1;
 	} else if(isset($scp) && $scp == 0) {
 		unset($shown['cp']);
 	} else if(isset($sbe) && $sbe == 1) {
 		$shown['be'] = 1;
 	} else if(isset($sbe) && $sbe == 0) {
 		unset($shown['be']);
 	}
