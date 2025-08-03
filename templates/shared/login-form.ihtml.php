<?php
// Shared login form component - can be included on login page and landing page
// Expects: $form_action (optional, defaults to current page)
// Expects: $form_class (optional, defaults to 'loginForm')
// Expects: $container_style (optional, for custom styling)

$form_action = $form_action ?? $GLOBALS['PHP_SELF'] ?? '';
$form_class = $form_class ?? 'loginForm';
$container_style = $container_style ?? 'max-width: 400px; margin: 2rem auto; padding: 2rem;';

// Get username from last effort if not set
if(empty($GLOBALS['username'])) {
	$db = new Database();
	$auth_table = $GLOBALS['_PJ_auth_table'];
	$effort_table = $GLOBALS['_PJ_effort_table'];
	$query = sprintf("SELECT username FROM `%s` AS auth LEFT JOIN `%s` AS effort ON auth.id=effort.user
	ORDER BY effort.date DESC LIMIT 1", $auth_table, $effort_table);
	$res = $db->query($query);
	if($row = mysqli_fetch_assoc($res)) {
		$GLOBALS['username'] = $row['username'];
	}
}
?>

<FORM CLASS="<?= $form_class ?>" METHOD="POST" ACTION="<?= $form_action ?>">
<?php
// Print $_GET && $_POST as hidden form fields. Exclude fields password and username
print PJAuth::assembleFormFields(NULL, NULL, array('password', 'username'));
?>
<!-- Modern Login Card Design -->
<div class="container" style="<?= $container_style ?>">
	<div class="card">
		<div class="card-header">
			<h1 class="card-title" style="text-align: center; display: flex; align-items: center; justify-content: center; gap: 1rem;">
				<img src="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/login-welcome.gif" height="40" id="welcomeImage" width="36" border="0" style="display: none;">
				<?=$GLOBALS['_PJ_strings']['login-welcome']?>
			</h1>
		</div>
<?php
if(!empty($GLOBALS['login_lockout'])) {
?>
		<div class="alert alert-danger" style="margin: 1rem 0; padding: 1rem; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 0.25rem; color: #721c24;">
			<h4 style="margin: 0 0 0.5rem 0;">🔒 <?=$GLOBALS['_PJ_strings']['account_locked'] ?? 'Account Locked'?></h4>
			<p style="margin: 0;">
				<?php if($GLOBALS['lockout_reason'] == 'ip'): ?>
					<?=$GLOBALS['_PJ_strings']['lockout_ip_message'] ?? 'Too many failed login attempts from your IP address.'?>
				<?php else: ?>
					<?=$GLOBALS['_PJ_strings']['lockout_user_message'] ?? 'Too many failed login attempts for this username.'?>
				<?php endif; ?>
			</p>
			<p style="margin: 0.5rem 0 0 0; font-weight: bold;">
				<?=$GLOBALS['_PJ_strings']['lockout_until'] ?? 'Please try again after'?>: <?=$GLOBALS['lockout_until']?>
			</p>
		</div>
<?php
} elseif (!empty($GLOBALS['login_failed']) && !empty($GLOBALS['remaining_attempts'])) {
?>
		<div class="alert alert-warning" style="margin: 1rem 0; padding: 1rem; background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 0.25rem; color: #856404;">
			<h4 style="margin: 0 0 0.5rem 0;">⚠️ <?=$GLOBALS['_PJ_strings']['login_failed'] ?? 'Login Failed'?></h4>
			<p style="margin: 0;">
				<?=$GLOBALS['_PJ_strings']['remaining_attempts'] ?? 'Remaining attempts'?>: <strong><?=$GLOBALS['remaining_attempts']?></strong>
			</p>
			<p style="margin: 0.5rem 0 0 0; font-size: 0.9rem;">
				<?=$GLOBALS['_PJ_strings']['lockout_warning'] ?? 'Your account will be temporarily locked after too many failed attempts.'?>
			</p>
		</div>
<?php
} elseif (!empty($GLOBALS['username']) && !empty($GLOBALS['_PJ_strings']['login_error_msg'])) {
?>
		<div class="alert alert-danger" style="margin: 1rem 0; padding: 1rem; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 0.25rem; color: #721c24;">
			<?php echo $GLOBALS['_PJ_strings']['login_error_msg'] ?>
		</div>
<?php
}
?>
		<!-- Modern Form Fields -->
		<div class="form-group">
			<label class="form-label" for="username"><?=$GLOBALS['_PJ_strings']['username']?>:</label>
			<input type="text" id="username" name="username" value="<?= @$GLOBALS['username'] ?>" placeholder="<?=$GLOBALS['_PJ_strings']['username']?>" required>
		</div>
		
		<div class="form-group">
			<label class="form-label" for="password"><?=$GLOBALS['_PJ_strings']['password']?>:</label>
			<input type="password" id="password" name="password" placeholder="<?=$GLOBALS['_PJ_strings']['password']?>" required>
		</div>
		
		<div class="form-group">
			<label class="form-label" for="lang"><?=$GLOBALS['_PJ_strings']['language']?>:</label>
			<select id="lang" name="lang">
				<option value="de"<?php if($GLOBALS['_PJ_language'] == 'de') print ' selected'; ?>><?=$GLOBALS['_PJ_strings']['language_de']?></option>
				<option value="en"<?php if($GLOBALS['_PJ_language'] == 'en') print ' selected'; ?>><?=$GLOBALS['_PJ_strings']['language_en']?></option>
				<option value="fr"<?php if($GLOBALS['_PJ_language'] == 'fr') print ' selected'; ?>><?=$GLOBALS['_PJ_strings']['language_fr']?></option>
			</select>
		</div>
		
		<div class="form-group">
			<button type="submit" class="btn btn-primary" style="width: 100%;">
				<span><?=$GLOBALS['_PJ_strings']['login']?></span>
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-left: 0.5rem;">
					<path d="M5 12h14M12 5l7 7-7 7"/>
				</svg>
			</button>
		</div>
		
		<!-- Registration and Password Recovery Links -->
		<div class="form-help" style="text-align: center; margin-top: 1rem;">
<?php if (isset($GLOBALS['_PJ_allow_registration']) && $GLOBALS['_PJ_allow_registration']): ?>
			<a href="<?= $GLOBALS['_PJ_http_root'] ?>/register.php" class="form-link"><?= $GLOBALS['_PJ_strings']['register_new_account'] ?></a>
			<br>
<?php endif; ?>
<?php if (isset($GLOBALS['_PJ_allow_password_recovery']) && $GLOBALS['_PJ_allow_password_recovery']): ?>
			<a href="<?= $GLOBALS['_PJ_http_root'] ?>/password_reset.php" class="form-link"><?= $GLOBALS['_PJ_strings']['forgot_password'] ?></a>
<?php endif; ?>
		</div>
		
<?php
if($GLOBALS['_PJ_session_length']) {
?>
		<div class="form-help" style="text-align: center; margin-top: 1rem;">
			<?=$GLOBALS['_PJ_strings']['session-expire']?>
		</div>
<?php
}
?>
	</div>
</div>
</FORM>

<script>
// Auto-focus function for login form
function sf(){
	if(document.forms[0].username.value == '')
		document.forms[0].username.focus();
	else
		document.forms[0].password.focus();
}

// Auto-focus on page load if not already done
if (typeof window.loginFormFocused === 'undefined') {
	window.loginFormFocused = true;
	sf();
}
</script>
