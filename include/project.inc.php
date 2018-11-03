<?php
	if(!isset($_PJ_root)) {
		print "<b>FEHLER:</b> \$_PJ_root ist <b>nicht festgelegt</b>! (" . __FILE__ . ", Zeile: " . __LINE__ . ")";
		exit;
	}

	class ProjectList {
		var $db;
		var $data = array();
		var $projects;
		var $show_closed = false;
		var $project_count	= 0;
		var $project_cursor	= -1;
		
		function ProjectList($customer, &$user, $show_closed = false, $limit = 1000) {
			self::__construct($customer, $user, $show_closed, $limit);
		}
		
		function __construct($customer, &$user, $show_closed = false, $limit = 1000) {
			$this->customer	= $customer;
			$this->user		= $user;
			$this->db = new Database;
			$this->showClosed($show_closed);

			if(!$user->checkPermission('admin')) {
				$access_query  = " AND (";
				$access_query .= " (user = '" . $user->giveValue('id') . "' AND access LIKE 'r________')";
				$access_query .= " OR ";
				$access_query .= " (gid IN (" . $user->giveValue('gids') . ") AND access LIKE '___r_____')";
				$access_query .= " OR ";
				$access_query .= " (access LIKE '______r__')";
				$access_query .= " ) ";
			}

			$query = "SELECT * FROM " . $GLOBALS['_PJ_project_table'];
			if(isset($customer) && is_object($customer) && $customer->giveValue('id')) {
				$query .= " WHERE customer_id = '" . $customer->giveValue('id') . "'";
				$order = " ORDER BY closed, project_name";
				$limit = "";
			} else {
				$this->db->query("SELECT id FROM " . $GLOBALS['_PJ_customer_table'] . " WHERE 1 $access_query");
				while($this->db->next_record()) {
					if(!empty($cids)) {
						$cids .= ',';
					}
					$cids .= $this->db->f('id');
				}
				if(empty($cids)) {
					return;
				}
				$query .= " WHERE customer_id IN ($cids)";
				if(isset($customer)) {
					$order = " ORDER BY customer_id, last DESC, project_name";
				} else {
					$order = " ORDER BY closed, last DESC, project_name";
				}
				if(!empty($limit)) {
					$sql_limit = " LIMIT $limit";
				}
			}
			if(!$this->showClosed()) {
				$query .= " AND closed = 'No'";
			}
			$query .= $access_query;
			$query .= $order . $sql_limit;

			$this->db->query($query);
			$this->projects = array();
			while($this->db->next_record()) {
				$this->projects[] = new Project($customer, $user, $this->db->Record);
				$this->project_count++;
				$project = $this->projects[$this->project_count-1];
				$this->data['seconds']			+= $project->giveValue('seconds');
				$this->data['minutes']			+= $project->giveValue('minutes');
				$this->data['hours']			+= $project->giveValue('hours');
				$this->data['days']				+= $project->giveValue('days');
				$this->data['billed_seconds']	+= $project->giveValue('billed_seconds');
				$this->data['billed_minutes']	+= $project->giveValue('billed_minutes');
				$this->data['billed_hours']		+= $project->giveValue('billed_hours');
				$this->data['billed_days']		+= $project->giveValue('billed_days');
				if($project->giveValue('project_budget')) {
					$this->data['budget']				+= $project->giveValue('project_budget');
					$this->data['remaining_budget']		+= ($project->giveValue('project_budget')-$project->giveValue('costs'));
					$this->data['costs_within_budget']	+= $project->giveValue('costs');
				} else {
					$this->data['additional_costs']		+= $project->giveValue('costs');
				}
				$this->data['costs']			+= $project->giveValue('costs');
			}
		}

		function showClosed($do = '') {
			if($do != '')
				$this->show_closed = $do;
			else
				return $this->show_closed;
		}

		function nextProject() {
			$this->project_cursor++;
			if($this->project_count == $this->project_cursor)
				return FALSE;
			return TRUE;
		}

		function reset() {
			$this->project_cursor = -1;
		}

		function giveProject() {
			return $this->projects[$this->project_cursor];
		}

		function giveValue($key) {
			return $this->data[$key];
		}
	}

	class Project extends Data {
		var $db;
		var $data;
		var $efforts;
		var $effort_count	= 0;
		var $effort_cursor	= -1;

		function Project(&$customer, &$user, $project = '') {
			self::__construct($customer, $user, $project);
		}
		
		function __construct(&$customer, &$user, $project = '') {
			$this->customer	= $customer;
			$this->user		= $user;
			if(is_array($project)) {
				$this->data = $project;
			} else if($project != '') {
				$this->load($project);
			}
			$this->user_access				= $this->getUserAccess();
			$this->loadEffort();
		}

		function load($id) {
			if(!isset($this->db) or !is_object($this->db)) {
				$this->db = new Database;
			}

			$query = "SELECT * FROM " . $GLOBALS['_PJ_project_table'] . " WHERE id='$id'";
			$this->db->query($query);
			if($this->db->next_record()) {
				$this->data = $this->db->Record;
			}
		}

		function count($billed = false) {
			if(!isset($this->db) or !is_object($this->db)) {
				$this->db = new Database;
			}

			$query = "SELECT COUNT(id) FROM " . $GLOBALS['_PJ_effort_table'] . " WHERE project_id='" . $this->data['id'] . "'";
			if(empty($billed)) {
					$query .= " AND billed IS NULL";
			}
			if(!$this->user->checkPermission('admin') && !$this->customer->giveValue('readforeignefforts')) {
				$query .= " AND " . $GLOBALS['_PJ_effort_table'] . ".user = '" . $this->user->giveValue('id') . "'";
			}
			$this->db->query($query);
			if($this->db->next_record()) {
				return $this->db->Record[0];
			}
		}

		function loadEffort () {
			if(!isset($this->db) or !is_object($this->db)) {
				$this->db = new Database;
			}

			if(!$this->data['id'])
				return;

			$rates			= new Rates($this->data['customer_id']);
			$effort_list	= new EffortList($this->customer, $this, $this->user);
			while($effort_list->nextEffort()) {
				$effort = $effort_list->giveEffort();
				$this->data['seconds']			+= $effort->giveValue('seconds');
				$this->data['minutes']			+= $effort->giveValue('minutes');
				$this->data['hours']			+= $effort->giveValue('hours');
				$this->data['days']				+= $effort->giveValue('days');
				$this->data['billed_seconds']	+= $effort->giveValue('billed_seconds');
				$this->data['billed_minutes']	+= $effort->giveValue('billed_minutes');
				$this->data['billed_hours']		+= $effort->giveValue('billed_hours');
				$this->data['billed_days']		+= $effort->giveValue('billed_days');
				$this->data['costs']			+= $effort->giveValue('hours') * $effort->giveValue('rate');
			}
		}

		function save () {
			if(!isset($this->db) or !is_object($this->db)) {
				$this->db = new Database;
			}

			$query = "REPLACE INTO " . $GLOBALS['_PJ_project_table'] . " (";

			if($this->data['id']) {
				$query .= "id, ";
			}
			$query .= "customer_id, user, gid, access, project_name, project_desc, project_budget, project_budget_currency, last, closed) VALUES(";
			if($this->data['id']) {
				$query .= $this->data['id'] . ", ";
			}
			$query .= $this->data['customer_id'] . ", ";
			$query .= "'" . $this->data['user'] . "', ";
			$query .= "'" . $this->data['gid'] . "', ";
			$query .= "'" . $this->data['access'] . "', ";
			$query .= "'" . $this->data['project_name'] . "', ";
			$query .= "'" . $this->data['project_desc'] . "', ";
			$query .= "'" . $this->data['project_budget'] . "', ";
			$query .= "'" . $this->data['project_budget_currency'] . "', ";
			$query .= "NOW(), ";
			$query .= "'" . $this->data['closed'] . "')";
			if($this->db->query($query)) {
				$this->data['id'] = $this->db->insert_id();
			}
		}

		function delete() {
			if(!isset($this->db) or !is_object($this->db)) {
				$this->db = new Database;
			}

			if(!$this->data['id']) {
				return;
			}
			$query = "DELETE FROM " . $GLOBALS['_PJ_project_table'] . " WHERE id=" . $this->data['id'];
			$this->db->query($query);
			$query = "DELETE FROM " . $GLOBALS['_PJ_effort_table'] . " WHERE project_id=" . $this->data['id'];
			$this->db->query($query);
		}

		function bill($from, $to, $date) {
			if(!$this->data['id'])
				return;

			$query = "UPDATE " . $GLOBALS['_PJ_effort_table'] .
					 " SET " . $GLOBALS['_PJ_effort_table'] . ".billed='$date'" .
					 " WHERE " . $GLOBALS['_PJ_effort_table'] . ".project_id=" . $this->data['id'] .
					 " AND " . $GLOBALS['_PJ_effort_table'] . ".date >= '$from'" .
					 " AND " . $GLOBALS['_PJ_effort_table'] . ".date <= '$to'";
			$this->db->query($query);
		}

		function unbill($from, $to) {
			if(!$this->data['id'])
				return;

			$query = "UPDATE " . $GLOBALS['_PJ_effort_table'] .
					 " SET " . $GLOBALS['_PJ_effort_table'] . ".billed IS NULL" .
					 " WHERE " . $GLOBALS['_PJ_effort_table'] . ".project_id=" . $this->data['id'] .
					 " AND " . $GLOBALS['_PJ_effort_table'] . ".date >= '$from'" .
					 " AND " . $GLOBALS['_PJ_effort_table'] . ".date <= '$to'";
			$this->db->query($query);
		}
	}
?>
