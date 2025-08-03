<!-- shared/left.ihtml - START -->

<?php

	$max_length	= 17;

	$nav_width = 120;

?>

<!-- Modern Navigation with Pastel Design -->
<div class="modern-nav animate-float">
	<!-- Logo Section -->
	<div style="padding: 1.5rem; text-align: center; border-bottom: 1px solid #e5e7eb; margin-bottom: 1rem;">
		<img src="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/logo_te_150.png" width="120" height="15" border="0" class="animate-glow" style="filter: brightness(1.1);">
	</div>
	
	<!-- Navigation Header - Removed per user request -->
	<div style="padding: 0 1rem 0.5rem 1rem;">
		<!-- Navigation title removed as requested -->
	</div>
	
	<!-- Main Navigation Items -->
	<div id="main_nav_items" style="padding: 0 0.5rem;">
		<a href="<?php if(!empty($GLOBALS['_PJ_customer_inventory_script'])) echo $GLOBALS['_PJ_customer_inventory_script'] ?>" class="nav-item" style="display: flex; align-items: center; gap: 0.75rem;">
			<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
				<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
				<circle cx="12" cy="7" r="4"/>
			</svg>
			<span><?php if(!empty($GLOBALS['_PJ_strings']['customers'])) echo $GLOBALS['_PJ_strings']['customers'] ?></span>
			<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-left: auto; opacity: 0.5;">
				<polyline points="6 9 12 15 18 9"/>
			</svg>
		</a>
		
		<!-- Customer Submenu -->
		<div style="margin-left: 1rem; border-left: 2px solid rgba(99, 102, 241, 0.1); padding-left: 1rem;">
			<?php
				// CustomerList für Navigation laden
				$nav_customers = new CustomerList($_PJ_auth, true);
				while($nav_customers->nextCustomer()) {
					$nav_customer = $nav_customers->giveCustomer();
					?>
					<a href="<?= $GLOBALS['_PJ_project_inventory_script'] . '?list=1&cid=' . $nav_customer->giveValue('id') ?>" 
					class="nav-item" 
					style="display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem 0.75rem; margin-bottom: 0.25rem; font-size: 0.875rem;">
						<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="opacity: 0.6;">
							<circle cx="12" cy="12" r="3"/>
							<path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1 1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/>
						</svg>
						<span><?= substr($nav_customer->giveValue('customer_name'), 0, 18) ?><?php if(strlen($nav_customer->giveValue('customer_name')) > 18) print "..."; ?></span>
					</a>
					<?php
				}
			?>
		</div>
		
		<!-- Projects Navigation -->
		<a href="<?php if(!empty($GLOBALS['_PJ_projects_inventory_script'])) echo $GLOBALS['_PJ_projects_inventory_script'] ?>" class="nav-item" style="display: flex; align-items: center; gap: 0.75rem;">
			<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
				<rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
				<rect x="9" y="9" width="6" height="6"/>
				<path d="M9 1v6M15 1v6M9 17v6M15 17v6M1 9h6M1 15h6M17 9h6M17 15h6"/>
			</svg>
			<span><?php if(!empty($GLOBALS['_PJ_strings']['projects'])) echo $GLOBALS['_PJ_strings']['projects'] ?></span>
		</a>
		
		<!-- Efforts Navigation -->
		<div style="display: flex; align-items: center; gap: 0.5rem;">
			<a href="<?php if(!empty($GLOBALS['_PJ_efforts_inventory_script'])) echo $GLOBALS['_PJ_efforts_inventory_script'] ?>" class="nav-item" style="display: flex; align-items: center; gap: 0.75rem; flex: 1;">
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
					<circle cx="12" cy="12" r="10"/>
					<polyline points="12,6 12,12 16,14"/>
				</svg>
				<span><?php if(!empty($GLOBALS['_PJ_strings']['efforts'])) echo $GLOBALS['_PJ_strings']['efforts'] ?></span>
			</a>
		</div>
		
		<!-- Statistics Navigation -->
		<a href="<?php if(!empty($GLOBALS['_PJ_projects_statistics_script'])) echo $GLOBALS['_PJ_projects_statistics_script'] ?>" class="nav-item" style="display: flex; align-items: center; gap: 0.75rem;">
			<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
				<path d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
			</svg>
			<span><?php if(!empty($GLOBALS['_PJ_strings']['statistics'])) echo $GLOBALS['_PJ_strings']['statistics'] ?></span>
		</a>
		
		<!-- Reports Navigation -->
		<a href="<?php if(!empty($GLOBALS['_PJ_reports_script'])) echo $GLOBALS['_PJ_reports_script'] ?>" class="nav-item" style="display: flex; align-items: center; gap: 0.75rem;">
			<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
				<path d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
			</svg>
			<span><?php if(!empty($GLOBALS['_PJ_strings']['reports'])) echo $GLOBALS['_PJ_strings']['reports'] ?></span>
		</a>
		
		<!-- Open Efforts Navigation -->
		<?php
			$__PJ_open_efforts = new OpenEfforts($_PJ_auth);
			if($__PJ_open_efforts->effortCount()) {
		?>
		<div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid rgba(99, 102, 241, 0.1);">
			<h4 style="color: var(--text-secondary); font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; margin: 0 0 0.5rem 1rem; opacity: 0.7;">
				<?php if(!empty($GLOBALS['_PJ_strings']['open_efforts'])) echo $GLOBALS['_PJ_strings']['open_efforts'] ?>
			</h4>
			<?php
				while($__PJ_open_efforts->nextEffort()) {
					$__effort = $__PJ_open_efforts->giveEffort();
			?>
			<div class="nav-item" style="display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem 0.75rem; margin-bottom: 0.25rem; font-size: 0.875rem;">
				<a href="<?php if(!empty($GLOBALS['_PJ_efforts_inventory_script'])) echo $GLOBALS['_PJ_efforts_inventory_script'] ?>?edit=1&eid=<?= $__effort->giveValue('id'); ?>" 
				style="display: flex; align-items: center; gap: 0.5rem; flex: 1; text-decoration: none; color: inherit;">
					<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="opacity: 0.6; color: var(--success-color);">
						<circle cx="12" cy="12" r="10"/>
						<polygon points="10,8 16,12 10,16"/>
					</svg>
					<span><?= substr($__effort->giveValue('description'), 0, 16) ?><?php if(strlen($__effort->giveValue('description')) > 16) print "..."; ?></span>
				</a>
				<a href="<?= $GLOBALS['_PJ_efforts_inventory_script'] . '?stop=1&eid=' . $__effort->giveValue('id'); ?>" 
				class="nav-stop-link"
				title="<?php if(!empty($GLOBALS['_PJ_strings']['stop'])) echo $GLOBALS['_PJ_strings']['stop'] ?>">🛑</a>
			</div>
			<?php
				}
			?>
		</div>
		<?php
			}
		?>
	</div>
	
	<!-- Footer Section -->
	<div style="position: absolute; bottom: 0; left: 0; right: 0; padding: 1rem; text-align: center; border-top: 1px solid rgba(99, 102, 241, 0.1); background: linear-gradient(180deg, transparent 0%, rgba(99, 102, 241, 0.02) 100%);">
		<a href="https://github.com/rubo77/timeeffect" target="_blank" style="color: var(--text-secondary); text-decoration: none; font-size: 0.75rem; opacity: 0.8; transition: var(--transition-normal);" onmouseover="this.style.opacity='1'; this.style.color='var(--primary-color)'" onmouseout="this.style.opacity='0.8'; this.style.color='var(--text-secondary)'">
			TIMEEFFECT on GitHub
		</a>
		<?php
			if($GLOBALS['_PJ_session_length']) {
				$timeout = (int)$GLOBALS['_PJ_session_timeout'];
		?>
		<div style="margin-top: 0.5rem; font-size: 0.7rem; color: var(--text-secondary); opacity: 0.6;">
			<?php if(!empty($GLOBALS['_PJ_strings']['session_timeout'])) echo $GLOBALS['_PJ_strings']['session_timeout'] ?>: 
			<?php printf("%dm %02ds", (($timeout-($timeout%60))/60), ($timeout%60)); ?>
		</div>
		<?php
			}
		?>
	</div>

<!-- shared/left.ihtml - END -->

