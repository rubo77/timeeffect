<?php
	include_once($GLOBALS['_PJ_include_path'] . '/scripts.inc.php');
?>
<!-- list.ihtml - START -->
<HTML>
<HEAD>
<TITLE>TIMEEFFECT - Login</TITLE>
<!-- Mobile viewport and PWA meta tags for Phase 1 mobile optimization -->
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="theme-color" content="#007bff">
<!-- Original CSS -->
<LINK REL="stylesheet" HREF="<?= $GLOBALS['_PJ_css_path']; ?>/project.css" TYPE="text/css">
<!-- Responsive CSS for mobile optimization -->
<LINK REL="stylesheet" HREF="<?= $GLOBALS['_PJ_css_path']; ?>/responsive.css" TYPE="text/css">
<!-- Modern UI Design Framework -->
<LINK REL="stylesheet" HREF="<?= $GLOBALS['_PJ_css_path']; ?>/modern.css" TYPE="text/css">
<!-- Modern Layout System -->
<LINK REL="stylesheet" HREF="<?= $GLOBALS['_PJ_css_path']; ?>/layout.css" TYPE="text/css">
<SCRIPT LANGUAGE="Javascript1.2" SRC="<?= $GLOBALS['_PJ_http_root']; ?>/include/functions.js" type="text/javascript"></SCRIPT>
<SCRIPT language="Javascript1.2">
<!--
function sf(){
	if(document.forms[0].username.value == '')
		document.forms[0].username.focus();
	else
		document.forms[0].password.focus();
}

//-->
</SCRIPT>
</HEAD>

<BODY CLASS="login-page" onLoad='sf()'>
<TABLE CELLPADDING="0" CELLSPACING="0" BORDER="0" WIDTH="100%" HEIGHT="100%">
	<TR HEIGHT="50">
<!-- START - left navigation -->
		<TD ID="leftNavigation" WIDTH="160" ROWSPAN="2"><!-- shared/left.ihtml - START -->
		<TABLE CELLPADDING="0" CELLSPACING="0" BORDER="0" WIDTH="160" HEIGHT="100%">
			<TR>
				<TD VALIGN="top" CLASS="leftNavi"><TABLE CELLPADDING="3" CELLSPACING="0" BORDER="0">
					<TR>
						<TD CLASS="headFrame" COLSPAN="2" HEIGHT="150" VALIGN="top"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/logo_te_150.png" WIDTH="150" HEIGHT="19" BORDER="0" HSPACE="5" VSPACE="0"></TD>
					</TR><TR>
						<TD><IMG SRC="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/abstand.gif" WIDTH="1" HEIGHT="20" BORDER="0"></TD>
					</TR>
				</TABLE></TD>
			</TR>
		</TABLE>
		</TD>
<!-- END - left navigation -->
		<TD CLASS="headFrame"><TABLE CELLPADDING="0" CELLSPACING="0" BORDER="0" WIDTH="100%">
	<TR HEIGHT="50">
<!-- START - Main Options  -->
		<TD CLASS="mainOptionFrame" VALIGN="bottom">
		<TABLE CELLPADDING="0" CELLSPACING="0" BORDER="0" WIDTH="100%">
			<TR>
				<TD><TABLE CELLPADDING="0" CELLSPACING="0" BORDER="0">
					<TR>
						<TD CLASS="mainOptionS">&nbsp;&nbsp;<A CLASS="mainOptionS" HREF="<?php if(!empty($GLOBALS['PHP_SELF'])) echo $GLOBALS['PHP_SELF'] ?>"><?php if(!empty($GLOBALS['_PJ_strings']['authentication'])) echo $GLOBALS['_PJ_strings']['authentication'] ?></A></TD>
						<TD CLASS="mainOptionDivision"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/main-option-es.gif" WIDTH="10" HEIGHT="24" BORDER="0"></TD>
					</TR>
				</TABLE>
			</TR>
		</TABLE>
		</TD>
<!-- END - Main Options  -->
		<TD CLASS="topNav" VALIGN="bottom" ALIGN="right"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_icon_path'])) echo $GLOBALS['_PJ_icon_path'] ?>/help.gif" WIDTH="16" HEIGHT="16" BORDER="0" ALIGN="absmiddle"><A CLASS="topNav" HREF="JavaScript:openPopUp('<?php if(!empty($GLOBALS['_PJ_help_script'])) echo $GLOBALS['_PJ_help_script'] ?>', 600, 500)"><?php if(!empty($GLOBALS['_PJ_strings']['help'])) echo $GLOBALS['_PJ_strings']['help'] ?></A></TD>
	</TR>
</TABLE></TD>
	</TR><TR>
<TD VALIGN="top">
	<main>
		<!-- START - content -->
		<FORM CLASS="loginForm" METHOD="POST" ACTION="<?php if(!empty($GLOBALS['PHP_SELF'])) echo $GLOBALS['PHP_SELF'] ?>">
		<?php
		// print $_GET && $_POST as hidden form fields. Exclude fields password and username
		print PJAuth::assembleFormFields(NULL, NULL, array('password', 'username'));
		?>
		<!-- Modern Login Card Design -->
		<div class="container" style="max-width: 400px; margin: 2rem auto; padding: 2rem;">
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
} elseif (!empty($GLOBALS['username'])) {
?>
					<div class="alert alert-danger" style="margin: 1rem 0; padding: 1rem; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 0.25rem; color: #721c24;">
						<?php if(!empty($GLOBALS['_PJ_strings']['login_error_msg'])) echo $GLOBALS['_PJ_strings']['login_error_msg'] ?>
					</div>
<?php
}
?>
<?php
if(!empty($GLOBALS['username'])) {
?>
					<TR>
						<TD COLSPAN="2" CLASS="ErrorMessage"><?php if(!empty($GLOBALS['_PJ_strings']['login_error_msg'])) echo $GLOBALS['_PJ_strings']['login_error_msg'] ?></TD>
					</TR><TR>
						<TD COLSPAN="2"><IMG SRC="<?php if(!empty($GLOBALS['_PJ_image_path'])) echo $GLOBALS['_PJ_image_path'] ?>/abstand.gif" HEIGHT="10" WIDTH="1" BORDER="0"></TD>
					</TR>
<?php
}else {
	// select the username of the last added effort
	$db = new Database;
	// Fix SQL Join syntax - use table aliases properly
	$auth_table = $GLOBALS['_PJ_auth_table'];
	$effort_table = $GLOBALS['_PJ_effort_table'];
	$query = sprintf("SELECT username FROM `%s` AS auth LEFT JOIN `%s` AS effort ON auth.id=effort.user
	ORDER BY effort.date DESC LIMIT 1", $auth_table, $effort_table);
	$res = $db->query($query);
	if($row = mysqli_fetch_assoc($res)) {
		$GLOBALS['username']=$row['username'];
	}
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
			</TR><TR>
				<TD class="version">&nbsp;TIMEEFFECT Version:&nbsp;<?php if(!empty($GLOBALS['_PJ_timeeffect_version'])) echo $GLOBALS['_PJ_timeeffect_version'] ?> (Revision: <?php if(!empty($GLOBALS['_PJ_timeeffect_revision'])) echo $GLOBALS['_PJ_timeeffect_revision'] ?>, <?= date($GLOBALS['_PJ_format_datetime'], strtotime($GLOBALS['_PJ_timeeffect_date'])) ?>)</td>
			</TR>
		</TABLE>
		</FORM>
	</main>
</TD>
<!-- END - content -->
	</TR>
</TABLE>
<!-- Mobile JavaScript for Phase 1 mobile optimization -->
<SCRIPT SRC="<?= $GLOBALS['_PJ_http_root']; ?>/js/mobile.js" type="text/javascript"></SCRIPT>
</BODY>
</HTML>
<!-- list.ihtml - END -->
