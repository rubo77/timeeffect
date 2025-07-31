<!-- statistic/project/list.ihtml - START -->
<?php
	include($GLOBALS['_PJ_root'] . '/templates/statistic/project/options/list.ihtml.php');
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
<A CLASS="list" HREF="<?= $GLOBALS['_PJ_pdf_statistics_script'] . "?cid=$cid" ?>"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_icon_path'])) echo $GLOBALS['_PJ_icon_path'] ?>/acrobat.gif" BORDER="0" ALT="<?php if(!empty($GLOBALS['_PJ_strings']['createpdf'])) echo $GLOBALS['_PJ_strings']['createpdf'] ?>" WIDTH="16" HEIGHT="16" ALIGN="absmiddle">&nbsp;<?php if(!empty($GLOBALS['_PJ_strings']['createpdf'])) echo $GLOBALS['_PJ_strings']['createpdf'] ?></A>
&nbsp;&nbsp;&nbsp;
<A CLASS="list" HREF="<?= $GLOBALS['_PJ_csv_statistics_script'] . "?cid=$cid" ?>"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_icon_path'])) echo $GLOBALS['_PJ_icon_path'] ?>/csv.gif" BORDER="0" ALT="<?php if(!empty($GLOBALS['_PJ_strings']['createcsv'])) echo $GLOBALS['_PJ_strings']['createcsv'] ?>" WIDTH="16" HEIGHT="16" ALIGN="absmiddle">&nbsp;<?php if(!empty($GLOBALS['_PJ_strings']['createcsv'])) echo $GLOBALS['_PJ_strings']['createcsv'] ?></A>
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
						<TH CLASS="listNumeric"><?php if(!empty($GLOBALS['_PJ_strings']['workingdays'])) echo $GLOBALS['_PJ_strings']['workingdays'] ?></TH>
						<TH CLASS="listNumeric"><?php if(!empty($GLOBALS['_PJ_strings']['costs'])) echo $GLOBALS['_PJ_strings']['costs'] ?></TH>
						<TH CLASS="listNumeric"><?php if(!empty($GLOBALS['_PJ_strings']['fullbudget'])) echo $GLOBALS['_PJ_strings']['fullbudget'] ?></TH>
						<TH CLASS="listNumeric"><?php if(!empty($GLOBALS['_PJ_strings']['remainingbudget'])) echo $GLOBALS['_PJ_strings']['remainingbudget'] ?></TH>
					</TR>
<?php
	$rowclass = 1;
	while($projects->nextProject()) {
		$rowclass = !$rowclass;
		$project = $projects->giveProject();
		$row_class = !$row_class;
		if(isset($expanded) && isset($expanded['pid']['all'])) {
			$expanded['pid'][$project->giveValue('id')] = 1;
		}
		include("$_PJ_root/templates/statistic/project/row.ihtml.php");
	}
	if(isset($expanded)) unset($expanded['pid']['all']);
?>
					<TR>
						<TD COLSPAN="10"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/gray.gif" WIDTH="100%" HEIGHT="1" BORDER="0"></TD>
					</TR><TR>
						<TD COLSPAN="10"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/abstand.gif" WIDTH="1" HEIGHT="20" BORDER="0"></TD>
					</TR><TR HEIGHT="25">
						<TD>&nbsp;</TD>
						<TD CLASS="listSum" ALIGN="right"><?php if(!empty($GLOBALS['_PJ_strings']['sum'])) echo $GLOBALS['_PJ_strings']['sum'] ?>:</TD>
						<TD CLASS="listSumNumeric"><?php if(!empty($sum_project_days)) print formatNumber($sum_project_days, true); ?></TD>
						<TD CLASS="listSumNumeric"><?php if(!empty($sum_project_costs)) print formatNumber($sum_project_costs, true) . '&nbsp;' . $GLOBALS['_PJ_currency']; ?></TD>
						<TD CLASS="listSumNumeric"><?php if(!empty($sum_project_full_budget)) print formatNumber($sum_project_full_budget, true) . '&nbsp;' . $GLOBALS['_PJ_currency']; ?></TD>
						<TD CLASS="listSumNumeric"><?php if($sum_project_full_budget && $sum_project_remaining_budget) print formatNumber($sum_project_remaining_budget, true) . '&nbsp;' . $GLOBALS['_PJ_currency']; ?></TD>
					</TR><TR>
						<TD COLSPAN="10"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/gray.gif" WIDTH="100%" HEIGHT="1" BORDER="0"></TD>
					</TR>
				</TABLE></TD>
			</TR><TR>
				<TD ALIGN="center"><TABLE BORDER="0" CELLPADDING="0" CELLSPACING="0" WIDTH="90%">
					<TR>
						<TD COLSPAN="2"><IMG src="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/abstand.gif" HEIGHT="3" WIDTH="1" BORDER="0"></TD>
					</TR><TR>
						<TD ALIGN="left"><?php
if(empty($shown['cp'])) {
						?><A CLASS="listFoot" HREF="<?= $GLOBALS['_PJ_projects_statistics_script'] . "?scp=1&cid=".@$cid.'&pid='.@$pid.'&eid='.@$eid.""; ?>"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_icon_path'])) echo $GLOBALS['_PJ_icon_path'] ?>/show-closed.gif" WIDTH="16" HEIGHT="16" BORDER="0" ALIGN="absmiddle">&nbsp;<?php if(!empty($GLOBALS['_PJ_strings']['show_closed_projects'])) echo $GLOBALS['_PJ_strings']['show_closed_projects'] ?></A><?php
} else {
						?><A CLASS="listFoot" HREF="<?= $GLOBALS['_PJ_projects_statistics_script'] . "?scp=0&cid=".@$cid.'&pid='.@$pid.'&eid='.@$eid.""; ?>"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_icon_path'])) echo $GLOBALS['_PJ_icon_path'] ?>/hide-closed.gif" WIDTH="16" HEIGHT="16" BORDER="0" ALIGN="absmiddle">&nbsp;<?php if(!empty($GLOBALS['_PJ_strings']['hide_closed_projects'])) echo $GLOBALS['_PJ_strings']['hide_closed_projects'] ?></A><?php
}
						?>
						
						</TD>
						<TD ALIGN="right">
						<A CLASS="listFoot" HREF="<?= $GLOBALS['_PJ_projects_statistics_script'] . "?expa=1&cid=".@$cid.'&pid='.@$pid.'&eid='.@$eid.""; ?>"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_icon_path'])) echo $GLOBALS['_PJ_icon_path'] ?>/triangle-d.gif" WIDTH="16" HEIGHT="16" BORDER="0" ALIGN="absmiddle">&nbsp;<?php if(!empty($GLOBALS['_PJ_strings']['expand_all'])) echo $GLOBALS['_PJ_strings']['expand_all'] ?></A> |
						<A CLASS="listFoot" HREF="<?= $GLOBALS['_PJ_projects_statistics_script'] . "?copa=1&cid=".@$cid.'&pid='.@$pid.'&eid='.@$eid.""; ?>"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_icon_path'])) echo $GLOBALS['_PJ_icon_path'] ?>/triangle-l.gif" WIDTH="16" HEIGHT="16" BORDER="0" ALIGN="absmiddle">&nbsp;<?php if(!empty($GLOBALS['_PJ_strings']['collapse_all'])) echo $GLOBALS['_PJ_strings']['collapse_all'] ?>
						</TD>
					</TR>
				</TABLE></TD>
			</TR>
		</TABLE></TD>
	</TR>
</TABLE>
<!-- statistic/project/list.ihtml - END -->
