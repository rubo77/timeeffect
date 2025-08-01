<?php
	// Include secure defaults for new user permissions
	require_once(__DIR__ . '/secure_defaults.inc.php');
	// Include centralized ACL query functions
	require_once(__DIR__ . '/acl_query.inc.php');
	if(!isset($_PJ_root)) {
		print "<b>FEHLER:</b> \$_PJ_root ist <b>nicht festgelegt</b>! (" . __FILE__ . ", Zeile: " . __LINE__ . ")";
		exit;
	}

	// Include security layer
	require_once(__DIR__ . '/security.inc.php');

	class ProjectList {
		var $db;
		var $data = array();
		var $projects;
		var $show_closed = false;
		var $project_count	= 0;
		var $project_cursor	= -1;
		var $customer;
		var $user;
		
		function ProjectList($customer, &$user, $show_closed = false, $limit = 1000) {
			self::__construct($customer, $user, $show_closed, $limit);
		}
		
		function __construct($customer, &$user, $show_closed = false, $limit = 1000) {
			$this->customer	= $customer;
			$this->user		= $user;
			$this->db = new Database;
			$this->showClosed($show_closed);

			$access_query='';
			// Use centralized ACL query function
			$access_query = buildProjectAclQuery($user);

			$safeProjectTable = DatabaseSecurity::sanitizeColumnName($GLOBALS['_PJ_project_table']);
			$query = "SELECT * FROM {$safeProjectTable}";
			$sql_limit='';
			if(isset($customer) && is_object($customer) && $customer->giveValue('id')) {
				$safeCustomerId = DatabaseSecurity::escapeInt($customer->giveValue('id'));
				$query .= " WHERE customer_id = {$safeCustomerId}";
				$order = " ORDER BY closed, project_name";
				$limit = "";
			} else {
				$cids = ''; // Initialisierung von $cids
				$safeCustomerTable = DatabaseSecurity::sanitizeColumnName($GLOBALS['_PJ_customer_table']);
				$this->db->query("SELECT id FROM {$safeCustomerTable} WHERE 1 {$access_query}");
				while($this->db->next_record()) {
					if(!empty($cids)) {
						$cids .= ',';
					}
					$cids .= DatabaseSecurity::escapeInt($this->db->f('id'));
				}
				if(empty($cids)) {
					return;
				}
				$query .= " WHERE customer_id IN ({$cids})";
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
				@$this->data['seconds']			+= $project->giveValue('seconds');
				@$this->data['minutes']			+= $project->giveValue('minutes');
				@$this->data['hours']			+= $project->giveValue('hours');
				@$this->data['days']				+= $project->giveValue('days');
				@$this->data['billed_seconds']	+= $project->giveValue('billed_seconds');
				@$this->data['billed_minutes']	+= $project->giveValue('billed_minutes');
				@$this->data['billed_hours']		+= $project->giveValue('billed_hours');
				@$this->data['billed_days']		+= $project->giveValue('billed_days');
				if($project->giveValue('project_budget')) {
					@$this->data['budget']				+= $project->giveValue('project_budget');
					@$this->data['remaining_budget']		+= ($project->giveValue('project_budget')-$project->giveValue('costs'));
					@$this->data['costs_within_budget']	+= $project->giveValue('costs');
				} else {
					@$this->data['additional_costs']		+= $project->giveValue('costs');
				}
				@$this->data['costs']			+= $project->giveValue('costs');
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
			if(isset($this->data[$key])) return $this->data[$key];
else return null;
		}
	}

	class Project extends Data {
		var $customer; // Customer-Objekt Referenz
		var $user; // User-Objekt Referenz
		var $user_access; // Zugriffsrechte des Users
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
			} else {
				// LOG_PROJECT_INIT: Initialize empty project with required fields
				$this->data = array();
				$this->data['id'] = '';
				$this->data['access'] = 'rwxr--r--'; // Default access: owner read/write, group read, world read
				$this->data['user'] = $user ? $user->giveValue('id') : '';
				$this->data['gid'] = $user ? $user->giveValue('gid') : '';
				debugLog("LOG_PROJECT_INIT", "Initialized empty project with default access for user: " . ($user ? $user->giveValue('id') : 'no_user'));
			}
			// Always call getUserAccess() - now safe because access field is always set
			$this->user_access = $this->getUserAccess();
			$this->loadEffort();
		}

		function load($id) {
			if(!isset($this->db) or !is_object($this->db)) {
				$this->db = new Database;
			}
			// Ensure database connection is established
			$this->db->connect();

			// SQL injection protection: escape the ID parameter
			$safeId = DatabaseSecurity::escapeString($id, $this->db->Link_ID);
			$query = "SELECT * FROM " . $GLOBALS['_PJ_project_table'] . " WHERE id='$safeId'";
			$this->db->query($query);
			if($this->db->next_record()) {
				$this->data = $this->db->Record;
			}
		}

		function count($billed = false) {
			if(!isset($this->db) or !is_object($this->db)) {
				$this->db = new Database;
			}
			// Ensure database connection is established
			$this->db->connect();

			// SQL injection protection: escape the project ID parameter
			$safeProjectId = DatabaseSecurity::escapeString($this->data['id'], $this->db->Link_ID);
			$query = "SELECT COUNT(id) FROM " . $GLOBALS['_PJ_effort_table'] . " WHERE project_id='$safeProjectId'";
			if(empty($billed)) {
					$query .= " AND billed IS NULL";
			}
			// LOG_PROJECT_COUNT: Check customer object before accessing readforeignefforts
			if(!$this->user->checkPermission('admin') && $this->customer && !$this->customer->giveValue('readforeignefforts')) {
				debugLog("LOG_PROJECT_COUNT", "Restricting to own efforts for project " . $this->data['id']);
				$query .= " AND " . $GLOBALS['_PJ_effort_table'] . ".user = '" . $this->user->giveValue('id') . "'";
			} elseif (!$this->customer) {
				debugLog("LOG_PROJECT_COUNT", "No customer object available for project " . $this->data['id'] . ", allowing all efforts");
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

			if(!isset($this->data) || !isset($this->data['id']) || !$this->data['id'])
				return;

			$rates			= new Rates($this->data['customer_id']);
			$effort_list	= new EffortList($this->customer, $this, $this->user);
			while($effort_list->nextEffort()) {
				$effort = $effort_list->giveEffort();
				@$this->data['seconds']			+= $effort->giveValue('seconds');
				@$this->data['minutes']			+= $effort->giveValue('minutes');
				@$this->data['hours']			+= $effort->giveValue('hours');
				@$this->data['days']				+= $effort->giveValue('days');
				@$this->data['billed_seconds']	+= $effort->giveValue('billed_seconds');
				@$this->data['billed_minutes']	+= $effort->giveValue('billed_minutes');
				@$this->data['billed_hours']		+= $effort->giveValue('billed_hours');
				@$this->data['billed_days']		+= $effort->giveValue('billed_days');
				@$this->data['costs']			+= $effort->giveValue('hours') * $effort->giveValue('rate');
			}
		}

		function save () {
			if(!isset($this->db) or !is_object($this->db)) {
				$this->db = new Database;
			}

			// Apply secure defaults for new projects if enabled
			if (isSecureDefaultsEnabled() && !isset($this->data['id'])) {
				global $_PJ_auth;
				if (isset($_PJ_auth) && is_object($_PJ_auth)) {
					$current_user_id = $_PJ_auth->giveValue('id');
					$current_group_id = $_PJ_auth->giveValue('gid');
					
					// Apply secure project defaults
					$this->data = applySecureProjectDefaults($this->data, $current_user_id, $current_group_id);
					
					// Log security action for audit
					logSecurityAction('project_created', $current_user_id, $this->data);
				}
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
