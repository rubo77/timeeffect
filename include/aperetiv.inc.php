<?php
error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 1);
$_PJ_budget_security_percentage		= 10;

// Initialize variables from request data to ensure strict mode compatibility
$lang = $_REQUEST['lang'] ?? null;
$last = $_REQUEST['last'] ?? null;
$exc = $_REQUEST['exc'] ?? null;
$coc = $_REQUEST['coc'] ?? null;
$exp = $_REQUEST['exp'] ?? null;
$cop = $_REQUEST['cop'] ?? null;
$exca = $_REQUEST['exca'] ?? null;
$coca = $_REQUEST['coca'] ?? null;
$expa = $_REQUEST['expa'] ?? null;
$copa = $_REQUEST['copa'] ?? null;
$sic = $_REQUEST['sic'] ?? null;
$scp = $_REQUEST['scp'] ?? null;
$sbe = $_REQUEST['sbe'] ?? null;

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

$_PJ_day_counts = array(31, 29, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);

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

// Set $logout from request for global logout handling
$logout = isset($_REQUEST['logout']) ? $_REQUEST['logout'] : null;
include_once($_PJ_include_path . '/auth.inc.php');

// Initialize expanded array to avoid strict mode warnings
$expanded = $_SESSION['expanded'] ?? [];
$shown = $_SESSION['shown'] ?? [];

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

// Fix: Default expand all when no specific arguments are provided
if(!isset($exc) && !isset($coc) && !isset($exp) && !isset($cop) && !isset($exca) && !isset($coca) && !isset($expa) && !isset($copa) && empty($_GET['cid']) && empty($_GET['pid']) && empty($_GET['eid'])) {
	$expanded['cid']['all'] = 1;
	$expanded['pid']['all'] = 1;
}
