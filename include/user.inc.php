<?php
	if(!isset($_PJ_root)) {
		print "<b>FEHLER:</b> \$_PJ_root ist <b>nicht festgelegt</b>! (" . __FILE__ . ", Zeile: " . __LINE__ . ")";
		exit;
	}

	class UserList {
		var $db;
		var $data;
		var $users;
		var $show_closed = false;
		var $user_count	= 0;
		var $user_cursor	= -1;

		function UserList() {
			$this->db = new Database;

			$query = "SELECT * FROM " . $GLOBALS['_PJ_auth_table'];
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
			return $this->data[$key];
		}
	}

	class User {
		var $data = array();

		function User($data = '') {
			if(!is_object($this->db)) {
				$this->db = new Database;
			}
			if(is_array($data)) {
				$this->data = $data;
			} else {
				$this->load($data);
			}
			$this->loadGroups();
		}

		function load($id = '') {
			$query = "SELECT * FROM " . $GLOBALS['_PJ_auth_table'] . " WHERE id='$id'";
			$this->db->query($query);

			if($this->db->next_record()) {
				$this->data = $this->db->Record;
			}
		}

		function loadGroups() {
			if($this->data['gids'] == '') {
				$this->data['group_names'] = '';
				return;
			}
			$query = "SELECT name FROM " . $GLOBALS['_PJ_gid_table'] . " WHERE id IN (" . $this->data['gids'] . ")";;
			$this->db->query($query);

			while($this->db->next_record()) {
				if($this->data['group_names']) {
					$this->data['group_names'] .= ', ';
				}
				$this->data['group_names'] .= $this->db->f('name');
			}
		}
		function exists($username) {
			$query = "SELECT * FROM " . $GLOBALS['_PJ_auth_table'] . " WHERE username='$username' AND id <> '" . $this->data['id'] . "'";
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
			if(!is_object($this->db)) {
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

			if(strpos($this->data['permissions'], 'admin') === false && $this->data['gids'] == '') {
	        	return $GLOBALS['_PJ_strings']['error_gids_empty'];
	        }

	        $query = sprintf("REPLACE INTO %s (id, username, password, permissions, gids, allow_nc, firstname, lastname, email, telephone, facsimile) VALUES('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')",
	                         $GLOBALS['_PJ_auth_table'],
	                         $this->data['id'],
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

			$this->db->query($query);
		}

		function reset() {
			reset($this->data);
		}

		function giveNext() {
			list($key, $val) = each($this->data);
			return $val;
		}

		function giveValue($key) {
			return $this->data[$key];
		}
	}
?>