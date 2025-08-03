<?php
	include_once($GLOBALS['_PJ_include_path'] . '/scripts.inc.php');
?>
<!-- list.ihtml - START -->
<HTML>
<HEAD>
<TITLE>TIMEEFFECT - Login</TITLE>
<!-- Mobile viewport and PWA meta tags for Phase 1 mobile optimization -->
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="theme-color" content="#007bff">
<!-- Original CSS -->
<LINK REL="stylesheet" HREF="<?= $GLOBALS['_PJ_css_path']; ?>/project.css" TYPE="text/css">
<!-- Responsive CSS for mobile optimization -->
<LINK REL="stylesheet" HREF="<?= $GLOBALS['_PJ_css_path']; ?>/responsive.css" TYPE="text/css">
<!-- Modern UI Design Framework -->
<LINK REL="stylesheet" HREF="<?= $GLOBALS['_PJ_css_path']; ?>/modern.css" TYPE="text/css">
<!-- Modern Layout System -->
<LINK REL="stylesheet" HREF="<?= $GLOBALS['_PJ_css_path']; ?>/layout.css" TYPE="text/css">
<SCRIPT LANGUAGE="Javascript1.2" SRC="<?= $GLOBALS['_PJ_http_root']; ?>/include/functions.js" type="text/javascript"></SCRIPT>
<SCRIPT language="Javascript1.2">
<!--
function sf(){
	if(document.forms[0].username.value == '')
		document.forms[0].username.focus();
	else
		document.forms[0].password.focus();
}

//-->
</SCRIPT>
</HEAD>

<BODY CLASS="login-page" onLoad='sf()'>
<TABLE CELLPADDING="0" CELLSPACING="0" BORDER="0" WIDTH="100%" HEIGHT="100%">
	<TR HEIGHT="50">
<!-- START - left navigation -->
		<TD ID="leftNavigation" WIDTH="160" ROWSPAN="2"><!-- shared/left.ihtml - START -->
		<TABLE CELLPADDING="0" CELLSPACING="0" BORDER="0" WIDTH="160" HEIGHT="100%">
			<TR>
				<TD VALIGN="top" CLASS="leftNavi"><TABLE CELLPADDING="3" CELLSPACING="0" BORDER="0">
					<TR>
						<TD CLASS="headFrame" COLSPAN="2" HEIGHT="150" VALIGN="top"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/logo_te_150.png" WIDTH="150" HEIGHT="19" BORDER="0" HSPACE="5" VSPACE="0"></TD>
					</TR><TR>
						<TD><IMG SRC="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/abstand.gif" WIDTH="1" HEIGHT="20" BORDER="0"></TD>
					</TR>
				</TABLE></TD>
			</TR>
		</TABLE>
		</TD>
<!-- END - left navigation -->
		<TD CLASS="headFrame"><TABLE CELLPADDING="0" CELLSPACING="0" BORDER="0" WIDTH="100%">
	<TR HEIGHT="50">
<!-- START - Main Options  -->
		<TD CLASS="mainOptionFrame" VALIGN="bottom">
		<TABLE CELLPADDING="0" CELLSPACING="0" BORDER="0" WIDTH="100%">
			<TR>
				<TD><TABLE CELLPADDING="0" CELLSPACING="0" BORDER="0">
					<TR>
						<TD CLASS="mainOptionS">&nbsp;&nbsp;<A CLASS="mainOptionS" HREF="<?php if(!empty($GLOBALS['PHP_SELF'])) echo $GLOBALS['PHP_SELF'] ?>"><?php if(!empty($GLOBALS['_PJ_strings']['authentication'])) echo $GLOBALS['_PJ_strings']['authentication'] ?></A></TD>
						<TD CLASS="mainOptionDivision"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/main-option-es.gif" WIDTH="10" HEIGHT="24" BORDER="0"></TD>
					</TR>
				</TABLE>
			</TR>
		</TABLE>
		</TD>
<!-- END - Main Options  -->
		<TD CLASS="topNav" VALIGN="bottom" ALIGN="right"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_icon_path'])) echo $GLOBALS['_PJ_icon_path'] ?>/help.gif" WIDTH="16" HEIGHT="16" BORDER="0" ALIGN="absmiddle"><A CLASS="topNav" HREF="JavaScript:openPopUp('<?php if(!empty($GLOBALS['_PJ_help_script'])) echo $GLOBALS['_PJ_help_script'] ?>', 600, 500)"><?php if(!empty($GLOBALS['_PJ_strings']['help'])) echo $GLOBALS['_PJ_strings']['help'] ?></A></TD>
	</TR>
</TABLE></TD>
	</TR><TR>
<TD VALIGN="top">
	<main>
		<!-- START - content -->
		<?php
		// Set form action for shared login form include
		$form_action = $GLOBALS['PHP_SELF'] ?? '';
		$form_class = 'loginForm';
		$container_style = 'max-width: 400px; margin: 2rem auto; padding: 2rem;';
		
		// Include the shared login form component
		include('login-form.ihtml.php');
		?>
		
		<!-- Version Information -->
		<div class="version" style="text-align: center; margin-top: 2rem; padding: 1rem; color: var(--text-secondary); font-size: 0.9rem;">
			TIMEEFFECT Version: <?php if(!empty($GLOBALS['_PJ_timeeffect_version'])) echo $GLOBALS['_PJ_timeeffect_version'] ?> 
			(Revision: <?php if(!empty($GLOBALS['_PJ_timeeffect_revision'])) echo $GLOBALS['_PJ_timeeffect_revision'] ?>, 
			<?= date($GLOBALS['_PJ_format_datetime'], strtotime($GLOBALS['_PJ_timeeffect_date'])) ?>)
		</div>
	</main>
</TD>
<!-- END - content -->
	</TR>
</TABLE>
<!-- Mobile JavaScript for Phase 1 mobile optimization -->
<SCRIPT SRC="<?= $GLOBALS['_PJ_http_root']; ?>/js/mobile.js" type="text/javascript"></SCRIPT>
</BODY>
</HTML>
<!-- list.ihtml - END -->
