<!-- Modern topnav: Actions, theme, user avatar as settings, logout -->
<div class="topnav-modern" style="display: flex; align-items: center; gap: 0.75rem; justify-content: flex-end;">
	<?php if(isset($_PJ_auth) && is_object($_PJ_auth) && $_PJ_auth->checkPermission('agent')) { ?>
	<!-- Action buttons -->
	<button id="start-anything-btn" class="topNav-btn start-anything-btn" onclick="startAnything()" title="<?php echo $GLOBALS['_PJ_strings']['start_activity_title']; ?>">▶</button>
	<button id="stop-all-btn" class="topNav-btn stop-all-btn" onclick="stopAllActivities()" title="<?php echo $GLOBALS['_PJ_strings']['stop_all_activities_title']; ?>">🛑</button>
	<?php } ?>

	<!-- Theme toggle -->
	<button id="theme-toggle" class="theme-toggle-btn" title="Toggle Dark/Light Mode">🌙</button>

	<?php if(isset($_PJ_auth) && is_object($_PJ_auth)) { ?>
	<!-- User dropdown menu -->
	<div class="user-dropdown">
		<!-- User initial in circle -->
		<div class="user-avatar" title="<?php echo $_PJ_auth->giveValue('firstname') . ' ' . $_PJ_auth->giveValue('lastname'); ?>">
			<a href="<?php echo $GLOBALS['_PJ_http_root']; ?>/user/settings.php"><?php 
				$firstname = $_PJ_auth->giveValue('firstname');
				$lastname = $_PJ_auth->giveValue('lastname');
				$initial = '';
				if(!empty($firstname)) {
					$initial = strtoupper(substr($firstname, 0, 1));
				} elseif(!empty($lastname)) {
					$initial = strtoupper(substr($lastname, 0, 1));
				} else {
					$initial = strtoupper(substr($_PJ_auth->giveValue('username'), 0, 1));
				}
				echo $initial;
			?></a>
		</div>

		<!-- Dropdown menu -->
		<div class="user-dropdown-menu">
			<a href="<?php echo $GLOBALS['_PJ_http_root']; ?>/user/settings.php" class="dropdown-item">
				⚙️ <?php echo !empty($GLOBALS['_PJ_strings']['settings']) ? $GLOBALS['_PJ_strings']['settings'] : 'Settings'; ?>
			</a>
			<a href="<?php if(!empty($GLOBALS['PHP_SELF'])) echo $GLOBALS['PHP_SELF'] ?>?logout=1" class="dropdown-item logout">
				⏏ <?php echo !empty($GLOBALS['_PJ_strings']['logout']) ? $GLOBALS['_PJ_strings']['logout'] : 'Logout'; ?>
			</a>
		</div>
	</div>
	<?php } ?>
</div>

<!-- Mobile Navigation Touch Fix -->
<script src="<?php echo $GLOBALS['_PJ_http_root']; ?>/js/mobile-nav.js"></script>