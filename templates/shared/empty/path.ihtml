<?php

// Fix: Show breadcrumbs on edit/detail pages, not just lists
if(isset($user) && is_object($user) && $user->giveValue('id') &&
   (strpos($_SERVER['PHP_SELF'], '/user/') !== false || 
    $SCRIPT_NAME == $GLOBALS['_PJ_user_script'])) {

$uid = $user->giveValue('id');

?><div class="breadcrumb-path"><img src="<?php if(!empty($GLOBALS['_PJ_icon_path'])) echo $GLOBALS['_PJ_icon_path'] ?>/user.gif" width="16" height="16" border="0" class="breadcrumb-icon">&nbsp;<span class="breadcrumb-item">[ID: <?= $uid ?>] <?= htmlspecialchars($user->giveValue('firstname') . ' ' . $user->giveValue('lastname')) ?></span></div><?php

}

// Fix: Show group breadcrumbs on edit/detail pages
if(isset($group) && is_object($group) && $group->giveValue('id') &&
   (strpos($_SERVER['PHP_SELF'], '/groups/') !== false || 
    $SCRIPT_NAME == $GLOBALS['_PJ_group_script'])) {

$gid = $group->giveValue('id');

?><div class="breadcrumb-path"><img src="<?php if(!empty($GLOBALS['_PJ_icon_path'])) echo $GLOBALS['_PJ_icon_path'] ?>/group.gif" width="16" height="16" border="0" class="breadcrumb-icon">&nbsp;<span class="breadcrumb-item">[ID: <?= $gid ?>] <?= htmlspecialchars($group->giveValue('name')) ?></span></div><?php

}

