<?php

// Fix: Show customer breadcrumbs on inventory pages (edit/detail/list)
if(isset($customer) && is_object($customer) && $customer->giveValue('id') &&
   (strpos($_SERVER['PHP_SELF'], '/inventory/') !== false ||
    $SCRIPT_NAME == $GLOBALS['_PJ_customer_inventory_script'] ||
    $SCRIPT_NAME == $GLOBALS['_PJ_projects_inventory_script'] ||
    $SCRIPT_NAME == $GLOBALS['_PJ_efforts_inventory_script'])) {

$c_id = $customer->giveValue('id');

?><div class="breadcrumb-path"><img src="<?php if(!empty($GLOBALS['_PJ_icon_path'])) echo $GLOBALS['_PJ_icon_path'] ?>/customer.gif" width="16" height="16" border="0" class="breadcrumb-icon">&nbsp;<span class="breadcrumb-item">[ID: <?= $c_id ?>] <?= htmlspecialchars($customer->giveValue('customer_name')) ?></span></div><?php

}


if(isset($project) && is_object($project) &&

	$project->giveValue('id') &&

	((($SCRIPT_NAME == $GLOBALS['_PJ_projects_inventory_script']) && isset($list) && $list!=1) ||

	  ($SCRIPT_NAME == $GLOBALS['_PJ_efforts_inventory_script']))) {

$p_id = $project->giveValue('id');

if(!empty($c_id)) {

?>&nbsp;<IMG SRC="<?php if(!empty($GLOBALS['_PJ_icon_path'])) echo $GLOBALS['_PJ_icon_path'] ?>/arrow-r.gif" WIDTH="11" HEIGHT="7" BORDER="0">&nbsp;<?php

} ?><IMG SRC="<?php if(!empty($GLOBALS['_PJ_icon_path'])) echo $GLOBALS['_PJ_icon_path'] ?>/project.gif" WIDTH="16" HEIGHT="16" BORDER="0" ALIGN="absmiddle">&nbsp<?php

print "[ID: '$p_id']&nbsp;" . $project->giveValue('project_name');

}

if(isset($effort) && is_object($effort) &&

	$effort->giveValue('id') &&

	($list != 1) &&

	($SCRIPT_NAME == $GLOBALS['_PJ_efforts_inventory_script'])) {

	$e_id = $effort->giveValue('id');

	if($p_id || $c_id) {

?>&nbsp;<IMG SRC="<?php if(!empty($GLOBALS['_PJ_icon_path'])) echo $GLOBALS['_PJ_icon_path'] ?>/arrow-r.gif" WIDTH="11" HEIGHT="7" BORDER="0">&nbsp;<?php

	} ?><IMG SRC="<?php if(!empty($GLOBALS['_PJ_icon_path'])) echo $GLOBALS['_PJ_icon_path'] ?>/effort.gif" WIDTH="16" HEIGHT="16" BORDER="0" ALIGN="absmiddle">&nbsp;<?php

print "[ID: '$e_id']&nbsp;" . $effort->giveValue('description');

}
