<?php
	$no_login = 1;
	include_once("include/aperetiv.inc.php");
?>
<HTML>
<HEAD>
<TITLE>TIMEEFFECT - <?= $GLOBALS['_PJ_strings']['help'] ?></TITLE>
<LINK REL="stylesheet" HREF="<?php print $_PJ_css_path; ?>/project.css" type="text/css">
</head>

<body>

<TABLE CELLPADDING="0" CELLSPACING="0" BORDER="0" WIDTH="100%" HEIGHT="100%">
	<TR>
		<TD VALIGN="top"><br>&nbsp;<b><?= $GLOBALS['_PJ_strings']['not_implemented'] ?>!</b></TD>
	</TR><TR>
		<TD>&nbsp;<a href="javascript:self.close()"><b><?= $GLOBALS['_PJ_strings']['close_window'] ?></b></a></TD>
	</TR><TR>
		<TD class="version">&nbsp;TIMEEFFECT Version:&nbsp;<?= $_PJ_timeeffect_version ?></td>
	</TR>
</TABLE>
</body>
</html>
