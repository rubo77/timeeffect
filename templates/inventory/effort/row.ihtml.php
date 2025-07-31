<?php
	$agent = $_PJ_auth->giveUserById($effort->giveValue('user'));
?>
<!-- inventory/effort/row.ihtml - START -->
					<TR>
						<TD COLSPAN="<?php echo (empty($cid) ? 1 : 0) + (empty($pid) ? 1 : 0) + 6; ?>"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/gray.gif" WIDTH="100%" HEIGHT="1" BORDER="0"></TD>
					</TR>
					<TR HEIGHT="25">
						<TD CLASS="list<?php if(isset($rowclass)) echo $rowclass; ?>" WIDTH="30%">&nbsp;<?php if($effort->checkUserAccess('write')) { ?><A CLASS="list" HREF="<?= $GLOBALS['_PJ_efforts_inventory_script'] . "?edit=1&cid=$cid&pid=$pid&eid=" . $effort->giveValue('id') ?>"><?php } ?><IMG SRC="<?php if(!empty($GLOBALS['_PJ_icon_path'])) echo $GLOBALS['_PJ_icon_path'] ?>/effort<?php if(!($effort->giveValue('billed') == '' || $effort->giveValue('billed') == '0000-00-00')) print 'b' ?>.gif" BORDER="0" WIDTH="16" HEIGHT="16" ALIGN="absmiddle">&nbsp;<?= $effort->giveValue('description') ?></A></TD>
						<?php if(empty($cid)) { ?><TD CLASS="list<?php if(isset($rowclass)) echo $rowclass; ?>" style="padding-right: 5px;">&nbsp;<?= $effort->giveValue('customer_name') ? htmlspecialchars($effort->giveValue('customer_name')) : '-' ?></TD><?php } ?>
						<?php if(empty($pid)) { ?><TD CLASS="list<?php if(isset($rowclass)) echo $rowclass; ?>" style="padding-right: 5px;">&nbsp;<?= $effort->giveValue('project_name') ? htmlspecialchars($effort->giveValue('project_name')) : ($effort->giveValue('project_id') == '0' ? '-' : 'ID: ' . $effort->giveValue('project_id')) ?></TD><?php } ?>
						<TD CLASS="list<?php if(isset($rowclass)) echo $rowclass; ?>">&nbsp;<?= $agent['firstname'] . ' ' . $agent['lastname']; ?></TD>
						<TD CLASS="list<?php if(isset($rowclass)) echo $rowclass; ?>" WIDTH="150">&nbsp;<?= $effort->formatTime($effort->giveValue('date'), "d.m.Y") ?>&nbsp;<?= $effort->formatTime($effort->giveValue('begin'), "H:i"); ?></TD>
						<TD CLASS="listDetailNumeric<?php if(isset($rowclass)) echo $rowclass; ?>">&nbsp;<?php
#echo formatNumber(floor($effort->giveValue('hours')), true)
echo (floor($effort->giveValue('hours')))."h, ".sprintf("%2.0f",round( ($effort->giveValue('hours')-floor($effort->giveValue('hours') ))*60))." min"; ?>&nbsp;&nbsp;
(<?= $effort->formatTime($effort->giveValue('begin'), "H:i"); ?> - <?= 
$effort->formatTime($effort->giveValue('end'), "H:i"); ?>)</TD>
						<TD CLASS="listDetailNumeric<?php if(isset($rowclass)) echo $rowclass; ?>" WIDTH="200">&nbsp;<?= formatNumber($effort->giveValue('costs'), true) . '&nbsp;' . $GLOBALS['_PJ_currency'] ?></TD>
						<TD CLASS="list<?php if(isset($rowclass)) echo $rowclass; ?>"><?php
if($effort->checkUserAccess('write') && ($effort->giveValue('user') == $_PJ_auth->giveValue('id')) && ($effort->giveValue('billed') == '') && ($effort->giveValue('hours') == 0)) {
	?><A CLASS="list" HREF="<?= $GLOBALS['_PJ_efforts_inventory_script'] . "?stop=1&eid=" . $effort->giveValue('id') ?>"><?php if(!empty($GLOBALS['_PJ_strings']['stop'])) echo $GLOBALS['_PJ_strings']['stop'] ?>&nbsp;🛑</A><?php
} else if($project && $project->checkUserAccess('new') && ($effort->giveValue('user') == $_PJ_auth->giveValue('id'))) {
	// Check if another effort with same description is already running (hours = 0)
	$db_check = new Database();
	$db_check->connect();
	$safe_description = DatabaseSecurity::escapeString($effort->giveValue('description'), $db_check->Link_ID);
	$safe_user_id = DatabaseSecurity::escapeString($_PJ_auth->giveValue('id'), $db_check->Link_ID);
	$check_query = "SELECT COUNT(*) as count FROM " . $GLOBALS['_PJ_effort_table'] . " WHERE description = '$safe_description' AND user = '$safe_user_id' AND begin = end AND id != " . intval($effort->giveValue('id'));
	$db_check->query($check_query);
	$db_check->next_record();
	$running_count = $db_check->f('count');
	
	if($running_count == 0) {
		?><A CLASS="list continue-button" HREF="<?= $GLOBALS['_PJ_efforts_inventory_script'] . "?cont=1&eid=" . $effort->giveValue('id') ?>"><?php if(!empty($GLOBALS['_PJ_strings']['continue'])) echo $GLOBALS['_PJ_strings']['continue'] ?>&nbsp;<span class="play-icon">▶</span></A><?php
	}
} ?>&nbsp;</TD>
						<TD CLASS="list<?php if(isset($rowclass)) echo $rowclass; ?>"><?php if($effort->checkUserAccess('write') && ($effort->giveValue('billed') == '') && ($_PJ_auth->checkPermission('accountant') || $GLOBALS['_PJ_agents_allow_delete'])) { ?><A CLASS="list" HREF="<?= $GLOBALS['_PJ_efforts_inventory_script'] . "?delete=1&cid=$cid&pid=$pid&eid=" . $effort->giveValue('id') ?>"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_icon_path'])) echo $GLOBALS['_PJ_icon_path'] ?>/delete.gif" BORDER="0" ALT="<?php if(!empty($GLOBALS['_PJ_strings']['delete'])) echo $GLOBALS['_PJ_strings']['delete'] ?>" WIDTH="16" HEIGHT="16" ALIGN="absmiddle">&nbsp;<?php if(!empty($GLOBALS['_PJ_strings']['delete'])) echo $GLOBALS['_PJ_strings']['delete'] ?></A><?php } ?>&nbsp;</TD>
					</TR>
<?php
if($effort->giveValue('note') != '') {
?>
					<TR>
						<TD CLASS="list<?php if(isset($rowclass)) echo $rowclass; ?>"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/abstand.gif" WIDTH="100%" HEIGHT="1" BORDER="0"></TD>
						<TD COLSPAN="<?php echo (empty($cid) ? 1 : 0) + (empty($pid) ? 1 : 0) + 5; ?>"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/light-gray.gif" WIDTH="100%" HEIGHT="1" BORDER="0"></TD>
					</TR><TR>
						<TD CLASS="list<?php if(isset($rowclass)) echo $rowclass; ?>"></TD>
						<TD COLSPAN="<?php echo (empty($cid) ? 1 : 0) + (empty($pid) ? 1 : 0) + 5; ?>" CLASS="list<?php if(isset($rowclass)) echo $rowclass; ?>"><TABLE BORDER="0" CELLPADDING="0" CELLSPACING="0" WIDTH="100%">
							<TR>
								<TD CLASS="listDetail<?php if(isset($rowclass)) echo $rowclass; ?>" WIDTH="1%" valign="top"><?php if(!empty($GLOBALS['_PJ_strings']['note'])) echo $GLOBALS['_PJ_strings']['note'] ?>:</TD>
								<TD CLASS="listDetail<?php if(isset($rowclass)) echo $rowclass; ?>"><?= $effort->giveValue('note') ?></TD>
							</TR>
						</TABLE></TD>
					</TR>
<?php
}
?>
<!-- inventory/effort/row.ihtml - END -->
