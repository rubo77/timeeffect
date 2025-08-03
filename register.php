<?php
    require_once(__DIR__ . "/bootstrap.php"); // Modern PHP 8.4 compatibility
	$no_login = true; // Disable automatic login requirement
	include_once("include/config.inc.php");
	include_once($_PJ_include_path . '/scripts.inc.php');

	// Check if registration is enabled
	if (!isset($_PJ_allow_registration) || !$_PJ_allow_registration) {
		$error_message = $GLOBALS['_PJ_strings']['registration_disabled'];
		include("$_PJ_root/templates/error.ihtml.php");
		include_once("$_PJ_include_path/degestiv.inc.php");
		exit;
	}

	// Initialize variables from request
	$register = $_REQUEST['register'] ?? null;
	$altered = $_REQUEST['altered'] ?? null;
	$confirm = $_REQUEST['confirm'] ?? null;
	$token = $_REQUEST['token'] ?? null;
	$email_only = $_REQUEST['email_only'] ?? null;
	$complete = $_REQUEST['complete'] ?? null;

	$center_template = "user";
	$center_title = $GLOBALS['_PJ_strings']['register_new_account'];

	// Handle registration completion with token
	if (isset($complete) && isset($token)) {
		$db = new Database();
		$query = sprintf("SELECT * FROM %s WHERE confirmation_token='%s' AND confirmed=0", 
						 $GLOBALS['_PJ_auth_table'], 
						 mysqli_real_escape_string($db->Link_ID, $token));
		$db->query($query);
		
		if ($db->next_record()) {
			$email = $db->f('email');
			// Show completion form - this will use the template with completion_step = true
			include("$_PJ_root/templates/user/register.ihtml");
			include_once("$_PJ_include_path/degestiv.inc.php");
			exit;
		} else {
			$error_message = $GLOBALS['_PJ_strings']['email_confirm_error'];
			include("$_PJ_root/templates/error.ihtml");
			include_once("$_PJ_include_path/degestiv.inc.php");
			exit;
		}
	}

	// Handle email confirmation (legacy flow)
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
			include("$_PJ_root/templates/note.ihtml.php");
		} else {
			$error_message = $GLOBALS['_PJ_strings']['email_confirm_error'];
			include("$_PJ_root/templates/error.ihtml.php");
		}
		include_once("$_PJ_include_path/degestiv.inc.php");
		exit;
	}

	// Handle email-only registration (first step)
	if (isset($register) && isset($altered) && isset($email_only)) {
		$email = $_POST['email'] ?? '';
		
		if ($email == '') {
			$message = $GLOBALS['_PJ_strings']['error_email_empty'] ?? 'Email address is required.';
			include("$_PJ_root/templates/user/register.ihtml");
			include_once("$_PJ_include_path/degestiv.inc.php");
			exit;
		}
		
		// Check if email is already registered
		$db = new Database();
		$check_query = sprintf("SELECT id FROM %s WHERE email='%s'", 
							   $GLOBALS['_PJ_auth_table'], 
							   mysqli_real_escape_string($db->Link_ID, $email));
		$db->query($check_query);
		
		if ($db->next_record()) {
			$message = $GLOBALS['_PJ_strings']['error_email_exists'] ?? 'This email address is already registered.';
			include("$_PJ_root/templates/user/register.ihtml");
			include_once("$_PJ_include_path/degestiv.inc.php");
			exit;
		}
		
		// Create minimal user record with token
		$confirmation_token = bin2hex(random_bytes(32));
		$data = array(
			'id' => null,
			'email' => $email,
			'confirmed' => 0,
			'confirmation_token' => $confirmation_token,
			'username' => '', // Will be set during completion
			'password' => '', // Will be set during completion
			'firstname' => '',
			'lastname' => '',
			'telephone' => '',
			'facsimile' => '',
			'gids' => '',
			'permissions' => 'agent'
		);
		
		// Insert user record
		$insert_query = sprintf(
			"INSERT INTO %s (email, confirmed, confirmation_token, username, password, firstname, lastname, telephone, facsimile, gids, permissions) VALUES ('%s', 0, '%s', '', '', '', '', '', '', '', 'agent')",
			$GLOBALS['_PJ_auth_table'],
			mysqli_real_escape_string($db->Link_ID, $email),
			$confirmation_token
		);
		
		$result = $db->query($insert_query);
		if (!$result) {
			$message = 'Registration failed. Please try again.';
			include("$_PJ_root/templates/user/register.ihtml");
			include_once("$_PJ_include_path/degestiv.inc.php");
			exit;
		}
		
		// Send registration completion email
		$subject = "TIMEEFFECT - Complete Your Registration";
		$message_body = "Please click the following link to complete your registration:\n\n";
		$message_body .= $_PJ_http_root . "/register.php?complete=1&token=" . $confirmation_token . "\n\n";
		$message_body .= "This link will allow you to set your username, password and other account details.\n\n";
		$message_body .= "If you did not request this registration, please ignore this email.";
		
		if (function_exists('mail')) {
			mail($email, $subject, $message_body);
		}
		
		$success_message = 'Registration initiated! Please check your email and click the link to complete your account setup.';
		$center_template = '';
		include("$_PJ_root/templates/note.ihtml");
		include_once("$_PJ_include_path/degestiv.inc.php");
		exit;
	}

	// Handle registration completion (second step)
	if (isset($register) && isset($altered) && isset($complete) && isset($token)) {
		$db = new Database();
		$query = sprintf("SELECT * FROM %s WHERE confirmation_token='%s' AND confirmed=0", 
						 $GLOBALS['_PJ_auth_table'], 
						 mysqli_real_escape_string($db->Link_ID, $token));
		$db->query($query);
		
		if (!$db->next_record()) {
			$error_message = $GLOBALS['_PJ_strings']['email_confirm_error'];
			include("$_PJ_root/templates/error.ihtml");
			include_once("$_PJ_include_path/degestiv.inc.php");
			exit;
		}
		
		$user_id = $db->f('id');
		$email = $db->f('email');
		
		// Validate completion form data
		$data = array();
		$data['id'] = $user_id;
		$data['email'] = $email; // Keep existing email
		$data['telephone'] = $_POST['telephone'] ?? '';
		$data['facsimile'] = $_POST['facsimile'] ?? '';
		$data['password'] = $_POST['password'] ?? '';
		$data['password_retype'] = $_POST['password_retype'] ?? '';
		$data['lastname'] = add_slashes($_POST['lastname'] ?? '');
		$data['firstname'] = add_slashes($_POST['firstname'] ?? '');
		$data['username'] = add_slashes($_POST['login'] ?? '');
		
		// Handle secure group membership - filter out invalid groups and handle no-group option
		$selected_gids = isset($_POST['gids']) && is_array($_POST['gids']) ? $_POST['gids'] : array();
		$safe_gids = array();
		
		// Filter groups to only allow valid user groups from gids table or no group (0)
		foreach ($selected_gids as $gid) {
			$gid = intval($gid);
			if ($gid == 0) {
				// No group membership selected - this is the secure default
				continue; // Skip adding to gids (empty = no groups)
			} else {
				// Verify group exists in gids table (user-defined groups)
				$db_check = new Database();
				$db_check->connect();
				$db_check->query("SELECT id FROM " . $GLOBALS['_PJ_gid_table'] . " WHERE id = $gid");
				if ($db_check->next_record()) {
					$safe_gids[] = $gid; // Only add verified existing groups
				}
			}
		}
		
		$data['gids'] = implode(',', $safe_gids); // Empty string if no groups selected
		$data['permissions'] = 'agent'; // Only allow agent permission for registration
		$data['confirmed'] = 1; // Mark as confirmed upon completion
		$data['confirmation_token'] = null; // Clear token
		
		// Update user record with complete information
		$user = new User($data);
		$message = $user->save();
		
		if ($message != '') {
			// Show completion form again with error
			$username = $_POST['login'] ?? '';
			$firstname = $_POST['firstname'] ?? '';
			$lastname = $_POST['lastname'] ?? '';
			$telephone = $_POST['telephone'] ?? '';
			$facsimile = $_POST['facsimile'] ?? '';
			
			include("$_PJ_root/templates/user/register.ihtml");
			include_once("$_PJ_include_path/degestiv.inc.php");
			exit;
		}
		
		// Auto-create personal group for new user
		if ($message == '') {
			$username = $data['username'];
			$user_id = $user->giveValue('id');
			
			// Create personal group with unique name to avoid conflicts
			$group_name = $username . '_personal';
			$group_data = array(
				'id' => null,
				'name' => $group_name,
				'description' => 'Personal group for ' . $username
			);
			
			$new_group = new Group($group_data);
			$group_message = $new_group->save();
			
			if ($group_message == '') {
				// Group created successfully - assign user to group
				$group_id = $new_group->giveValue('id');
				
				// Add user to the new group
				$db_group = new Database();
				$db_group->connect($_PJ_db_host, $_PJ_db_user, $_PJ_db_password, $_PJ_db_name);
				
				$insert_query = "INSERT INTO " . $_PJ_table_prefix . "user_group (user_id, group_id) VALUES ('$user_id', '$group_id')";
				$db_group->query($insert_query);
				
				debugLog('REGISTRATION', 'Auto-created group "' . $group_name . '" (ID: ' . $group_id . ') for user "' . $username . '" (ID: ' . $user_id . ')');
			} else {
				// Group creation failed - log but don't fail registration
				debugLog('REGISTRATION', 'Failed to create auto-group "' . $group_name . '" for user "' . $username . '": ' . $group_message);
			}
		}
		
		$success_message = 'Registration completed successfully! You can now login with your username and password.';
		$center_template = '';
		include("$_PJ_root/templates/note.ihtml");
		include_once("$_PJ_include_path/degestiv.inc.php");
		exit;
	}

	// Handle registration form submission (legacy full form registration)
	if (isset($register) && isset($altered)) {
		$data = array();
		$data['id'] = null; // New user
		$data['telephone'] = $_POST['telephone'] ?? '';
		$data['facsimile'] = $_POST['facsimile'] ?? '';
		$data['email'] = $_POST['email'] ?? '';
		$data['password'] = $_POST['password'] ?? '';
		$data['password_retype'] = $_POST['password_retype'] ?? '';
		// Handle secure group membership - filter out invalid groups and handle no-group option
		$selected_gids = isset($_POST['gids']) && is_array($_POST['gids']) ? $_POST['gids'] : array();
		$safe_gids = array();
		
		// Filter groups to only allow valid user groups from gids table or no group (0)
		foreach ($selected_gids as $gid) {
			$gid = intval($gid);
			if ($gid == 0) {
				// No group membership selected - this is the secure default
				continue; // Skip adding to gids (empty = no groups)
			} else {
				// Verify group exists in gids table (user-defined groups)
				$db_check = new Database();
				$db_check->connect();
				$db_check->query("SELECT id FROM " . $GLOBALS['_PJ_gid_table'] . " WHERE id = $gid");
				if ($db_check->next_record()) {
					$safe_gids[] = $gid; // Only add verified existing groups
				}
			}
		}
		
		$data['gids'] = implode(',', $safe_gids); // Empty string if no groups selected
		$data['lastname'] = add_slashes($_POST['lastname'] ?? '');
		$data['firstname'] = add_slashes($_POST['firstname'] ?? '');
		$data['username'] = add_slashes($_POST['login'] ?? '');
		$data['permissions'] = 'agent'; // Only allow agent permission for registration
		
		// Apply secure default permissions if enabled
		if (isset($_PJ_registration_secure_defaults) && $_PJ_registration_secure_defaults) {
			$data['allow_nc'] = 0; // Secure: not allowing new customers by default
			// Note: Additional access restrictions will be applied to customer/project creation
		} else {
			$data['allow_nc'] = 0; // Default to not allowing new customers
		}
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
			
			include("$_PJ_root/templates/user/register.ihtml.php");
			include_once("$_PJ_include_path/degestiv.inc.php");
			exit;
		}

		// Auto-create personal group for new user
		if ($message == '') {
			$username = $data['username'];
			$user_id = $new_user->giveValue('id');
			
			// Create personal group with unique name to avoid conflicts
			$group_name = $username . '_personal';
			$group_data = array(
				'id' => null,
				'name' => $group_name,
				'description' => 'Personal group for ' . $username
			);
			
			$new_group = new Group($group_data);
			$group_message = $new_group->save();
			
			if ($group_message == '') {
				// Group created successfully - assign user to group
				$group_id = $new_group->giveValue('id');
				
				// Add user to the new group
				$db = new Database();
				$db->connect($_PJ_db_host, $_PJ_db_user, $_PJ_db_password, $_PJ_db_name);
				
				$insert_query = "INSERT INTO " . $_PJ_table_prefix . "user_group (user_id, group_id) VALUES ('$user_id', '$group_id')";
				$db->query($insert_query);
				
				debugLog('REGISTRATION', 'Auto-created group "' . $group_name . '" (ID: ' . $group_id . ') for user "' . $username . '" (ID: ' . $user_id . ')');
			} else {
				// Group creation failed - log but don't fail registration
				debugLog('REGISTRATION', 'Failed to create auto-group "' . $group_name . '" for user "' . $username . '": ' . $group_message);
			}
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
		
		$center_template = ''; // Use direct message display in note.ihtml
		include("$_PJ_root/templates/note.ihtml.php");
		include_once("$_PJ_include_path/degestiv.inc.php");
		
		exit;
	}

	// Show registration form
	include("$_PJ_root/templates/user/register.ihtml.php");
	include_once("$_PJ_include_path/degestiv.inc.php");
?>