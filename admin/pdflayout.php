<?php
    require_once(__DIR__ . "/../bootstrap.php"); // Modern PHP 8.4 compatibility
	include_once("../include/config.inc.php");
	include_once($_PJ_include_path . '/scripts.inc.php');

	// Initialize variables from request
	$altered = $_REQUEST['altered'] ?? null;
	$pdflayout = '';

	$center_template	= "admin/pdflayout";
	$center_title		= $GLOBALS['_PJ_strings']['admin'] . ' ' . $GLOBALS['_PJ_strings']['pdf_layout'];

	if(!$_PJ_auth->checkPermission('admin')) {
		$error_message		= $GLOBALS['_PJ_strings']['error_access'];
		include("$_PJ_root/templates/error.ihtml");
		include_once("$_PJ_include_path/degestiv.inc.php");
		exit;
	}
	if(isset($altered)) {
		foreach($_POST as $pname => $pvalue) {
			if(substr($pname, 0, 4) != 'pdf_') {
				continue;
			}
			if(is_array($pvalue)) {
				foreach($pvalue as $ppname => $ppvalue) {
					$pdflayout .= "\t\$_PJ_" . $pname . "['" . $ppname . "'] = '$ppvalue';" . "\n";
				}
			} else {
				$pvalue = str_replace('\"', '"', $pvalue);
				$pdflayout .= "\t\$_PJ_$pname = '$pvalue';" . "\n";
			}
		}
		if(!($pfile = @fopen($_PJ_include_path . '/pdflayout.inc.php', 'w'))) {
			$error_message		= "$_PJ_include_path/pdflayout.inc.php:<br>" . $GLOBALS['_PJ_strings']['error_fopen'];
			include("$_PJ_root/templates/error.ihtml");
			include_once("$_PJ_include_path/degestiv.inc.php");
			exit;
		}
		fwrite($pfile, '<' . "?php\n$pdflayout\n\t\$_PJ_pdf_font_face = 'Helvetica';\n?" . '>');
		fclose($pfile);
		include($_PJ_include_path . '/pdflayout.inc.php');
	}
	include("$_PJ_root/templates/list.ihtml");
	include_once("$_PJ_include_path/degestiv.inc.php");
?>