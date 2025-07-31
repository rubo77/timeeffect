<?php
    require_once(__DIR__ . "/bootstrap.php"); // Modern PHP 8.4 compatibility
	$no_login = true; // Disable automatic login requirement
	include_once("include/config.inc.php");
	include_once($_PJ_include_path . '/scripts.inc.php');

	// Enable debug logging for password reset troubleshooting
	$GLOBALS['_PJ_debug'] = true;
	
	// Check if reset_token column exists first
	$check_query = "SHOW COLUMNS FROM {$GLOBALS['_PJ_auth_table']} LIKE 'reset_token'";
	$db = new Database();
	$db->query($check_query);
	if (!$db->next_record()) {
		debugLog('PASSWORD_RESET_ERROR', 'Column reset_token does not exist in ' . $GLOBALS['_PJ_auth_table']);
		// Skip the update and show error to user
		$error_message = 'Database schema not up to date. Please run migrations.';
		include("$_PJ_root/templates/error.ihtml");
		include_once("$_PJ_include_path/degestiv.inc.php");
		exit;
	}

	// Check if password recovery is enabled
	if (!isset($_PJ_allow_password_recovery) || !$_PJ_allow_password_recovery) {
		$error_message = $GLOBALS['_PJ_strings']['not_implemented'];
		include("$_PJ_root/templates/error.ihtml");
		include_once("$_PJ_include_path/degestiv.inc.php");
		exit;
	}

	// Initialize variables from request
	$reset = $_REQUEST['reset'] ?? null;
	$token = $_REQUEST['token'] ?? null;
	$email = $_REQUEST['email'] ?? '';
	$password = $_REQUEST['password'] ?? '';
	$password_retype = $_REQUEST['password_retype'] ?? '';

	$center_title = $GLOBALS['_PJ_strings']['reset_password'];

	// Handle password reset with token
	if (isset($token)) {
		$query = sprintf("SELECT * FROM %s WHERE reset_token='%s' AND reset_expires > NOW()", 
						 $GLOBALS['_PJ_auth_table'], 
						 mysqli_real_escape_string($db->Link_ID, $token));
		$db->query($query);
		
		if ($db->next_record()) {
			$user_id = $db->f('id');
			
			if (isset($reset)) {
				// Process password reset
				if ($password == '') {
					$message = $GLOBALS['_PJ_strings']['error_pw_empty'];
				} elseif ($password != $password_retype) {
					$message = $GLOBALS['_PJ_strings']['error_pw_retype'];
				} else {
					// Update password and clear reset token
					$new_password = md5($password);
					$query = sprintf("UPDATE %s SET password='%s', reset_token=NULL, reset_expires=NULL WHERE id='%s'", 
									 $GLOBALS['_PJ_auth_table'], 
									 $new_password, 
									 $user_id);
					$db->query($query);
					
					$success_message = $GLOBALS['_PJ_strings']['password_reset_success'];
					$center_template = ''; // additional template content for the notification
					include("$_PJ_root/templates/note.ihtml");
					include_once("$_PJ_include_path/degestiv.inc.php");
					exit;
				}
			}
			
			// Show password reset form
			?>
			<HTML>
			<HEAD>
			<TITLE>TIMEEFFECT - <?= $center_title; ?></TITLE>
			<LINK REL="stylesheet" HREF="<?php print $_PJ_css_path; ?>/project.css" TYPE="text/css">
			<LINK REL="stylesheet" HREF="<?php print $_PJ_css_path; ?>/responsive.css" TYPE="text/css">
			<LINK REL="stylesheet" HREF="<?php print $_PJ_css_path; ?>/modern.css" TYPE="text/css">
			<LINK REL="stylesheet" HREF="<?php print $_PJ_css_path; ?>/layout.css" TYPE="text/css">
			</HEAD>
			<BODY>
			<div class="container" style="max-width: 400px; margin: 2rem auto; padding: 2rem;">
				<div class="card">
					<div class="card-header">
						<h1 class="card-title" style="text-align: center;"><?= $center_title ?></h1>
					</div>
					<FORM METHOD="POST">
					<INPUT TYPE="hidden" NAME="token" VALUE="<?= htmlspecialchars($token) ?>">
					<INPUT TYPE="hidden" NAME="reset" VALUE="1">
					
					<?php if(isset($message)): ?>
					<div class="form-error" style="margin-bottom: 1rem; color: red;">
						<?= $message ?>
					</div>
					<?php endif; ?>
					
					<div class="form-group">
						<label class="form-label" for="password"><?= $GLOBALS['_PJ_strings']['password'] ?>*:</label>
						<input type="password" id="password" name="password" required>
					</div>
					
					<div class="form-group">
						<label class="form-label" for="password_retype"><?= $GLOBALS['_PJ_strings']['password_retype'] ?>*:</label>
						<input type="password" id="password_retype" name="password_retype" required>
					</div>
					
					<div class="form-group">
						<button type="submit" class="btn btn-primary" style="width: 100%;">
							<?= $GLOBALS['_PJ_strings']['reset_password'] ?>
						</button>
					</div>
					</FORM>
				</div>
			</div>
			</BODY>
			</HTML>
			<?php
			exit;
		} else {
			$error_message = $GLOBALS['_PJ_strings']['password_reset_error'];
			include("$_PJ_root/templates/error.ihtml");
			include_once("$_PJ_include_path/degestiv.inc.php");
			exit;
		}
	}

	// Handle password reset request
	if (isset($reset) && $email != '') {
		$db = new Database();
		$db->connect(); // Ensure database connection is established
		$user_query = sprintf("SELECT * FROM %s WHERE email='%s'", 
						 $GLOBALS['_PJ_auth_table'], 
						 mysqli_real_escape_string($db->Link_ID, $email));
		$db->query($user_query);
		
		if ($db->next_record()) {
			// Store user ID before any other queries
			$user_id = $db->f('id');
			
			// Generate reset token
			$reset_token = bin2hex(random_bytes(32));
			$expires = date('Y-m-d H:i:s', strtotime('+24 hours'));
			
			$query = sprintf("UPDATE %s SET reset_token='%s', reset_expires='%s' WHERE id='%s'", 
							 $GLOBALS['_PJ_auth_table'], 
							 $reset_token, 
							 $expires, 
							 $user_id);
			$result = $db->query($query);
			if (!$result) {
				die('PASSWORD_RESET_ERROR: SQL Error: ' . $db->Error . ' (Errno: ' . $db->Errno . ')');exit;
			} else {
				// die('PASSWORD_RESET: Token saved successfully for user ID: ' . $user_id);exit;
			}
			
			// Send reset email
			$subject = "TIMEEFFECT - Password Reset";
			$message_body = "Please click the following link to reset your password:\n\n";
			
			// Construct full URL with domain
			$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
			$domain = $_SERVER['HTTP_HOST'] ?? 'localhost';
			$reset_url = $protocol . '://' . $domain . '/password_reset.php?token=' . $reset_token;
			
			$message_body .= $reset_url . "\n\n";
			$message_body .= "This link will expire in 24 hours. If you did not request this reset, please ignore this email.";
			
			if (function_exists('mail')) {
				mail($email, $subject, $message_body);
				// die('PASSWORD_RESET: Email sent successfully to ' . $email);
				debugLog('PASSWORD_RESET', 'Email sent successfully to ' . $email);
			} else {
				die('PASSWORD_RESET_ERROR: Email function not available');
			}
		} else {
			// die('PASSWORD_RESET_ERROR: User not found for email: ' . $email . ' in ' . $GLOBALS['_PJ_auth_table'] . ' with query: ' . $user_query); exit;// DEBUG
			debugLog('PASSWORD_RESET_ERROR', 'User not found for email: ' . $email . ' in ' . $GLOBALS['_PJ_auth_table']);
		}
		// Always show success message to prevent email enumeration
		$success_message = $GLOBALS['_PJ_strings']['password_reset_sent'];
		$center_template = ''; // additional template content for the notification
		include("$_PJ_root/templates/note.ihtml");
		include_once("$_PJ_include_path/degestiv.inc.php");
		exit;
	}

	// Show email input form
	?>
	<HTML>
	<HEAD>
	<TITLE>TIMEEFFECT - <?= $center_title; ?></TITLE>
	<LINK REL="stylesheet" HREF="<?php print $_PJ_css_path; ?>/project.css" TYPE="text/css">
	<LINK REL="stylesheet" HREF="<?php print $_PJ_css_path; ?>/responsive.css" TYPE="text/css">
	<LINK REL="stylesheet" HREF="<?php print $_PJ_css_path; ?>/modern.css" TYPE="text/css">
	<LINK REL="stylesheet" HREF="<?php print $_PJ_css_path; ?>/layout.css" TYPE="text/css">
	</HEAD>
	<BODY>
	<div class="container" style="max-width: 400px; margin: 2rem auto; padding: 2rem;">
		<div class="card">
			<div class="card-header">
				<h1 class="card-title" style="text-align: center;"><?= $center_title ?></h1>
			</div>
			<FORM METHOD="POST">
			<INPUT TYPE="hidden" NAME="reset" VALUE="1">
			
			<div class="form-group">
				<label class="form-label" for="email"><?= $GLOBALS['_PJ_strings']['email'] ?>*:</label>
				<input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required 
					   placeholder="Enter your email address">
			</div>
			
			<div class="form-group">
				<button type="submit" class="btn btn-primary" style="width: 100%;">
					<?= $GLOBALS['_PJ_strings']['reset_password'] ?>
				</button>
			</div>
			
			<div class="form-help" style="text-align: center; margin-top: 1rem;">
				<a href="<?= $GLOBALS['_PJ_http_root'] ?>/" class="form-link">Back to Login</a>
			</div>
			</FORM>
		</div>
	</div>
	</BODY>
	</HTML>
	<?php
	include_once("$_PJ_include_path/degestiv.inc.php");
?>