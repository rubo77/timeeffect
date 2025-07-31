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
			include("$_PJ_root/templates/note.ihtml.php");
		} else {
			$error_message = $GLOBALS['_PJ_strings']['email_confirm_error'];
			include("$_PJ_root/templates/error.ihtml.php");
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