<?php
	if(!isset($_PJ_root)) {
		print "<b>FEHLER:</b> \$_PJ_root ist <b>nicht festgelegt</b>! (" . __FILE__ . ", Zeile: " . __LINE__ . ")";
		exit;
	}

	class GroupList {
		var $db;
		var $data;
		var $groups;
		var $group_count	= 0;
		var $group_cursor	= -1;

		function GroupList() {
			$this->db = new Database;

			$query = "SELECT * FROM " . $GLOBALS['_PJ_gid_table'];
			$query .= " ORDER BY name";

			$this->db->query($query);
			$this->projects = array();
			while($this->db->next_record()) {
				$this->groups[] = new Group($this->db->Record);
				$this->group_count++;
			}
		}

		function reset() {
			$this->group_cursor = -1;
		}

		function nextGroup() {
			$this->group_cursor++;
			if($this->group_count == $this->group_cursor)
				return FALSE;
			return TRUE;
		}

		function giveCount() {
			return $this->group_count;
		}

		function giveGroup() {
			return $this->groups[$this->group_cursor];
		}

		function giveValue($key) {
			return $this->data[$key];
		}
	}

	class Group {
		var $data = array();

		function Group($data = '') {
			if(!is_object($this->db)) {
				$this->db = new Database;
			}
			if(is_array($data)) {
				$this->data = $data;
				return;
			}

			$this->load($data);

		}

		function load($id = '') {
			$query = "SELECT * FROM " . $GLOBALS['_PJ_gid_table'] . " WHERE id='$id'";
			$this->db->query($query);

			if($this->db->next_record()) {
				$this->data = $this->db->Record;
			}
		}

		function exists($name) {
			$query = "SELECT * FROM " . $GLOBALS['_PJ_gid_table'] . " WHERE name='$name' AND id <> '" . $this->data['id'] . "'";
			$this->db->query($query);

			if($this->db->next_record()) {
				return true;
			}
			return false;
		}

		function retrieve($id, $value) {
			$query = "SELECT $value FROM " . $GLOBALS['_PJ_gid_table'] . " WHERE id='$id'";
			$this->db->query($query);

			if($this->db->next_record()) {
				return $this->db->f($value);;
			}
			return NULL;
		}

		function delete() {
			$query = "DELETE FROM " . $GLOBALS['_PJ_gid_table'] . " WHERE id='" . $this->giveValue('id') . "'";
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

			if($this->data['name'] == '') {
	        	return $GLOBALS['_PJ_strings']['error_group_empty'];
	        }

			if($this->exists($this->data['name'])) {
	        	return $GLOBALS['_PJ_strings']['error_group_exists'];
	        }

	        $query = sprintf("REPLACE INTO %s (id, name) VALUES('%s', '%s')",
	                         $GLOBALS['_PJ_gid_table'],
	                         $this->data['id'],
	                         $this->data['name']
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