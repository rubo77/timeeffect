<?php
	class CustomerList {
		var $db;
		var $data;
		var $customers;
		var $customer_count	= 0;
		var $customer_cursor	= -1;

		function CustomerList($inactive = '') {
			global $_PJ_customer_table;

			$this->db = new Database;
			$query = "SELECT * FROM $_PJ_customer_table";
			if(!$inactive) {
				$query .= " WHERE active='yes'";
			}
			$query .= " ORDER BY customer_name";

			$this->db->query($query);
			$this->customers = array();
			while($this->db->next_record()) {
				$this->customers[] = new Customer($this->db->Record);
				$this->customer_count++;
				$customer = $this->customers[$this->customer_count-1];
			}

			$this->inactive_count = 0;
			$query = "SELECT count(id) FROM $_PJ_customer_table WHERE active='no'";
			$this->db->query($query);
			if($this->db->next_record()) {
				$this->inactive_count = $this->db->Record[0];
			}
		}

		function nextCustomer() {
			$this->customer_cursor++;
			if($this->customer_count == $this->customer_cursor)
				return FALSE;
			return TRUE;
		}

		function giveCustomer() {
			return $this->customers[$this->customer_cursor];
		}
	}

	class Customer extends Data {
		var $db;
		var $data;

		function Customer($customer = '') {
			if(is_array($customer)) {
				$this->data = $customer;
			} else if($customer != '') {
				$this->load($customer);
			}
		}

		function load($id) {
			if(!is_object($this->db)) {
				$this->db = new Database;
			}

			$query = "SELECT * FROM " . $GLOBALS['_PJ_customer_table'] . " WHERE id='$id'";
			$this->db->query($query);
			if($this->db->next_record()) {
				$this->data = $this->db->Record;
			}
			$query = "SELECT COUNT(id) FROM " . $GLOBALS['_PJ_project_table'] . " WHERE customer_id='$id'";
			if($this->db->next_record()) {
				print $this->db->Record[0];
			}
		}

		function count($closed = false) {
			if(!is_object($this->db)) {
				$this->db = new Database;
			}

			$query = "SELECT COUNT(id) FROM " . $GLOBALS['_PJ_project_table'] . " WHERE customer_id='" . $this->data['id'] . "'";
			if(!$closed) {
					$query .= " AND closed = 'No'";
			}
			$this->db->query($query);
			if($this->db->next_record()) {
				return $this->db->Record[0];
			}
		}

		function loadEffort () {
			if(!$this->data['id'] || ($this->data['seconds'] > 0))
				return;

			$project_list = new ProjectList($this->data['id']);
			while($project_list->nextProject()) {
				$project = $project_list->giveProject();
				$this->data['seconds']	+= $project->giveValue('seconds');
				$this->data['minutes']	+= $project->giveValue('minutes');
				$this->data['hours']	+= $project->giveValue('hours');
				$this->data['days']		+= $project->giveValue('days');
				$this->data['billed_seconds']	+= $project->giveValue('billed_seconds');
				$this->data['billed_minutes']	+= $project->giveValue('billed_minutes');
				$this->data['billed_hours']		+= $project->giveValue('billed_hours');
				$this->data['billed_days']		+= $project->giveValue('billed_days');
			}
		}

		function save () {
			if(!is_object($this->db)) {
				$this->db = new Database;
			}

			$query = "REPLACE INTO " . $GLOBALS['_PJ_customer_table'] . " (";

			if($this->data['id']) {
				$query .= "id, ";
			}
			$query .= "active, customer_name, customer_desc, customer_budget, customer_budget_currency, customer_logo) VALUES(";
			if($this->data['id']) {
				$query .= $this->data['id'] . ", ";
			}
			$query .= "'" . $this->data['active'] . "', ";
			$query .= "'" . $this->data['customer_name'] . "', ";
			$query .= "'" . $this->data['customer_desc'] . "', ";
			$query .= "'" . $this->data['customer_budget'] . "', ";
			$query .= "'" . $this->data['customer_budget_currency'] . "', ";
			$query .= "'" . $this->data['customer_logo'] . "')";
			if($this->db->query($query)) {
				$this->data['id'] = $this->db->insert_id();
			}
		}

		function delete() {
			if(!is_object($this->db)) {
				$this->db = new Database;
			}

			if(!$this->data['id']) {
				return;
			}

			$query = "DELETE FROM " . $GLOBALS['_PJ_customer_table'] . " WHERE id=" . $this->data['id'];
			$project_list = new ProjectList($this->data['id']);
			while($project_list->nextProject()) {
				$project = $project_list->giveProject();
				$project->delete();
			}
			$this->db->query($query);
		}

		function bill($from, $to, $date) {
			if(!is_object($this->db)) {
				$this->db = new Database;
			}

			if(!$this->data['id']) {
				return;
			}

			$query = "SELECT id FROM " . $GLOBALS['_PJ_project_table'] .
					  " WHERE customer_id=" . $this->data['id'];

			$this->db->query($query);
			while($this->db->next_record()) {
				$projects = new Project($this->db->Record);
				$projects->bill($from, $to, $date);
			}
		}

		function unbill($from, $to) {
			if(!is_object($this->db)) {
				$this->db = new Database;
			}

			if(!$this->data['id']) {
				return;
			}

			$query = "SELECT id FROM " . $GLOBALS['_PJ_project_table'] .
					  " WHERE customer_id=" . $this->data['id'];

			$this->db->query($query);
			while($this->db->next_record()) {
				$projects = new Project($this->db->Record);
				$projects->unbill($from, $to, $date);
			}
		}
	}
?>