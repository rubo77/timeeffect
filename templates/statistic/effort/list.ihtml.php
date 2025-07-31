<!-- statistic/effort/list.ihtml - START -->
<?php
	include($GLOBALS['_PJ_root'] . '/templates/statistic/effort/options/list.ihtml.php');
?>
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
							<TR VALIGN="center">
								<TH CLASS="list"><?php
if($customer->count()) {
?>
<A CLASS="list" HREF="<?= $GLOBALS['_PJ_pdf_statistics_script'] . "?cid=$cid&pid=$pid" ?>"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_icon_path'])) echo $GLOBALS['_PJ_icon_path'] ?>/acrobat.gif" BORDER="0" ALT="<?php if(!empty($GLOBALS['_PJ_strings']['createpdf'])) echo $GLOBALS['_PJ_strings']['createpdf'] ?>" WIDTH="16" HEIGHT="16" ALIGN="absmiddle">&nbsp;<?php if(!empty($GLOBALS['_PJ_strings']['createpdf'])) echo $GLOBALS['_PJ_strings']['createpdf'] ?></A>
&nbsp;&nbsp;&nbsp;
<A CLASS="list" HREF="<?= $GLOBALS['_PJ_csv_statistics_script'] . "?cid=$cid&pid=$pid" ?>"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_icon_path'])) echo $GLOBALS['_PJ_icon_path'] ?>/csv.gif" BORDER="0" ALT="<?php if(!empty($GLOBALS['_PJ_strings']['createcsv'])) echo $GLOBALS['_PJ_strings']['createcsv'] ?>" WIDTH="16" HEIGHT="16" ALIGN="absmiddle">&nbsp;<?php if(!empty($GLOBALS['_PJ_strings']['createcsv'])) echo $GLOBALS['_PJ_strings']['createcsv'] ?></A>
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
						<TH CLASS="list"><?php if(!empty($GLOBALS['_PJ_strings']['name'])) echo $GLOBALS['_PJ_strings']['name'] ?></TH>
						<TH CLASS="listNumeric"><?php if(!empty($GLOBALS['_PJ_strings']['agent'])) echo $GLOBALS['_PJ_strings']['agent'] ?></TH>
						<TH CLASS="listNumeric"><?php if(!empty($GLOBALS['_PJ_strings']['date'])) echo $GLOBALS['_PJ_strings']['date'] ?></TH>
						<TH CLASS="listNumeric"><?php if(!empty($GLOBALS['_PJ_strings']['from_to'])) echo $GLOBALS['_PJ_strings']['from_to'] ?></TH>
						<TH CLASS="listNumeric"><?php if(!empty($GLOBALS['_PJ_strings']['workingdays'])) echo $GLOBALS['_PJ_strings']['workingdays'] ?> (h)</TH>
						<TH CLASS="listNumeric"><?php if(!empty($GLOBALS['_PJ_strings']['costs'])) echo $GLOBALS['_PJ_strings']['costs'] ?></TH>
					</TR>
<?php
	$rowclass = 1;
	while($efforts->nextEffort()) {
		$rowclass = !$rowclass;
		$effort = $efforts->giveEffort();
		$row_class = !$row_class;
		include("$_PJ_root/templates/statistic/effort/row.ihtml.php");
	}
?>
					<TR>
						<TD COLSPAN="10"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/gray.gif" WIDTH="100%" HEIGHT="1" BORDER="0"></TD>
					</TR><TR>
						<TD COLSPAN="10"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/abstand.gif" WIDTH="1" HEIGHT="20" BORDER="0"></TD>
					</TR><TR HEIGHT="25">
						<TD COLSPAN="3"></TD>
						<TD CLASS="listSum" ALIGN="right" valign="top"><?php if(!empty($GLOBALS['_PJ_strings']['sum'])) echo $GLOBALS['_PJ_strings']['sum'] ?>:</TD>
						<TD CLASS="listSumNumeric" valign="top"><?php if(!empty($sum_effort_days)) print formatNumber($sum_effort_days, true).'<br>('.formatNumber($sum_effort_days*8, true).' h)'; ?></TD>
						<TD CLASS="listSumNumeric" valign="top"><?php if(!empty($sum_effort_costs)) print formatNumber($sum_effort_costs, true) . '&nbsp;' . $GLOBALS['_PJ_currency']; ?></TD>
					</TR><TR>
						<TD COLSPAN="10"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/gray.gif" WIDTH="100%" HEIGHT="1" BORDER="0"></TD>
					</TR>
				</TABLE></TD>
			</TR><TR>
				<TD ALIGN="center"><TABLE BORDER="0" CELLPADDING="0" CELLSPACING="0" WIDTH="90%">
					<TR>
						<TD COLSPAN="2"><IMG src="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/abstand.gif" HEIGHT="3" WIDTH="1" BORDER="0"></TD>
					</TR><TR>
						<TD CLASS="listFoot" ALIGN="left"><?php
if(empty($shown['be'])) {
						?><A CLASS="listFoot" HREF="<?= $GLOBALS['_PJ_efforts_statistics_script'] . "?sbe=1&cid=".@$cid.'&pid='.@$pid.'&eid='.@$eid.""; ?>"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_icon_path'])) echo $GLOBALS['_PJ_icon_path'] ?>/show-closed.gif" WIDTH="16" HEIGHT="16" BORDER="0" ALIGN="absmiddle">&nbsp;<?php if(!empty($GLOBALS['_PJ_strings']['show_closed_efforts'])) echo $GLOBALS['_PJ_strings']['show_closed_efforts'] ?></A><?php
} else {
						?><A CLASS="listFoot" HREF="<?= $GLOBALS['_PJ_efforts_statistics_script'] . "?sbe=0&cid=".@$cid.'&pid='.@$pid.'&eid='.@$eid.""; ?>"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_icon_path'])) echo $GLOBALS['_PJ_icon_path'] ?>/hide-closed.gif" WIDTH="16" HEIGHT="16" BORDER="0" ALIGN="absmiddle">&nbsp;<?php if(!empty($GLOBALS['_PJ_strings']['hide_closed_efforts'])) echo $GLOBALS['_PJ_strings']['hide_closed_efforts'] ?></A><?php
}
						?></TD>
						<TD CLASS="listFoot" ALIGN="right">&nbsp;</TD>
					</TR>
				</TABLE></TD>
			</TR>
		</TABLE></TD>
	</TR>
</TABLE>
<!-- statistic/effort/list.ihtml - END -->
