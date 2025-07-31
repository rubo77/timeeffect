<?php
	$inventory_selection	= 'u';
	$statistics_selection	= 'u';
	$reports_selection		= 'u';
	$users_selection		= 'u';
	$groups_selection		= 'u';
	$admin_selection		= 'u';
	switch($PHP_SELF) {
		case $GLOBALS['_PJ_customer_inventory_script']:
		case $GLOBALS['_PJ_projects_inventory_script']:
		case $GLOBALS['_PJ_efforts_inventory_script']:
			$inventory_selection = 's';
			break;
		case $GLOBALS['_PJ_customer_statistics_script']:
		case $GLOBALS['_PJ_projects_statistics_script']:
		case $GLOBALS['_PJ_efforts_statistics_script']:
			$statistics_selection = 's';
			break;
		case $GLOBALS['_PJ_reports_script']:
			$reports_selection = 's';
			break;
		case $GLOBALS['_PJ_user_script']:
		case $GLOBALS['_PJ_own_user_script']:
			$users_selection = 's';
			break;
		case $GLOBALS['_PJ_group_script']:
			$groups_selection = 's';
			break;
		case $GLOBALS['_PJ_admin_script']:
			$admin_selection = 's';
			break;
	}
?>
<!-- shared/main-options.ihtml - START -->
<div id="mainOptions" class="modern-tabs">
<?php
// Check if auth is available and properly initialized
if(!isset($_PJ_auth) || !is_object($_PJ_auth)) {
	debugLog('TEMPLATE_ERROR', 'Auth object not available in main-options template');
	// Skip main options if no auth - this can happen in no_login scripts like password_reset.php
	return;
}
if($_PJ_auth->checkPermission('agent')) {
?>
	<a class="modern-tab<?= ($inventory_selection == 's') ? ' active' : '' ?>" href="<?php if(!empty($GLOBALS['_PJ_customer_inventory_script'])) echo $GLOBALS['_PJ_customer_inventory_script'] ?>"><?=$GLOBALS['_PJ_strings']['inventory']?></a>
<?php
}
?>
	<a class="modern-tab<?= ($statistics_selection == 's') ? ' active' : '' ?>" href="<?php if(!empty($GLOBALS['_PJ_statistics_script'])) echo $GLOBALS['_PJ_statistics_script'] ?>"><?=$GLOBALS['_PJ_strings']['statistics']?></a>
<?php
if($_PJ_auth->checkPermission('admin')) {
?>
	<a class="modern-tab<?= ($users_selection == 's') ? ' active' : '' ?>" href="<?php if(!empty($GLOBALS['_PJ_user_script'])) echo $GLOBALS['_PJ_user_script'] ?>"><?=$GLOBALS['_PJ_strings']['user']?></a>
	<a class="modern-tab<?= ($groups_selection == 's') ? ' active' : '' ?>" href="<?php if(!empty($GLOBALS['_PJ_group_script'])) echo $GLOBALS['_PJ_group_script'] ?>"><?=$GLOBALS['_PJ_strings']['groups']?></a>
	<a class="modern-tab<?= ($admin_selection == 's') ? ' active' : '' ?>" href="<?php if(!empty($GLOBALS['_PJ_admin_script'])) echo $GLOBALS['_PJ_admin_script'] ?>"><?=$GLOBALS['_PJ_strings']['admin']?></a>
<?php
}
?>
	<a class="modern-tab<?= ($reports_selection == 's') ? ' active' : '' ?>" href="<?php if(!empty($GLOBALS['_PJ_reports_script'])) echo $GLOBALS['_PJ_reports_script'] ?>"><?=$GLOBALS['_PJ_strings']['reports']?></a>
</div>
<!-- shared/main-options.ihtml - END -->
