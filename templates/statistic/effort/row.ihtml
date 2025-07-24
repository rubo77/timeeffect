<?php
	$agent = $_PJ_auth->giveUserById($effort->giveValue('user'));
?>
<!-- statistic/effort/row.ihtml - START -->
<?php
	// Fix: Initialize sum variables if not set to prevent undefined variable warnings
	if (!isset($sum_effort_costs)) {
		$sum_effort_costs = 0;
	}
	if (!isset($sum_effort_days)) {
		$sum_effort_days = 0;
	}
	$sum_effort_costs				+= $effort->giveValue('costs');
	$sum_effort_days				+= $effort->giveValue('days');
?>
					<TR>
						<TD COLSPAN="6"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/gray.gif" WIDTH="100%" HEIGHT="1" BORDER="0"></TD>
					</TR>
					<TR HEIGHT="25">
						<TD CLASS="list<?php if(isset($rowclass)) echo $rowclass; ?>" WIDTH="35%"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_icon_path'])) echo $GLOBALS['_PJ_icon_path'] ?>/effort<?php if(!($effort->giveValue('billed') == '' || $effort->giveValue('billed') == '0000-00-00')) print 'b' ?>.gif" BORDER="0" WIDTH="16" HEIGHT="16" ALIGN="absmiddle">&nbsp;<?= $effort->giveValue('description') ?></TD>
						<TD CLASS="listDetailNumeric<?php if(isset($rowclass)) echo $rowclass; ?>"><?= $agent['firstname'] . ' ' . $agent['lastname']; ?></TD>
						<TD CLASS="listDetailNumeric<?php if(isset($rowclass)) echo $rowclass; ?>"><?= $effort->formatDate($effort->giveValue('date')); ?></TD>
						<TD CLASS="listDetailNumeric<?php if(isset($rowclass)) echo $rowclass; ?>"><?= $effort->formatTime($effort->giveValue('begin'), "H:i"); ?> - <?= $effort->formatTime($effort->giveValue('end'), "H:i"); ?></TD>
						<TD CLASS="listDetailNumeric<?php if(isset($rowclass)) echo $rowclass; ?>"><?= $effort->formatNumber($effort->giveValue('days'), true); ?> (<?=$effort->formatNumber($effort->giveValue('days')*8,true)?> h)</TD>
						<TD CLASS="listDetailNumeric<?php if(isset($rowclass)) echo $rowclass; ?>"><?php if($effort->giveValue('costs')) print formatNumber($effort->giveValue('costs'), true) . '&nbsp;' . $GLOBALS['_PJ_currency']; ?></TD>
					</TR>
<!-- statistic/effort/row.ihtml - END -->
