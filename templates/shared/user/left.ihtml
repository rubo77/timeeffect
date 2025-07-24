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

			</TR><TR>

				<TD WIDTH="10" ROWSPAN="30"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/abstand.gif" WIDTH="10" HEIGHT="1" BORDER="0"></TD>

				<TD CLASS="leftHead"><?=$GLOBALS['_PJ_strings']['navigation']?></TD>

			</TR><TR>

				<TD><IMG SRC="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/gray.gif" WIDTH="<?php if(isset($nav_width)) echo $nav_width; ?>" HEIGHT="1" BORDER="0"></TD>

			</TR><TR>

				<TD><A CLASS="left" HREF="<?php if(!empty($GLOBALS['_PJ_customer_statistics_script'])) echo $GLOBALS['_PJ_customer_statistics_script'] ?>"><?php if(!empty($GLOBALS['_PJ_strings']['customers'])) echo $GLOBALS['_PJ_strings']['customers'] ?></A>&nbsp;<IMG SRC="<?php if(!empty($GLOBALS['_PJ_icon_path'])) echo $GLOBALS['_PJ_icon_path'] ?>/arrow-top.gif" WIDTH="11" HEIGHT="7" BORDER="0"></TD>

			</TR><TR>

				<TD><?php

		if(($SCRIPT_NAME == $GLOBALS['_PJ_projects_statistics_script']) || ($SCRIPT_NAME == $GLOBALS['_PJ_efforts_statistics_script'])) {

		?><A CLASS="left" HREF="<?php if(!empty($GLOBALS['_PJ_projects_statistics_script'])) echo $GLOBALS['_PJ_projects_statistics_script'] ?>"><?php if(!empty($GLOBALS['_PJ_strings']['projects'])) echo $GLOBALS['_PJ_strings']['projects'] ?></A>&nbsp;<IMG SRC="<?php if(!empty($GLOBALS['_PJ_icon_path'])) echo $GLOBALS['_PJ_icon_path'] ?>/arrow-up.gif" WIDTH="11" HEIGHT="7" BORDER="0"><?php

		} ?>&nbsp;</TD>

			</TR><TR>

				<TD><?php

		if($SCRIPT_NAME == $GLOBALS['_PJ_efforts_statistics_script']) {

		?><A CLASS="left" HREF="<?php if(!empty($GLOBALS['_PJ_efforts_statistics_script'])) echo $GLOBALS['_PJ_efforts_statistics_script'] ?>"><?php if(!empty($GLOBALS['_PJ_strings']['efforts'])) echo $GLOBALS['_PJ_strings']['efforts'] ?></A>&nbsp;<IMG SRC="<?php if(!empty($GLOBALS['_PJ_icon_path'])) echo $GLOBALS['_PJ_icon_path'] ?>/arrow-up.gif" WIDTH="11" HEIGHT="7" BORDER="0"><?php

		} ?>&nbsp;</TD>

			</TR><TR>

				<TD><IMG SRC="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/abstand.gif" WIDTH="1" HEIGHT="30" BORDER="0"></TD>

			</TR><TR>

				<TD CLASS="leftHead"><?php if(!empty($GLOBALS['_PJ_strings']['chosen_elements'])) echo $GLOBALS['_PJ_strings']['chosen_elements'] ?></TD>

			</TR><TR>

				<TD><IMG SRC="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/gray.gif" WIDTH="<?php if(isset($nav_width)) echo $nav_width; ?>" HEIGHT="1" BORDER="0"></TD>

			</TR><TR>

				<TD><?php

		if(isset($customer) && is_object($customer) && $customer->giveValue('id')) {

		?><IMG SRC="<?php if(!empty($GLOBALS['_PJ_icon_path'])) echo $GLOBALS['_PJ_icon_path'] ?>/customer.gif" WIDTH="16" HEIGHT="16" BORDER="0" ALIGN="absmiddle">&nbsp;<A CLASS="left" HREF="<?= $GLOBALS['_PJ_projects_statistics_script'] . "?list=1&cid=".@$cid.'&pid='.@$pid.'&eid='.@$eid."" ?>"><?php

		print $customer->giveValue('customer_name');

		?></A><?php

		} ?>&nbsp;</TD>

			</TR><TR>

				<TD><?php

		if(isset($project) && is_object($project) && $project->giveValue('id')) {

		?><IMG SRC="<?php if(!empty($GLOBALS['_PJ_icon_path'])) echo $GLOBALS['_PJ_icon_path'] ?>/project.gif" WIDTH="16" HEIGHT="16" BORDER="0" ALIGN="absmiddle">&nbsp;<A CLASS="left" HREF="<?= $GLOBALS['_PJ_efforts_statistics_script'] . "?list=1&cid=".@$cid.'&pid='.@$pid.'&eid='.@$eid."" ?>"><?php

		print $project->giveValue('project_name');

		?></A><?php

		} ?>&nbsp;</TD>

			</TR><TR>

				<TD><?php

		if(isset($effort) && is_object($effort) && $effort->giveValue('id')) {

		?><IMG SRC="<?php if(!empty($GLOBALS['_PJ_icon_path'])) echo $GLOBALS['_PJ_icon_path'] ?>/effort.gif" WIDTH="16" HEIGHT="16" BORDER="0" ALIGN="absmiddle">&nbsp;<A CLASS="left" HREF="<?= $GLOBALS['_PJ_efforts_statistics_script'] . "?edit=1&cid=".@$cid.'&pid='.@$pid.'&eid='.@$eid."" ?>"><?php

		if(strlen($effort->giveValue('description')) > $max_length) {

			print substr($effort->giveValue('description'), 0, $max_length-3) . '...';

		} else {

			print $effort->giveValue('description');

		}

		?></A><?php

		} ?>&nbsp;</TD>

			</TR>

		</TABLE></TD>

	</TR><TR>

		<td VALIGN="bottom" CLASS="leftNavi" align="center"><br><br><a href="https://github.com/rubo77/timeeffect" target="_blank">TIMEEFFECT on GitHub</a><br><br></td>

<?php

if($GLOBALS['_PJ_session_length']) {

?>

	</TR><TR>

		<TD CLASS="leftNaviInfo">&nbsp;<?php if(!empty($GLOBALS['_PJ_strings']['session_timeout'])) echo $GLOBALS['_PJ_strings']['session_timeout'] ?>: <?php

printf("%dm %02ds", (($GLOBALS['_PJ_session_timeout']-($GLOBALS['_PJ_session_timeout']%60))/60), ($GLOBALS['_PJ_session_timeout']%60));

		?></TD>

<?php

}

?>

	</TR>

</TABLE>

<!-- shared/left.ihtml - END -->

