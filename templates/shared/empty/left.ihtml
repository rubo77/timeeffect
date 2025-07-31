<!-- shared/left.ihtml - START -->

<?php

	$max_length	= 17;

	$nav_width = 120;

?>

<TABLE CELLPADDING="0" CELLSPACING="0" BORDER="0" WIDTH="160" HEIGHT="100%">

	<TR>

		<TD VALIGN="top" CLASS="leftNavi"><TABLE CELLPADDING="3" CELLSPACING="0" BORDER="0">

			<TR>

				<TD CLASS="headFrame" COLSPAN="2" HEIGHT="150" VALIGN="top"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/logo_te_150.png" WIDTH="150" HEIGHT="19" BORDER="0" HSPACE="5" VSPACE="0"></TD>

			</TR><TR>

				<TD><IMG SRC="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/abstand.gif" WIDTH="1" HEIGHT="20" BORDER="0"></TD>

			</TR>
			<?php if (isset($_PJ_auth) && is_object($_PJ_auth)) { ?>
			<TR>

				<TD WIDTH="10" ROWSPAN="30"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/abstand.gif" WIDTH="10" HEIGHT="1" BORDER="0"></TD>

				<TD CLASS="leftHead"><?=$GLOBALS['_PJ_strings']['navigation']?></TD>

			</TR><TR>

				<TD><IMG SRC="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/gray.gif" WIDTH="<?php if(isset($nav_width)) echo $nav_width; ?>" HEIGHT="1" BORDER="0"></TD>

			</TR>

			</TR>
			<?php } ?>

		</TABLE></TD>

	</TR>
	<?php if (isset($_PJ_auth) && is_object($_PJ_auth)) { ?>
	<TR>

		<td VALIGN="bottom" CLASS="leftNavi" align="center"><br><br><a href="https://github.com/rubo77/timeeffect" target="_blank">TIMEEFFECT on GitHub</a><br><br></td>

	</TR>
	<?php } ?>

	<?php if (isset($_PJ_auth) && is_object($_PJ_auth) && $GLOBALS['_PJ_session_length']) { ?>
	<TR>

		<TD CLASS="leftNaviInfo">&nbsp;<?php if(!empty($GLOBALS['_PJ_strings']['session_timeout'])) echo $GLOBALS['_PJ_strings']['session_timeout'] ?>: <?php

// Check if session timeout is available (not in no_login scripts)
if (isset($GLOBALS['_PJ_session_timeout'])) {
	// Convert string to integer for modulo operation
	$timeout = (int)$GLOBALS['_PJ_session_timeout'];
	printf("%dm %02ds", (($timeout-($timeout%60))/60), ($timeout%60));
} else {
	echo "0m 00s";
}

		?></TD>

	</TR>
	<?php } ?>

</TABLE>

<!-- shared/left.ihtml - END -->

