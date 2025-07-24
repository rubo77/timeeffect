<?php
	class Rates {
		var $rate_count = 0;
		var $data		= array();
		var $data_keys = array();
		var $data_pointer = 0;
		var $db; // Datenbankverbindung

		function Rates($data = '') {
			self::__construct($data);
		}
		function __construct($data = '') {
			if(!isset($this->db) or !is_object($this->db)) {
				$this->db = new Database;
			}

			if(is_array($data)) {
				$this->data = $data;
				$this->data_keys = array_keys($this->data);
				$this->data_pointer = 0;
				return;
			}

			$query = "SELECT * FROM " . $GLOBALS['_PJ_rate_table'];
			if(intval($data) > 0) {
				$query .= " WHERE customer_id=$data";
			}

			$this->db->query($query);

			$i = 0;
			while($this->db->next_record()) {
				$t_array = array();
				$t_array['id']			= $this->db->Record['id'];
				$t_array['price']		= $this->db->Record['price'];
				$t_array['name']		= $this->db->Record['name'];
				$t_array['currency']	= $this->db->Record['currency'];
				$this->data[$this->db->Record['id']] = $t_array;
				$this->rate_count++;
				++$i;
			}
			$this->data_keys = array_keys($this->data);
			$this->data_pointer = 0;
		}

		function resetList () {
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
			list($id, $key) = explode(".", $key);
			return $this->data[$id][$key];
		}

		function giveCount() {
			return $this->rate_count;
		}

		function save () {
			if(!isset($this->db) or !is_object($this->db)) {
				$this->db = new Database;
			}

			foreach ($this->data as $id=>$data) {
				if($data['name'] == '') {
					$query = "DELETE FROM " . $GLOBALS['_PJ_rate_table'] . " WHERE id='" . $data['id'] . "'";
				} else {
					$query = "REPLACE INTO " . $GLOBALS['_PJ_rate_table'] . " (id, customer_id, name, price, currency)";
					$query .= " VALUES(";
					if(empty($data['id'])) $query .= "NULL, ";
					else $query .= "'" . $data['id'] . "', ";
					$query .= $data['cid'] . ", ";
					$query .= "'" . $data['name'] . "', ";
					$query .= "'" . $data['price'] . "', ";
					$query .= "'" . $data['currency'] . "')";
				}
				$this->db->query($query);
			}
		}

		function delete() {
			if(!isset($this->db) or !is_object($this->db)) {
				$this->db = new Database;
			}

			if(!$this->data['id']) {
				return;
			}
			$query = "DELETE FROM " . $GLOBALS['_PJ_effort_table'] . " WHERE id=" . $this->data['id'];
			$this->db->query($query);
		}

	}
?>
