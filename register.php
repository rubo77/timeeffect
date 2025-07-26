<?php
    require_once(__DIR__ . "/bootstrap.php"); // Modern PHP 8.4 compatibility
	$no_login = true; // Disable automatic login requirement
	include_once("include/config.inc.php");
	include_once($_PJ_include_path . '/scripts.inc.php');

	// Check if registration is enabled
	if (!isset($_PJ_allow_registration) || !$_PJ_allow_registration) {
		$error_message = $GLOBALS['_PJ_strings']['registration_disabled'];
		include("$_PJ_root/templates/error.ihtml");
		include_once("$_PJ_include_path/degestiv.inc.php");
		exit;
	}

	// Initialize variables from request
	$register = $_REQUEST['register'] ?? null;
	$altered = $_REQUEST['altered'] ?? null;
	$confirm = $_REQUEST['confirm'] ?? null;
	$token = $_REQUEST['token'] ?? null;

	$center_template = "user";
	$center_title = $GLOBALS['_PJ_strings']['register_new_account'];

	// Handle email confirmation
	if (isset($confirm) && isset($token)) {
		$db = new Database();
		$query = sprintf("SELECT * FROM %s WHERE confirmation_token='%s' AND confirmed=0", 
						 $GLOBALS['_PJ_auth_table'], 
						 mysqli_real_escape_string($db->Link_ID, $token));
		$db->query($query);
		
		if ($db->next_record()) {
			// Confirm the user
			$query = sprintf("UPDATE %s SET confirmed=1, confirmation_token=NULL WHERE id='%s'", 
							 $GLOBALS['_PJ_auth_table'], 
							 $db->f('id'));
			$db->query($query);
			
			$success_message = $GLOBALS['_PJ_strings']['email_confirm_success'];
			include("$_PJ_root/templates/note.ihtml");
		} else {
			$error_message = $GLOBALS['_PJ_strings']['email_confirm_error'];
			include("$_PJ_root/templates/error.ihtml");
		}
		include_once("$_PJ_include_path/degestiv.inc.php");
		exit;
	}

	// Handle registration form submission
	if (isset($register) && isset($altered)) {
		$data = array();
		$data['id'] = null; // New user
		$data['telephone'] = $_POST['telephone'] ?? '';
		$data['facsimile'] = $_POST['facsimile'] ?? '';
		$data['email'] = $_POST['email'] ?? '';
		$data['password'] = $_POST['password'] ?? '';
		$data['password_retype'] = $_POST['password_retype'] ?? '';
		$data['gids'] = isset($_POST['gids']) && is_array($_POST['gids']) ? implode(',', $_POST['gids']) : '';
		$data['lastname'] = add_slashes($_POST['lastname'] ?? '');
		$data['firstname'] = add_slashes($_POST['firstname'] ?? '');
		$data['username'] = add_slashes($_POST['login'] ?? '');
		$data['permissions'] = 'agent'; // Only allow agent permission for registration
		$data['allow_nc'] = 0; // Default to not allowing new customers
		$data['confirmed'] = isset($_PJ_registration_email_confirm) && $_PJ_registration_email_confirm ? 0 : 1;
		$data['confirmation_token'] = isset($_PJ_registration_email_confirm) && $_PJ_registration_email_confirm ? 
									  bin2hex(random_bytes(32)) : null;

		// Create new user
		$new_user = new User($data);
		$message = $new_user->save();
		
		if ($message != '') {
			// Show registration form again with error
			$username = $_POST['login'] ?? '';
			$firstname = $_POST['firstname'] ?? '';
			$lastname = $_POST['lastname'] ?? '';
			$email = $_POST['email'] ?? '';
			$telephone = $_POST['telephone'] ?? '';
			$facsimile = $_POST['facsimile'] ?? '';
			
			include("$_PJ_root/templates/user/register.ihtml");
			include_once("$_PJ_include_path/degestiv.inc.php");
			exit;
		}

		// Registration successful
		if (isset($_PJ_registration_email_confirm) && $_PJ_registration_email_confirm && $data['confirmation_token']) {
			// Send confirmation email
			$subject = "TIMEEFFECT - Email Confirmation";
			$message_body = "Please click the following link to confirm your email address:\n\n";
			$message_body .= $_PJ_http_root . "/register.php?confirm=1&token=" . $data['confirmation_token'] . "\n\n";
			$message_body .= "If you did not request this registration, please ignore this email.";
			
			if (function_exists('mail')) {
				mail($data['email'], $subject, $message_body);
			}
			
			$success_message = $GLOBALS['_PJ_strings']['registration_success'];
		} else {
			$success_message = $GLOBALS['_PJ_strings']['registration_success'];
		}
		
		include("$_PJ_root/templates/note.ihtml");
		include_once("$_PJ_include_path/degestiv.inc.php");
		exit;
	}

	// Show registration form
	include("$_PJ_root/templates/user/register.ihtml");
	include_once("$_PJ_include_path/degestiv.inc.php");
?>