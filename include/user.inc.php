<?php
if(!isset($_PJ_root)) {
	print "<b>FEHLER:</b> \$_PJ_root ist <b>nicht festgelegt</b>! (" . __FILE__ . ", Zeile: " . __LINE__ . ")";
	exit;
}

// Include security layer
require_once(__DIR__ . '/security.inc.php');

class UserList {
	var $db;
	var $data;
	var $users;
	var $show_closed = false;
	var $user_count	= 0;
	var $user_cursor	= -1;
	var $projects = array();

	function __construct() {
		$this->db = new Database;

		$safeAuthTable = DatabaseSecurity::sanitizeColumnName($GLOBALS['_PJ_auth_table']);
		$query = "SELECT * FROM {$safeAuthTable}";
		$query .= " ORDER BY lastname";

		$this->db->query($query);
		$this->projects = array();
		while($this->db->next_record()) {
			$this->users[] = new User($this->db->Record);
			$this->user_count++;
		}
	}

	function reset() {
		$this->user_cursor = -1;
	}

	function nextUser() {
		$this->user_cursor++;
		if($this->user_count == $this->user_cursor)
			return FALSE;
		return TRUE;
	}

	function giveCount() {
		return $this->user_count;
	}

	function giveUser() {
		return $this->users[$this->user_cursor];
	}

	function giveValue($key) {
		if(isset($this->data[$key])) return $this->data[$key];
		else return null;
	}
}

class User {
	var $data = array();
	var $data_keys = array();
	var $data_pointer = 0;
	var $db; // Datenbankobjekt
	var $debug_exists; // Property für Debug-Ausgabe von exists()

	function __construct($data = '') {
		if(!isset($this->db) or !is_object($this->db)) {
			$this->db = new Database;
		}
		if(is_array($data)) {
			$this->data = $data;
			$this->data_keys = array_keys($this->data);
			$this->data_pointer = 0;
		} else {
			$this->load($data);
		}
		$this->loadGroups();
		$this->loadPermissionNames();
	}

	function load($id = '') {
		$safeAuthTable = DatabaseSecurity::sanitizeColumnName($GLOBALS['_PJ_auth_table']);
		$safeId = DatabaseSecurity::escapeInt($id);
		$query = "SELECT * FROM {$safeAuthTable} WHERE id={$safeId}";
		$this->db->query($query);

		if($this->db->next_record()) {
			$this->data = $this->db->Record;
			$this->data_keys = array_keys($this->data);
			$this->data_pointer = 0;
		}
	}

	function loadGroups() {
		if($this->data['gids'] == '') {
			$this->data['group_names'] = '';
			return;
		}
		$safeGidTable = DatabaseSecurity::sanitizeColumnName($GLOBALS['_PJ_gid_table']);
		// Parse the gids string and sanitize each ID
		$gidList = explode(',', $this->data['gids']);
		$safeGids = array_map([DatabaseSecurity::class, 'escapeInt'], $gidList);
		$gidsString = implode(',', $safeGids);
		
		$query = "SELECT name FROM {$safeGidTable} WHERE id IN ({$gidsString})";
		$this->db->query($query);

		while($this->db->next_record()) {
			if(isset($this->data['group_names'])) {
				$this->data['group_names'] .= ', ';
			}else $this->data['group_names']='';
			$this->data['group_names'] .= $this->db->f('name');
		}
	}

	function loadPermissionNames() {
		$user_perms = explode(',', $this->giveValue('permissions'));
		foreach($user_perms as $user_perm) {
			// Skip empty permission values
			if(empty($user_perm) || !isset($GLOBALS['_PJ_permission_names'][$user_perm])) {
				continue;
			}
			if(!empty($this->data['perm_names'])) {
				$this->data['perm_names'] .= ', ';
			} else $this->data['perm_names']='';
			$this->data['perm_names'] .= $GLOBALS['_PJ_permission_names'][$user_perm];
		}
	}

	function exists($username) {
		// FIX: Fehlende Formularfelder aus REQUEST übernehmen
		if (isset($_REQUEST['id']) && !isset($this->data['id'])) {
			$this->data['id'] = $_REQUEST['id'];
		}
		
		$id_condition = !empty($this->data['id']) ? " AND id <> '" . $this->data['id'] . "'" : "";
		// SQL injection protection: escape the username parameter
		$db = new Database();
		$db->connect();
		$safeUsername = DatabaseSecurity::escapeString($username, $db->Link_ID);
		$query = "SELECT * FROM " . $GLOBALS['_PJ_auth_table'] . " WHERE username='$safeUsername'" . $id_condition;
		$this->db->query($query);

		if($this->db->next_record()) {
			return true;
		}
		return false;
	}

	function retrieve($id, $value) {
		// SQL injection protection: escape the ID parameter
		$db = new Database();
		$db->connect();
		$safeId = DatabaseSecurity::escapeString($id, $db->Link_ID);
		$query = "SELECT $value FROM " . $GLOBALS['_PJ_auth_table'] . " WHERE id='$safeId'";
		$this->db->query($query);

		if($this->db->next_record()) {
			return $this->db->f($value);
		}
		return NULL;
	}

	function delete() {
		$query = "DELETE FROM " . $GLOBALS['_PJ_auth_table'] . " WHERE id='" . $this->giveValue('id') . "'";
		$this->db->query($query);

		if(!$this->Errno) {
			return true;
		}
		return false;
	}

	function save() {
		// Debug logging for firstname/lastname
		if(isset($GLOBALS['_PJ_debug']) && $GLOBALS['_PJ_debug']) {
			error_log("USER_SAVE_DEBUG: firstname in data: '" . ($this->data['firstname'] ?? 'NOT_SET') . "'");
			error_log("USER_SAVE_DEBUG: lastname in data: '" . ($this->data['lastname'] ?? 'NOT_SET') . "'");
			error_log("USER_SAVE_DEBUG: user data: " . print_r($this->data, true));
		}
		
		if(!isset($this->db) or !is_object($this->db)) {
			$this->db = new Database;
		}

		if($this->data['username'] == '') {
			return $GLOBALS['_PJ_strings']['error_user_empty'];
		}

		if($this->exists($this->data['username'])) {
			return $GLOBALS['_PJ_strings']['error_user_exists'];
		}

		// Clean mode-based password validation
		$mode = isset($this->data['mode']) ? $this->data['mode'] : 'new'; // Default to 'new' for registration
		if($mode === 'new') {
			// New user: password is required
			if($this->data['password'] == '') {
				return $GLOBALS['_PJ_strings']['error_pw_empty'];
			}
			if($this->data['password'] != $this->data['password_retype']) {
				return $GLOBALS['_PJ_strings']['error_pw_retype'];
			}
			$password = md5($this->data['password']);
		} else {
			// Edit mode: only validate password if it's being changed (not empty)
			if($this->data['password'] != '') {
				// Password is being changed
				if($this->data['password'] != $this->data['password_retype']) {
					return $GLOBALS['_PJ_strings']['error_pw_retype'];
				}
				$password = md5($this->data['password']);
			} else {
				// Password not being changed, keep existing password
				$password = $this->retrieve($this->data['id'], 'password');
			}
		}

		if($this->data['lastname'] == '') {
			return $GLOBALS['_PJ_strings']['error_name_empty'];
		}

		if($this->data['permissions'] == '') {
			return $GLOBALS['_PJ_strings']['error_perm_empty'];
		}

		// Allow empty gids for secure registration (no group membership)
		// Only require groups for admin users who need specific group assignments
		if(strpos($this->data['permissions'], 'admin') !== false && $this->data['gids'] == '') {
	        	return $GLOBALS['_PJ_strings']['error_gids_empty'];
		}

		// Check if migration columns exist in the database
        $migration_columns_exist = false;
        try {
            $check_query = "SHOW COLUMNS FROM " . $GLOBALS['_PJ_auth_table'] . " LIKE 'confirmed'";
            $this->db->query($check_query);
            $migration_columns_exist = $this->db->next_record();
        } catch (Exception $e) {
            // Migration columns don't exist, use original schema
            $migration_columns_exist = false;
        }
        
        if ($migration_columns_exist) {
            // Use new schema with migration columns
            $query = sprintf("REPLACE INTO %s (id, username, password, permissions, gids, allow_nc, firstname, lastname, email, telephone, facsimile, confirmed, confirmation_token) VALUES(%s, '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', %s)",
                             $GLOBALS['_PJ_auth_table'],
                             $this->data['id']?"'".$this->data['id']."'":"NULL",
                             $this->data['username'],
                             $password,
                             $this->data['permissions'],
                             $this->data['gids'],
                             $this->data['allow_nc'],
                             $this->data['firstname'],
                             $this->data['lastname'],
                             $this->data['email'],
                             $this->data['telephone'],
                             $this->data['facsimile'],
                             isset($this->data['confirmed']) ? $this->data['confirmed'] : 1,
                             isset($this->data['confirmation_token']) && $this->data['confirmation_token'] ? 
                             "'".$this->data['confirmation_token']."'" : "NULL"
                             );
        } else {
            // Use original schema without migration columns
            $query = sprintf("REPLACE INTO %s (id, username, password, permissions, gids, allow_nc, firstname, lastname, email, telephone, facsimile) VALUES(%s, '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')",
                             $GLOBALS['_PJ_auth_table'],
                             $this->data['id']?"'".$this->data['id']."'":"NULL",
                             $this->data['username'],
                             $password,
                             $this->data['permissions'],
                             $this->data['gids'],
                             $this->data['allow_nc'],
                             $this->data['firstname'],
                             $this->data['lastname'],
                             $this->data['email'],
                             $this->data['telephone'],
                             $this->data['facsimile']
                             );
        }

		$this->db->query($query);

		// Auto-create personal group for new users
		if(empty($this->data['id'])) {
			// Get the new user ID after insert
			$new_user_id = $this->db->insert_id();
			$this->data['id'] = $new_user_id; // Update local data
			
			$this->createPersonalGroup();
		}
	}

	/**
	 * Create a personal group for a new user and assign the user to it
	 * This ensures each user has their own group for secure default permissions
	 */
	function createPersonalGroup() {
		// Enable debug logging for group creation
		$GLOBALS['_PJ_debug'] = true;
		
		if(empty($this->data['username'])) {
			debugLog('CREATE_GROUP', 'ERROR: Cannot create group - username is empty');
			return; // Cannot create group without username
		}
		
		// Create personal group with unique name to avoid conflicts (consistent with registration)
		$group_name = $this->data['username'] . '_personal';
		$group_data = array(
			'id' => null, // New group
			'name' => $group_name,
			'description' => 'Personal group for user: ' . $this->data['username']
		);
		
		$new_group = new Group($group_data);
		debugLog('CREATE_GROUP', 'Creating group: ' . $group_name);
		$new_group->save(); // Group::save() returns void, not a result
		
		// Check if group was created by getting the ID
		$group_id = $new_group->giveValue('id');
		debugLog('CREATE_GROUP', 'Group ID after save: ' . ($group_id ?? 'NULL'));
		
		if($group_id) {
			// Group created successfully - Update user's gids to include the new personal group
			$current_gids = $this->data['gids'];
			$new_gids = empty($current_gids) ? $group_id : $current_gids . ',' . $group_id;
			debugLog('CREATE_GROUP', 'Assigning user to group. Current gids: "' . $current_gids . '", New gids: "' . $new_gids . '"');
			
			// Update the user record with the new group assignment (SQL injection protection)
			$this->db->connect(); // Ensure database connection
			$safeGids = DatabaseSecurity::escapeString($new_gids, $this->db->Link_ID);
			$safeUsername = DatabaseSecurity::escapeString($this->data['username'], $this->db->Link_ID);
			$update_query = sprintf("UPDATE %s SET gids='%s' WHERE username='%s'",
				$GLOBALS['_PJ_auth_table'],
				$safeGids,
				$safeUsername
			);
			debugLog("UPDATE GROUPS", $update_query);
			$this->db->query($update_query);
			
			// Update local data for consistency
			$this->data['gids'] = $new_gids;
			debugLog('CREATE_GROUP', 'User-group assignment completed. Final gids: "' . $this->data['gids'] . '"');
		} else {
			debugLog('CREATE_GROUP', 'ERROR: Group creation failed - no group ID returned');
		}
	}

	function reset() {
		reset($this->data);
		$this->data_keys = array_keys($this->data);
		$this->data_pointer = 0;
	}

	function giveNext() {
		// Fixed: replaced deprecated each() function with array iteration
		if ($this->data_pointer >= count($this->data_keys)) {
			return false;
		}
		$key = $this->data_keys[$this->data_pointer];
		$val = $this->data[$key];
		$this->data_pointer++;
		return $val;
	}

	function giveValue($key) {
		if(isset($this->data[$key])) 
			return $this->data[$key];
		else 
			return null;
	}
}
?>
