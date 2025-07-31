<!-- report/list.ihtml - START -->
<?php
	include($GLOBALS['_PJ_root'] . '/templates/report/options/list.ihtml.php');
?>
<SCRIPT LANGUAGE="JavaScript">
<!--
// -->
</SCRIPT>
<TABLE BORDER="0" CELLPADDING="0" CELLSPACING="0" WIDTH="100%">
	<TR>
		<TD CLASS="content">
		<TABLE BORDER="0" CELLPADDING="0" CELLSPACING="0" WIDTH="100%">
			<TR>
				<TD><TABLE BORDER="0" CELLPADDING="0" CELLSPACING="0" WIDTH="100%">
					<TR>
						<TD>&nbsp;</TD>
					</TR><TR>
						<TD ALIGN="center"><TABLE BORDER="0" CELLPADDING="0" CELLSPACING="0" WIDTH="90%">
							<TR>
								<TH CLASS="list"><?php
if($statistic->count()) {
$qs = '';
if(is_array($users)) {
	foreach($users as $a_user) {
		$qs .= '&users[]=' . $a_user;
	}
}
?>
<A CLASS="list" HREF="<?= $GLOBALS['_PJ_pdf_statistics_script'] . "?mode=$mode&cid=$cid&pid=$pid&syear=$syear&smonth=$smonth&sday=$sday&eyear=$eyear&emonth=$emonth&eday=$eday$qs" ?>"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_icon_path'])) echo $GLOBALS['_PJ_icon_path'] ?>/acrobat.gif" BORDER="0" ALT="<?php if(!empty($GLOBALS['_PJ_strings']['createpdf'])) echo $GLOBALS['_PJ_strings']['createpdf'] ?>" WIDTH="16" HEIGHT="16" ALIGN="absmiddle">&nbsp;<?php if(!empty($GLOBALS['_PJ_strings']['createpdf'])) echo $GLOBALS['_PJ_strings']['createpdf'] ?></A>
&nbsp;&nbsp;&nbsp;&nbsp;
<A CLASS="list" HREF="<?= $GLOBALS['_PJ_csv_statistics_script'] . "?mode=$mode&cid=$cid&pid=$pid&syear=$syear&smonth=$smonth&sday=$sday&eyear=$eyear&emonth=$emonth&eday=$eday$qs" ?>"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_icon_path'])) echo $GLOBALS['_PJ_icon_path'] ?>/csv.gif" BORDER="0" ALT="<?php if(!empty($GLOBALS['_PJ_strings']['createcsv'])) echo $GLOBALS['_PJ_strings']['createcsv'] ?>" WIDTH="16" HEIGHT="16" ALIGN="absmiddle">&nbsp;<?php if(!empty($GLOBALS['_PJ_strings']['createcsv'])) echo $GLOBALS['_PJ_strings']['createcsv'] ?></A>
<?php
} else { ?>&nbsp;<?php
} ?></TH>
							</TR>
						</TABLE></TD>
					</TR><TR>
						<TD>&nbsp;</TD>
					</TR><TR>
						<TD COLSPAN="3" BGCOLOR="#DDDDDD"><IMG src="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/abstand.gif" HEIGHT="1" WIDTH="1" BORDER="0"></TD>
					</TR>
				</TABLE></TD>
			</TR><TR>
				<TD ALIGN="center"><TABLE BORDER="0" CELLPADDING="0" CELLSPACING="0" WIDTH="90%">
					<TR>
<?php
if($_PJ_auth->checkPermission('accountant')) {
?>
						<TH CLASS="list"><?php if(!empty($GLOBALS['_PJ_strings']['bill'])) echo $GLOBALS['_PJ_strings']['bill'] ?></TH>
<?php
}
if(empty($cid)) { ?>
						<TH CLASS="list"><?php if(!empty($GLOBALS['_PJ_strings']['customer'])) echo $GLOBALS['_PJ_strings']['customer'] ?></TH>
<?php }
	if(empty($pid)) { ?>
				
						<TH CLASS="list"><?php if(!empty($GLOBALS['_PJ_strings']['project'])) echo $GLOBALS['_PJ_strings']['project'] ?></TH>
<?php } ?>
						<TH CLASS="list"><?php if(!empty($GLOBALS['_PJ_strings']['description'])) echo $GLOBALS['_PJ_strings']['description'] ?></TH>
						<TH CLASS="list"><?php if(!empty($GLOBALS['_PJ_strings']['agent'])) echo $GLOBALS['_PJ_strings']['agent'] ?></TH>
						<TH CLASS="listNumeric"><?php if(!empty($GLOBALS['_PJ_strings']['date'])) echo $GLOBALS['_PJ_strings']['date'] ?></TH>
						<TH CLASS="listNumeric"><?php if(!empty($GLOBALS['_PJ_strings']['from_to'])) echo $GLOBALS['_PJ_strings']['from_to'] ?></TH>
<?php
if(!empty($mode) and $mode == 'billed') {
?>
						<TH CLASS="listNumeric"><?php if(!empty($GLOBALS['_PJ_strings']['billed_at'])) echo $GLOBALS['_PJ_strings']['billed_at'] ?></TH>
<?php
}
?>
						<TH CLASS="listNumeric"><?php if(!empty($GLOBALS['_PJ_strings']['workinghours'])) echo $GLOBALS['_PJ_strings']['workinghours'] ?></TH>
						<TH CLASS="listNumeric"><?php if(!empty($GLOBALS['_PJ_strings']['costs'])) echo $GLOBALS['_PJ_strings']['costs'] ?></TH>
					</TR>
<FORM ACTION="<?php if(!empty($GLOBALS['_PJ_reports_script'])) echo $GLOBALS['_PJ_reports_script'] ?>" METHOD="<?php if(!empty($GLOBALS['_PJ_form_method'])) echo $GLOBALS['_PJ_form_method'] ?>">
<INPUT TYPE="hidden" NAME="report" VALUE="1">
<INPUT TYPE="hidden" NAME="cid" VALUE="<?php if(isset($cid)) echo $cid; ?>">
<INPUT TYPE="hidden" NAME="pid" VALUE="<?php if(isset($pid)) echo $pid; ?>">
<INPUT TYPE="hidden" NAME="syear" VALUE="<?php if(isset($syear)) echo $syear; ?>">
<INPUT TYPE="hidden" NAME="smonth" VALUE="<?php if(isset($smonth)) echo $smonth; ?>">
<INPUT TYPE="hidden" NAME="sday" VALUE="<?php if(isset($sday)) echo $sday; ?>">
<INPUT TYPE="hidden" NAME="eyear" VALUE="<?php if(isset($eyear)) echo $eyear; ?>">
<INPUT TYPE="hidden" NAME="emonth" VALUE="<?php if(isset($emonth)) echo $emonth; ?>">
<INPUT TYPE="hidden" NAME="eday" VALUE="<?php if(isset($eday)) echo $eday; ?>">
<INPUT TYPE="hidden" NAME="mode" VALUE="billed">
<?php
	$rowclass = 1;
	while($statistic->nextEffort()) {
		$rowclass = !$rowclass;
		$effort = $statistic->giveEffort();
		$row_class = !$row_class;
		include("$_PJ_root/templates/report/row.ihtml.php");
	}
?>
					<TR>
						<TD COLSPAN="10"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/gray.gif" WIDTH="100%" HEIGHT="1" BORDER="0"></TD>
					</TR><TR>
						<TD COLSPAN="10"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/abstand.gif" WIDTH="1" HEIGHT="20" BORDER="0"></TD>
					</TR><TR HEIGHT="25">
<?php
$colspan = 3;
if($_PJ_auth->checkPermission('accountant')) {
	$colspan++;
}
if(empty($cid)) {
	$colspan++;
}
if(empty($pid)) {
	$colspan++;
}
if($statistic->mode == 'billed') {
	$colspan++;
}
if($statistic->count()) {
?>
						<TD COLSPAN="<?php if(isset($colspan)) echo $colspan; ?>"></TD>
						<TD CLASS="listSum" ALIGN="right"><?php if(!empty($GLOBALS['_PJ_strings']['sum'])) echo $GLOBALS['_PJ_strings']['sum'] ?>:</TD>
						<TD CLASS="listSumNumeric"><?php if(!empty($sum_effort_hours)) print formatNumber($sum_effort_hours, true); ?></TD>
						<TD CLASS="listSumNumeric"><?php if(!empty($sum_effort_costs)) print formatNumber($sum_effort_costs, true) . '&nbsp;' . $GLOBALS['_PJ_currency']; ?></TD>
					</TR><TR>
<?php
}
?>
						<TD COLSPAN="10"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/gray.gif" WIDTH="100%" HEIGHT="1" BORDER="0"></TD>
					</TR><TR>
						<TD COLSPAN="10"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/abstand.gif" WIDTH="1" HEIGHT="20" BORDER="0"></TD>
					</TR>
				</TABLE>
<?php
if($statistic->count() && ($statistic->count() != $statistic->count(true)) && $_PJ_auth->checkPermission('accountant')) {
?>
				<INPUT TYPE="submit" VALUE="<?php if(!empty($GLOBALS['_PJ_strings']['bill'])) echo $GLOBALS['_PJ_strings']['bill'] ?> &gt;&gt;"></TD>
<?php
}
?>
			</TR>
<FORM>
		</TABLE></TD>
	</TR>
</TABLE>
<!-- report/list.ihtml - END -->
