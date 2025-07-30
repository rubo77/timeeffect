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
			$query = "SELECT * FROM " . $GLOBALS['_PJ_auth_table'] . " WHERE username='$username'" . $id_condition;
			$this->db->query($query);

			if($this->db->next_record()) {
				return true;
			}
			return false;
		}

		function retrieve($id, $value) {
			$query = "SELECT $value FROM " . $GLOBALS['_PJ_auth_table'] . " WHERE id='$id'";
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
			if(!isset($this->db) or !is_object($this->db)) {
				$this->db = new Database;
			}

			if($this->data['username'] == '') {
	        	return $GLOBALS['_PJ_strings']['error_user_empty'];
	        }

			if($this->exists($this->data['username'])) {
	        	return $GLOBALS['_PJ_strings']['error_user_exists'];
	        }

			if($this->data['password'] == '') {
	        	return $GLOBALS['_PJ_strings']['error_pw_empty'];
	        }

	        if($this->data['password'] != $GLOBALS['_PJ_password_dummy']) {
	        	if($this->data['password'] != $this->data['password_retype']) {
	        		return $GLOBALS['_PJ_strings']['error_pw_retype'];
	        	}
	        	$password = md5($this->data['password']);
	        } else if($this->data['id']) {
				$password = $this->retrieve($this->data['id'], 'password');
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
			if(isset($this->data[$key])) return $this->data[$key];
else return null;
		}
	}
?>
