<?php
	class Rates {
		var $rate_count = 0;
		var $data		= array();

		function Rates($data = '') {
			if(!isset($this->db) or !is_object($this->db)) {
				$this->db = new Database;
			}

			if(is_array($data)) {
				$this->data = $data;
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
		}

		function resetList () {
			reset($this->data);
		}

		function giveNext() {
			list($key, $val) = each($this->data);
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

			reset($this->data);
			while(list($id, $data) = each($this->data)) {
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
