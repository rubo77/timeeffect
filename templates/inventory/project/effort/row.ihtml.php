<?php
	$agent = $_PJ_auth->giveUserById($effort->giveValue('user'));
?>
<!-- inventory/project/effort/row.ihtml - START -->
	<TR>
		<TD CLASS="list<?php if(isset($rowclass)) echo $rowclass; ?>"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/abstand.gif" WIDTH="100%" HEIGHT="1" BORDER="0"></TD>
		<TD COLSPAN="10"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/light-gray.gif" WIDTH="100%" HEIGHT="1" BORDER="0"></TD>
	</TR><TR HEIGHT="25">
		<TD CLASS="list<?php if(isset($rowclass)) echo $rowclass; ?>"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/abstand.gif" BORDER="0" WIDTH="16" HEIGHT="16" ALIGN="absmiddle">&nbsp;<?php if($effort->checkUserAccess('write')) { ?><A CLASS="list" HREF="<?= $GLOBALS['_PJ_efforts_inventory_script'] . "?edit=1&eid=" . $effort->giveValue('id') ?>"><?php } ?><IMG SRC="<?php if(!empty($GLOBALS['_PJ_icon_path'])) echo $GLOBALS['_PJ_icon_path'] ?>/effort<?php if(!($effort->giveValue('billed') == '' || $effort->giveValue('billed') == '0000-00-00')) print 'b' ?>.gif" BORDER="0" WIDTH="16" HEIGHT="16" ALIGN="absmiddle">&nbsp;<?= $effort->giveValue('description') ?></A> <span class="agentInfo">(<?php if(!empty($agent['username'])) echo $agent['username'] ?>)</span></TD>
		<TD CLASS="list<?php if(isset($rowclass)) echo $rowclass; ?>">&nbsp;</TD>
		<TD CLASS="list<?php if(isset($rowclass)) echo $rowclass; ?>"><?php
if($effort->giveValue('note') != '') { ?>
<A CLASS="list" HREF="JavaScript:openPopUp('<?= $GLOBALS['_PJ_efforts_inventory_script'] . "?detail=1&eid=" . $effort->giveValue('id') ?>', 400, 400)"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_icon_path'])) echo $GLOBALS['_PJ_icon_path'] ?>/note.gif" BORDER="0" WIDTH="16" HEIGHT="16" ALIGN="absmiddle">&nbsp;<?php if(!empty($GLOBALS['_PJ_strings']['note'])) echo $GLOBALS['_PJ_strings']['note'] ?></A>
<?php } ?>&nbsp;</TD>
		<TD CLASS="list<?php if(isset($rowclass)) echo $rowclass; ?>"><?= formatNumber($effort->giveValue('costs'), true) . "&nbsp;" . $GLOBALS['_PJ_currency'] ?></TD>
		<TD CLASS="list<?php if(isset($rowclass)) echo $rowclass; ?>"><?php
if($effort->checkUserAccess('write') && ($effort->giveValue('user') == $_PJ_auth->giveValue('id')) && ($effort->giveValue('billed') == '') && ($effort->giveValue('hours') == 0)) {
?><A CLASS="list" HREF="<?= $GLOBALS['_PJ_efforts_inventory_script'] . "?stop=1&eid=" . $effort->giveValue('id') ?>"><?php if(!empty($GLOBALS['_PJ_strings']['stop'])) echo $GLOBALS['_PJ_strings']['stop'] ?>&nbsp;🛑</A><?php
} else if($project->checkUserAccess('new') && ($effort->giveValue('user') == $_PJ_auth->giveValue('id'))) { ?><A CLASS="list continue-button" HREF="<?= $GLOBALS['_PJ_efforts_inventory_script'] . "?cont=1&eid=" . $effort->giveValue('id') ?>"><?php if(!empty($GLOBALS['_PJ_strings']['continue'])) echo $GLOBALS['_PJ_strings']['continue'] ?>&nbsp;<span class="play-icon">▶</span></A><?php
} ?></TD>
		<TD CLASS="list<?php if(isset($rowclass)) echo $rowclass; ?>"><?php if($effort->checkUserAccess('write') && ($effort->giveValue('billed') == '') && ($_PJ_auth->checkPermission('accountant') || $GLOBALS['_PJ_agents_allow_delete'])) { ?><A CLASS="list" HREF="<?= $GLOBALS['_PJ_efforts_inventory_script'] . "?delete=1&eid=" . $effort->giveValue('id') ?>"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_icon_path'])) echo $GLOBALS['_PJ_icon_path'] ?>/delete.gif" BORDER="0" ALT="<?php if(!empty($GLOBALS['_PJ_strings']['delete'])) echo $GLOBALS['_PJ_strings']['delete'] ?>" WIDTH="16" HEIGHT="16" ALIGN="absmiddle">&nbsp;<?php if(!empty($GLOBALS['_PJ_strings']['delete'])) echo $GLOBALS['_PJ_strings']['delete'] ?></A><?php } ?></TD>
	</TR>
<!-- inventory/project/effort/row.ihtml - END -->
