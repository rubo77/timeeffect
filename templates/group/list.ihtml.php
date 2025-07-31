<!-- group/list.ihtml - START -->
<?php
	include($GLOBALS['_PJ_root'] . '/templates/group/options/list.ihtml.php');
?>
<TABLE	WIDTH="100%"
		BORDER="<?php print($_PJ_inner_frame_border); ?>"
		CELLPADDING="<?php print($_PJ_inner_frame_cellpadding); ?>"
		CELLSPACING="<?php print($_PJ_inner_frame_cellspacing ); ?>">
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
								<TH CLASS="list"><A CLASS="list" HREF="<?= $GLOBALS['_PJ_group_script'] . "?new=1"; ?>"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_icon_path'])) echo $GLOBALS['_PJ_icon_path'] ?>/group.gif" BORDER="0" WIDTH="16" HEIGHT="16" ALT="<?php if(!empty($GLOBALS['_PJ_strings']['new_group'])) echo $GLOBALS['_PJ_strings']['new_group'] ?>" ALIGN="absmiddle">&nbsp;<?php if(!empty($GLOBALS['_PJ_strings']['new_group'])) echo $GLOBALS['_PJ_strings']['new_group'] ?></A></TH>
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
					</TR>
<?php
	$rowclass = 1;
	while($groups->nextGroup()) {
		$rowclass = !$rowclass;
		$group = $groups->giveGroup();
		$row_class = !$row_class;
		include("$_PJ_root/templates/group/row.ihtml.php");
	}
?>
					<TR>
						<TD COLSPAN="10"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/gray.gif" WIDTH="100%" HEIGHT="1" BORDER="0"></TD>
					</TR>
				</TABLE></TD>
			</TR>
		</TABLE></TD>
	</TR>
</TABLE>
<!-- group/list.ihtml - END -->
