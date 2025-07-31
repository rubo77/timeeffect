<!-- inventory/project/list.ihtml - START -->
<?php
	include($GLOBALS['_PJ_root'] . '/templates/inventory/project/options/list.ihtml.php');
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
if($cid && $customer->checkUserAccess('new')) {
?><A CLASS="list" HREF="<?= $GLOBALS['_PJ_projects_inventory_script'] . "?new=1&cid=$cid"; ?>"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_icon_path'])) echo $GLOBALS['_PJ_icon_path'] ?>/project.gif" BORDER="0" WIDTH="16" HEIGHT="16" ALT="<?php if(!empty($GLOBALS['_PJ_strings']['new_project'])) echo $GLOBALS['_PJ_strings']['new_project'] ?>" ALIGN="absmiddle">&nbsp;<?php if(!empty($GLOBALS['_PJ_strings']['new_project'])) echo $GLOBALS['_PJ_strings']['new_project'] ?></A><?php
} else { ?>&nbsp;<?php
} ?></TH>
							</TR>
						</TABLE></TD>
					</TR><TR>
						<TD>&nbsp;</TD>
					</TR><TR>
						<TD COLSPAN="3" BGCOLOR="#DDDDDD"><IMG src="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/abstand.gif" HEIGHT="1" WIDTH="1" BORDER="0"></TD>
					</TR><TR>
						<TD>&nbsp;</TD>
					</TR>
				</TABLE></TD>
			</TR><TR>
				<TD ALIGN="center"><TABLE BORDER="0" CELLPADDING="0" CELLSPACING="0" WIDTH="90%">
					<TR>
						<TH CLASS="list"><?php if(!empty($GLOBALS['_PJ_strings']['name'])) echo $GLOBALS['_PJ_strings']['name'] ?></TH>
						<TH CLASS="list" COLSPAN="10"><?php if(!empty($GLOBALS['_PJ_strings']['data'])) echo $GLOBALS['_PJ_strings']['data'] ?></TH>
					</TR>
<?php
	$rowclass = 1;
	while($projects->nextProject()) {
		$rowclass = !$rowclass;
		$project = $projects->giveProject();
		$row_class = !$row_class;
		if(isset($expanded) && isset($expanded['pid']['all']) && $expanded['pid']['all']) {
			$expanded['pid'][$project->giveValue('id')] = 1;
		}
		include("$_PJ_root/templates/inventory/project/row.ihtml.php");
	}
	if(isset($expanded)) unset($expanded['pid']['all']);
?>
					<TR>
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
						?><A CLASS="listFoot" HREF="<?= $GLOBALS['_PJ_projects_inventory_script'] . "?scp=1&cid=".@$cid.'&pid='.@$pid.'&eid='.@$eid.""; ?>"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_icon_path'])) echo $GLOBALS['_PJ_icon_path'] ?>/show-closed.gif" WIDTH="16" HEIGHT="16" BORDER="0" ALIGN="absmiddle">&nbsp;<?php if(!empty($GLOBALS['_PJ_strings']['show_closed_projects'])) echo $GLOBALS['_PJ_strings']['show_closed_projects'] ?></A><?php
} else {
						?><A CLASS="listFoot" HREF="<?= $GLOBALS['_PJ_projects_inventory_script'] . "?scp=0&cid=".@$cid.'&pid='.@$pid.'&eid='.@$eid.""; ?>"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_icon_path'])) echo $GLOBALS['_PJ_icon_path'] ?>/hide-closed.gif" WIDTH="16" HEIGHT="16" BORDER="0" ALIGN="absmiddle">&nbsp;<?php if(!empty($GLOBALS['_PJ_strings']['hide_closed_projects'])) echo $GLOBALS['_PJ_strings']['hide_closed_projects'] ?></A><?php
}
						?></TD>
						<TD ALIGN="right">
						<A CLASS="listFoot" HREF="<?= $GLOBALS['_PJ_projects_inventory_script'] . "?expa=1&cid=".@$cid.'&pid='.@$pid.'&eid='.@$eid.""; ?>"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_icon_path'])) echo $GLOBALS['_PJ_icon_path'] ?>/triangle-d.gif" WIDTH="16" HEIGHT="16" BORDER="0" ALIGN="absmiddle">&nbsp;<?php if(!empty($GLOBALS['_PJ_strings']['expand_all'])) echo $GLOBALS['_PJ_strings']['expand_all'] ?></A> |
						<A CLASS="listFoot" HREF="<?= $GLOBALS['_PJ_projects_inventory_script'] . "?copa=1&cid=".@$cid.'&pid='.@$pid.'&eid='.@$eid.""; ?>"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_icon_path'])) echo $GLOBALS['_PJ_icon_path'] ?>/triangle-l.gif" WIDTH="16" HEIGHT="16" BORDER="0" ALIGN="absmiddle">&nbsp;<?php if(!empty($GLOBALS['_PJ_strings']['collapse_all'])) echo $GLOBALS['_PJ_strings']['collapse_all'] ?>
						</TD>
					</TR>
				</TABLE></TD>
			</TR>
		</TABLE></TD>
	</TR>
</TABLE>
<!-- inventory/project/list.ihtml - END -->
