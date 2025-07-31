<!-- group/row.ihtml - START -->
					<TR>
						<TD COLSPAN="4"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/gray.gif" WIDTH="100%" HEIGHT="1" BORDER="0"></TD>
					</TR>
					<TR HEIGHT="25">
						<TD CLASS="list<?php if(isset($rowclass)) echo $rowclass; ?>">&nbsp;<IMG SRC="<?php if(!empty($GLOBALS['_PJ_icon_path'])) echo $GLOBALS['_PJ_icon_path'] ?>/group.gif" BORDER="0" WIDTH="16" HEIGHT="16" ALIGN="absmiddle">&nbsp;<A CLASS="list" HREF="<?= $GLOBALS['_PJ_group_script'] . "?edit=1&gid=" . $group->giveValue('id') ?>"><?= $group->giveValue('name') ?></A></TD>
						<TD CLASS="list<?php if(isset($rowclass)) echo $rowclass; ?>"><A CLASS="list" HREF="<?= $GLOBALS['_PJ_group_script'] . "?delete=1&gid=" . $group->giveValue('id') ?>"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_icon_path'])) echo $GLOBALS['_PJ_icon_path'] ?>/delete.gif" BORDER="0" ALT="<?php if(!empty($GLOBALS['_PJ_strings']['delete'])) echo $GLOBALS['_PJ_strings']['delete'] ?>" WIDTH="16" HEIGHT="16" ALIGN="absmiddle">&nbsp;<?php if(!empty($GLOBALS['_PJ_strings']['delete'])) echo $GLOBALS['_PJ_strings']['delete'] ?></A></TD>
					</TR>
<!-- group/row.ihtml - END -->
