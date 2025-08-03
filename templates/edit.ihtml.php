<!-- edit - START -->
<?php
// Include unified header
include_once(__DIR__ . '/shared/header.ihtml.php');
?>

<BODY>
<TABLE CELLPADDING="0" CELLSPACING="0" BORDER="0" WIDTH="100%" HEIGHT="100%">
	<TR VALIGN="top">
<!-- START - left navigation -->
		<TD ID="leftNavigation" WIDTH="160" ROWSPAN="2"><?php include("$_PJ_root/templates/shared/left.ihtml.php"); ?></TD>
<!-- END - left navigation -->
		<TD><TABLE CELLPADDING="0" CELLSPACING="0" BORDER="0" WIDTH="100%">
			<TR HEIGHT="50">
				<TD CLASS="headFrame"><?php include("$_PJ_root/templates/shared/top.ihtml.php"); ?></TD>
			</TR>
<!-- START - content -->
				<TD VALIGN="top">
<?php
	if($center_template != '') {
		include("$_PJ_root/templates/$center_template/form.ihtml.php");
	}
?>
				</TD>
<!-- END - content -->
			</TR>
		</TABLE></TD>
	</TR><TR>
		<TD class="version">&nbsp;TIMEEFFECT Version:&nbsp;<?php if(isset($_PJ_timeeffect_version)) echo $_PJ_timeeffect_version; ?> (Revision: <?php if(isset($_PJ_timeeffect_revision)) echo $_PJ_timeeffect_revision; ?>, <?= date($_PJ_format_datetime, strtotime($_PJ_timeeffect_date)) ?>)</td>
	</TR>
</TABLE>
<!-- Mobile JavaScript for Phase 1 mobile optimization -->
<SCRIPT SRC="<?php print $_PJ_http_root; ?>/js/mobile.js" type="text/javascript"></SCRIPT>
<!-- Theme Management JavaScript -->
<SCRIPT SRC="<?php print $_PJ_http_root; ?>/js/theme.js" type="text/javascript"></SCRIPT>
</BODY>
</HTML>
<!-- edit.ihtml - END -->
