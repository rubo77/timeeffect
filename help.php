<?php
	$no_login = 1;
    require_once(__DIR__ . "/../bootstrap.php"); // Modern PHP 8.4 compatibility
	include_once("include/config.inc.php");
?>
<HTML>
<HEAD>
<TITLE>TIMEEFFECT - <?php if(!empty($GLOBALS['_PJ_strings']['help'])) echo $GLOBALS['_PJ_strings']['help'] ?></TITLE>
<LINK REL="stylesheet" HREF="<?php print $_PJ_css_path; ?>/project.css" type="text/css">
</head>

<body>

<TABLE	WIDTH="100%"
		BORDER="<?php print($_PJ_inner_frame_border); ?>"
		CELLPADDING="<?php print($_PJ_inner_frame_cellpadding); ?>"
		CELLSPACING="<?php print($_PJ_inner_frame_cellspacing ); ?>">
	<TR>
		<TD CLASS="content" ALIGN="center">
		<TABLE BORDER="0" CELLPADDING="0" CELLSPACING="0" WIDTH="90%">
			<TR>
				<TD><IMG src="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/abstand.gif" HEIGHT="20" WIDTH="1" BORDER="0"></TD>
			</TR><TR>
				<TD><IMG src="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/abstand.gif" HEIGHT="20" WIDTH="1" BORDER="0"></TD>
			</TR><TR>
				<TD><b><?php if(!empty($GLOBALS['_PJ_strings']['not_implemented'])) echo $GLOBALS['_PJ_strings']['not_implemented'] ?>!</b></TD>
			</TR><TR>
				<TD><IMG src="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/abstand.gif" HEIGHT="20" WIDTH="1" BORDER="0">
				<HR SIZE="1"></TD>
			</TR><TR>
				<TD><?php if(!empty($GLOBALS['_PJ_strings']['translations'])) echo $GLOBALS['_PJ_strings']['translations'] ?></TD>
			</TR><TR>
				<TD><IMG src="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/abstand.gif" HEIGHT="40" WIDTH="1" BORDER="0"></TD>
			</TR><TR>
				<TD ALIGN="center"><A CLASS="note" HREF="JavaScript:self.close()"><IMG src="<?php if(!empty($GLOBALS['_PJ_icon_path'])) echo $GLOBALS['_PJ_icon_path'] ?>/close_window.gif" HEIGHT="16" WIDTH="16" BORDER="0" ALIGN="absmiddle">&nbsp;<?php if(!empty($GLOBALS['_PJ_strings']['close_window'])) echo $GLOBALS['_PJ_strings']['close_window'] ?></A></TD>
			</TR>
		</TABLE>
		</TD>
	</TR>
</TABLE>


</body>
</html>
