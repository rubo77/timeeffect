<?php
	// Include centralized ACL query functions
	require_once(__DIR__ . '/acl_query.inc.php');
	if(!isset($_PJ_root)) {
		print "<b>FEHLER:</b> \$_PJ_root ist <b>nicht festgelegt</b>! (" . __FILE__ . ", Zeile: " . __LINE__ . ")";
		exit;
	}

	// Include security layer
	require_once(__DIR__ . '/security.inc.php');

	class OpenEfforts {
		var $__effort_count	= 0;
		var $__effort_cursor	= -1;
		var $__user; // Deklaration der vorher dynamischen Property
		var $__db; // Deklaration der vorher dynamischen Property
		var $__efforts = array(); // Array für Efforts

		function OpenEfforts(&$_user) {
			self::__construct($_user);
		}
		function __construct(&$_user) {
			$this->__user		= $_user;
			$this->__db = new Database;
			// Use centralized ACL query functions
			$access_query = buildEffortAclQuery($_user, $GLOBALS['_PJ_effort_table']);
			$raw_access_query = buildRawAclQuery($_user, 'r');
			$this->__db->query("SELECT id FROM " . $GLOBALS['_PJ_customer_table'] . " WHERE 1 $raw_access_query");
			while($this->__db->next_record()) {
				if(!empty($cids)) {
					$cids .= ',';
				} else $cids='';
				$cids .= $this->__db->f('id');
			}
			if(empty($cids)) {
				return;
			}
			$this->__db->query("SELECT id FROM " . $GLOBALS['_PJ_project_table'] . " WHERE customer_id IN ($cids) $raw_access_query");
			while($this->__db->next_record()) {
				if(!empty($pids)) {
					$pids .= ',';
				} else $pids='';
				$pids .= $this->__db->f('id');
			}
			if(empty($pids)) {
				return;
			}
			$query  = "SELECT "	. $GLOBALS['_PJ_effort_table'] . ".* ";
			$query .= " FROM "	. $GLOBALS['_PJ_effort_table'];
			$query .= " WHERE (project_id IN ($pids) OR project_id = 0)";
			$query .= " AND `billed` IS NULL";
			$query .= " AND `begin` = `END`";
			$order_query = ' ORDER BY date DESC, begin, last DESC';
			$limit_query = ' LIMIT 1000';

			$this->__db->query($query);
			$this->__efforts = array();
			while($this->__db->next_record()) {
				$this->__efforts[] = new Effort($this->__db->Record, $this->__user);
				$this->__effort_count++;
			}
		}

		function nextEffort() {
			$this->__effort_cursor++;
			if($this->__effort_count == $this->__effort_cursor)
				return false;
			return true;
		}

		function reset() {
			$this->__effort_cursor = -1;
		}

		function effortCount() {
			return $this->__effort_count;
		}

		function giveEffort() {
			return $this->__efforts[$this->__effort_cursor];
		}

	}

	class EffortList {
		var $db;
		var $data;
		var $efforts;
		var $show_billed = false;
		var $effort_count	= 0;
		var $effort_cursor	= -1;
		var $customer; // Customer-Objekt Referenz
		var $project; // Project-Objekt Referenz
		var $user; // User-Objekt Referenz

		function EffortList(&$customer, &$project, &$user, $show_billed = false, $limit = NULL) {
			self::__construct($customer, $project, $user, $show_billed, $limit);
		}
		
		function __construct(&$customer, &$project, &$user, $show_billed = false, $limit = NULL, $sort_order = 'desc') {
			$this->customer	= $customer;
			$this->project	= $project;
			$this->user		= $user;
			$this->db = new Database;
			$this->showBilled($show_billed);

			$access_query='';
			// Use centralized ACL query functions
			$access_query = buildEffortAclQuery($user, $GLOBALS['_PJ_effort_table']);
			$raw_access_query = buildRawAclQuery($user, 'r');

			$safeEffortTable = DatabaseSecurity::sanitizeColumnName($GLOBALS['_PJ_effort_table']);
			$safeProjectTable = DatabaseSecurity::sanitizeColumnName($GLOBALS['_PJ_project_table']);
			$safeCustomerTable = DatabaseSecurity::sanitizeColumnName($GLOBALS['_PJ_customer_table']);
			
			// Use LEFT JOIN to include efforts without project (project_id = 0)
			$query  = "SELECT {$safeEffortTable}.*, {$safeProjectTable}.project_name, {$safeCustomerTable}.customer_name ";
			$query .= " FROM {$safeEffortTable}";
			$query .= " LEFT JOIN {$safeProjectTable} ON {$safeEffortTable}.project_id = {$safeProjectTable}.id";
			$query .= " LEFT JOIN {$safeCustomerTable} ON {$safeProjectTable}.customer_id = {$safeCustomerTable}.id";
			$query .= " WHERE 1=1"; // Always true condition to allow adding AND clauses
			if(isset($project) && is_object($project) && $project->giveValue('id')) {
				$safeProjectId = DatabaseSecurity::escapeInt($project->giveValue('id'));
				$query .= " AND project_id={$safeProjectId}";
				$sort_direction = ($sort_order === 'asc') ? 'ASC' : 'DESC';
				$order_query = " ORDER BY billed, date $sort_direction, begin $sort_direction";
				$limit_query = '';
			} else if(isset($customer) && is_object($customer) && $customer->giveValue('id')) {
				$safeCustomerId = DatabaseSecurity::escapeInt($customer->giveValue('id'));
				// Filter by customer, but also include efforts without project (project_id = 0)
				$query .= " AND ({$safeCustomerTable}.id={$safeCustomerId} OR {$safeEffortTable}.project_id = 0)";
				$order_query = ' ORDER BY billed, last DESC, date, begin';
				$limit_query = ' LIMIT 1000';
			} else {
				$this->db->query("SELECT id FROM {$safeCustomerTable} WHERE 1 $raw_access_query");
				$cids = '';
				while($this->db->next_record()) {
					if(!empty($cids)) {
						$cids .= ',';
					}
					$cids .= $this->db->f('id');
				}
				if(empty($cids)) {
					return;
				}
				$this->db->query("SELECT id FROM " . $GLOBALS['_PJ_project_table'] . " WHERE customer_id IN ($cids) $raw_access_query");
				$pids = '';
				while($this->db->next_record()) {
					if(!empty($pids)) {
						$pids .= ',';
					}
					$pids .= $this->db->f('id');
				}
				if(empty($pids)) {
					// If no projects found, only show efforts without project (project_id = 0)
					$query .= " AND project_id = 0";
				} else {
					// Include both efforts with valid project_id AND efforts without project (project_id = 0)
					$query .= " AND (project_id IN ($pids) OR project_id = 0)";
				}
				// Sort by project_id first, then by customer_id within project, then by date
				$order_query = ' ORDER BY billed, project_id, ' . $GLOBALS['_PJ_customer_table'] . '.id, date DESC, begin DESC, last DESC';
				$limit_query = ' LIMIT 1000';
			}
			if(!empty($limit)) {
				$limit_query = ' LIMIT ' . $limit;
			}
			if(!$this->show_billed) {
				$query .= " AND (billed IS NULL OR billed = '0000-00-00')";
			}
			if(!$this->user->checkPermission('admin')) {
				$query .= " AND (" . $GLOBALS['_PJ_customer_table'] . ".readforeignefforts = 1 OR " . $GLOBALS['_PJ_effort_table'] . ".user = '" . $this->user->giveValue('id') . "')";
			}
			$query .= $access_query . $order_query . $limit_query;

			$this->db->query($query);
			$this->efforts = array();
			while($this->db->next_record()) {
				$this->efforts[] = new Effort($this->db->Record, $this->user);
				$this->effort_count++;
			}
		}

		function showBilled($do = '') {
			if($do != '')
				$this->show_billed = $do;
			else
				return $this->show_billed;
		}

		function nextEffort() {
			$this->effort_cursor++;
			if($this->effort_count == $this->effort_cursor)
				return FALSE;
			return TRUE;
		}

		function reset() {
			$this->effort_cursor = -1;
		}

		function effortCount() {
			return $this->effort_count;
		}

		function giveEffort() {
			return $this->efforts[$this->effort_cursor];
		}

		function billEfforts($date, $ids) {
			if(!isset($this->db) or !is_object($this->db)) {
				$this->db = new Database;
			}
			$query = "UPDATE " . $GLOBALS['_PJ_effort_table'] . " SET BILLED = '$date' WHERE id IN ($ids)";
			$this->db->query($query);
		}

		function getTotalHours() {
			$total_hours = 0;
			foreach($this->efforts as $effort) {
				$total_hours += $effort->giveValue('hours');
			}
			return $total_hours;
		}

		function getTotalCosts() {
			$total_costs = 0;
			foreach($this->efforts as $effort) {
				$total_costs += $effort->giveValue('costs');
			}
			return $total_costs;
		}

		function getTotalDays() {
			$total_days = 0;
			foreach($this->efforts as $effort) {
				$total_days += $effort->giveValue('days');
			}
			return $total_days;
		}
	}

	class Effort extends Data {
		var $db;
		var $data;
		var $user;
		var $user_access;
		
		function Effort($effort, &$user) {
			self::__construct($effort, $user);
		}
		function __construct($effort, &$user) {
			$this->user = $user;
			if(is_array($effort)) {
				$this->data = $effort;
			} else if($effort != '') {
				$this->load($effort);
			} else {
				// LOG_EFFORT_INIT: Initialize empty effort with required fields
				$this->data = array();
				$this->data['id'] = '';
				$this->data['access'] = 'rwxr--r--'; // Default access: owner read/write, group read, world read
				$this->data['user'] = $user ? $user->giveValue('id') : '';
				$this->data['gid'] = $user ? $user->giveValue('gid') : '';
				debugLog("LOG_EFFORT_INIT", "Initialized empty effort with default access for user: " . ($user ? $user->giveValue('id') : 'no_user'));
			}
			// Always call getUserAccess() - now safe because access field is always set
			$this->user_access = $this->getUserAccess();
			$this->initEffort();
		}

		function load($id) {
			if(!isset($this->db) or !is_object($this->db)) {
				$this->db = new Database;
			}
			// Ensure database connection is established
			$this->db->connect();

			// SQL injection protection: escape the ID parameter
			$safeId = DatabaseSecurity::escapeString($id, $this->db->Link_ID);
			$query = "SELECT * FROM " . $GLOBALS['_PJ_effort_table'] . " WHERE id='$safeId'";
			$this->db->query($query);
			if($this->db->next_record()) {
				$this->data = $this->db->Record;
				// LOG_EFFORT_LOAD: Ensure access field is never null after loading from database
				if(empty($this->data['access']) || $this->data['access'] === null) {
					$this->data['access'] = 'rwxr--r--'; // Default access: owner read/write, group read, world read
					debugLog("LOG_EFFORT_LOAD", "Fixed null access field for effort ID: $id, set to default access");
				}
			}
		}

		function initEffort () {
			// Prüfen, ob Datums- und Zeitwerte vorhanden sind, um explode()-Fehler zu vermeiden
			if (empty($this->data['date'])) {
				$this->data['date'] = date("Y-m-d");
			}
			if (empty($this->data['begin'])) {
				$this->data['begin'] = "00:00:00";
			}
			if (empty($this->data['end'])) {
				$this->data['end'] = "00:00:00";
			}

			// Sicherstellen, dass wir Strings für explode() verwenden
			list($year, $month, $day) = explode("-", (string)$this->data['date']);
			list($b_hour, $b_minute, $b_second) = explode(":", (string)$this->data['begin']);
			list($e_hour, $e_minute, $e_second) = explode(":", (string)$this->data['end']);

			// Typsicherheit für numerische Operationen
			$b_hour = (int)$b_hour;
			$b_minute = (int)$b_minute;
			$b_second = (int)$b_second;
			$e_hour = (int)$e_hour;
			$e_minute = (int)$e_minute;
			$e_second = (int)$e_second;
			$year = (int)$year;
			$month = (int)$month;
			$day = (int)$day;

			// Zeiten berechnen
			$b_time = mktime($b_hour, $b_minute, $b_second, $month, $day, $year);
			$e_time = mktime($e_hour, $e_minute, $e_second, $month, $day, $year);

			// Sicherstellen, dass der 'billed' Schlüssel existiert
			if(!isset($this->data['billed'])) {
				$this->data['billed'] = '';
			}

			if($this->data['billed'] != '') {
				$this->data['billed_seconds']	= ($e_time - $b_time);
				$this->data['billed_minutes']	= round($this->data['billed_seconds']	/ 60, 0);
				$this->data['billed_hours']		= round($this->data['billed_seconds']	/ 3600, 2);
				$this->data['billed_days']		= round($this->data['billed_seconds']	/ 28800, 2);
			}
			// Sicherstellen, dass der 'rate' Schlüssel existiert
			if(!isset($this->data['rate'])) {
				$this->data['rate'] = 0;
			}

			$this->data['seconds']	= ($e_time - $b_time);
			$this->data['minutes']	= round($this->data['seconds']	/ 60, 0);
			$this->data['hours']	= round($this->data['seconds']	/ 3600, 2);
			$this->data['days']		= round($this->data['seconds']	/ 28800, 2);
			$this->data['costs']	= $this->data['hours'] * $this->data['rate'];
		}

		function checkDuplicate() {
			if(!isset($this->db) or !is_object($this->db)) {
				$this->db = new Database;
			}

			// Ensure database connection is established
			if(empty($this->db->Link_ID)) {
				$this->db->connect(
					$GLOBALS['_PJ_db_database'],
					$GLOBALS['_PJ_db_host'],
					$GLOBALS['_PJ_db_user'],
					$GLOBALS['_PJ_db_password']
				);
			}

			// Only check for duplicates when creating a new effort (no ID yet)
			if(!empty($this->data['id'])) {
				return false;
			}

			$safeTable = DatabaseSecurity::sanitizeColumnName($GLOBALS['_PJ_effort_table']);
			// Handle undefined project_id for new efforts without project
			$projectId = isset($this->data['project_id']) ? $this->data['project_id'] : '';
			$safeProjectId = DatabaseSecurity::escapeString($projectId, $this->db->Link_ID);
			$safeDate = DatabaseSecurity::escapeString($this->data['date'], $this->db->Link_ID);
			$safeBegin = DatabaseSecurity::escapeString($this->data['begin'], $this->db->Link_ID);
			$safeDescription = DatabaseSecurity::escapeString($this->data['description'], $this->db->Link_ID);
			$safeUser = DatabaseSecurity::escapeString($this->data['user'], $this->db->Link_ID);
			
			$query = "SELECT id FROM {$safeTable} WHERE ";
			$query .= "project_id = '{$safeProjectId}' AND ";
			$query .= "date = '{$safeDate}' AND ";
			$query .= "begin = '{$safeBegin}' AND ";
			$query .= "description = '{$safeDescription}' AND ";
			$query .= "user = '{$safeUser}'";

			$this->db->query($query);
			
			if($this->db->next_record()) {
				return true; // Duplicate found
			}
			
			return false; // No duplicate found
		}

		function save () {
			if(!isset($this->db) or !is_object($this->db)) {
				$this->db = new Database;
			}

			// Check for duplicates before saving
			if($this->checkDuplicate()) {
				return $GLOBALS['_PJ_strings']['error_effort_duplicate'];
			}

			list($year, $month, $day) = explode("-", $this->data['date']);
			list($b_hour, $b_minute, $b_second) = explode(":", $this->data['begin']);
			list($e_hour, $e_minute, $e_second) = explode(":", $this->data['end']);

			$b_timestamp = mktime($b_hour+1-1, $b_minute, $b_second, $month, $day, $year+1-1);
			$e_timestamp = mktime($e_hour+1-1, $e_minute, $e_second, $month, $day, $year+1-1);

			if((date("Y", $b_timestamp) <= 1970) ||
			   (date("Y", $e_timestamp) <= 1970	))
				return '';

			if($this->data['billed'] == '') {
				$this->data['billed'] = 'NULL';
			}
			$timestamp_diff = $e_timestamp - $b_timestamp;
			if($timestamp_diff < 0 && $this->data['end'] != '00:00:00') {
				$b_time	= "00:00:00";
				$date	= date("Y-m-d", $b_timestamp+86400);
				$e_time	= date("H:i:s", $e_timestamp);
				if($b_time == $e_time) {
					$e_time	= date("H:i:s", $e_timestamp-1);
				}

				$query = "INSERT INTO " . $GLOBALS['_PJ_effort_table'] . " (project_id, gid, access, date, begin, end, description, note, rate, user, billed, last)";
				$query .= " VALUES(";
				// Handle project_id as integer or 0 (database default) to prevent MySQL constraint errors
				if(!empty($this->data['project_id']) && is_numeric($this->data['project_id'])) {
					$query .= "'" . $this->data['project_id'] . "', ";
				} else {
					$query .= "'0', ";
				}
				$query .= "'" . $this->data['gid'] . "', ";
				$query .= "'" . $this->data['access'] . "', ";
				$query .= "'" . $date . "', ";
				$query .= "'" . $b_time . "', ";
				$query .= "'" . $e_time . "', ";
				$query .= "'" . $this->data['description'] . "', ";
				$query .= "'" . $this->data['note'] . "', ";
				$query .= "'" . $this->data['rate'] . "', ";
				$query .= "'" . $this->data['user'] . "', ";
				$query .= $this->data['billed'] . ", ";
				$query .= "NOW())";
				$this->db->query($query);

				$b_time	= date("H:i:s", $b_timestamp);
				$e_time	= "23:59:59";
			} else {
				$b_time = date("H:i:s", $b_timestamp);
				$e_time = date("H:i:s", $e_timestamp);
				if($b_time != '00:00:00' && $e_time == '00:00:00') {
					$e_time = '23:59:59';
				}
			}

			$query = "REPLACE INTO " . $GLOBALS['_PJ_effort_table'] . " (id, project_id, gid, access, date, begin, end, description, note, rate, user, billed)";
			$query .= " VALUES(";
			if(empty($this->data['id'])) $query .= "NULL, ";
			else $query .= "'" . $this->data['id'] . "', ";
			// Handle project_id as integer or 0 (database default) to prevent MySQL constraint errors
			if(!empty($this->data['project_id']) && is_numeric($this->data['project_id'])) {
				$query .= "'" . $this->data['project_id'] . "', ";
			} else {
				$query .= "'0', ";
			}
			$query .= "'" . $this->data['gid'] . "', ";
			$query .= "'" . $this->data['access'] . "', ";
			$query .= "'" . $this->data['date'] . "', ";
			$query .= "'" . $b_time . "', ";
			$query .= "'" . $e_time . "', ";
			$query .= "'" . addslashes($this->data['description']) . "', ";
			$query .= "'" . $this->data['note'] . "', ";
			$query .= "'" . $this->data['rate'] . "', ";
			$query .= "'" . $this->data['user'] . "', ";
			$query .= $this->data['billed'] . ")";

			$this->db->query($query);

			// Only update project timestamp if we have a valid project_id
			if(isset($this->data['project_id']) && !empty($this->data['project_id']) && $this->data['project_id'] !== '0') {
				// SQL injection protection: escape the project ID parameter
				$safeProjectId = DatabaseSecurity::escapeString($this->data['project_id'], $this->db->Link_ID);
				$query = "UPDATE " . $GLOBALS['_PJ_project_table'] . " SET last=NOW() WHERE id='$safeProjectId'";
				$this->db->query($query);
			}
			
			return ''; // Success - no error message
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

		function setEndTime($effort) {
			$date_parts = explode("-", $this->data['date']);
			$year = isset($date_parts[0]) ? $date_parts[0] : date('Y');
			$month = isset($date_parts[1]) ? $date_parts[1] : date('m');
			$day = isset($date_parts[2]) ? $date_parts[2] : date('d');
		
			$begin_parts = explode(":", $this->data['begin']);
			$b_hour = isset($begin_parts[0]) ? $begin_parts[0] : 0;
			$b_minute = isset($begin_parts[1]) ? $begin_parts[1] : 0;
			$b_second = isset($begin_parts[2]) ? $begin_parts[2] : 0;
		
			$effort_parts = explode(":", $effort);
			$e_hour = isset($effort_parts[0]) ? $effort_parts[0] : 0;
			$e_minute = isset($effort_parts[1]) ? $effort_parts[1] : 0;
			$e_second = isset($effort_parts[2]) ? $effort_parts[2] : 0;
			$b_timestamp = mktime($b_hour, $b_minute, $b_second, $month, $day, $year);
			$e_timestamp = $b_timestamp + $e_hour*3600 + $e_minute*60 + $e_second;
			$this->data['end'] = date("H:i:s", $e_timestamp);
		}

		function stop() {
			$GLOBALS['_PJ_debug'] = true; // Enable debug for this method
			debugLog('EFFORT_STOP', 'stop() method called for effort ID: ' . $this->giveValue('id'));
		
			// Fix: Add safe array handling for explode results to prevent undefined array key warnings
			$date_parts = explode("-", $this->data['date']);
			$year = isset($date_parts[0]) ? $date_parts[0] : date('Y');
			$month = isset($date_parts[1]) ? $date_parts[1] : date('m');
			$day = isset($date_parts[2]) ? $date_parts[2] : date('d');
		
			$begin_parts = explode(":", $this->data['begin']);
			$hour = isset($begin_parts[0]) ? $begin_parts[0] : 0;
			$minute = isset($begin_parts[1]) ? $begin_parts[1] : 0;
			$second = isset($begin_parts[2]) ? $begin_parts[2] : 0;
			$b_time 			= mktime($hour, $minute, $second, $month, $day, $year);
		
			debugLog('EFFORT_STOP', 'Effort data - begin: ' . $this->giveValue('begin') . ', end: ' . $this->giveValue('end') . ', b_time: ' . $b_time . ', current_time: ' . time());
		
			// Fix: Clear logic for stoppable efforts
			if($b_time > time()) {
				// Future efforts cannot be stopped
				debugLog('EFFORT_STOP', 'Cannot stop: Future effort (b_time > current_time)');
				return;
			}
			if($this->giveValue('begin') != $this->giveValue('end')) {
				// Already stopped efforts cannot be stopped again
				debugLog('EFFORT_STOP', 'Cannot stop: Already stopped effort (begin != end)');
				return;
			}
			// Running effort (begin == end, not in future) - can be stopped
			debugLog('EFFORT_STOP', 'Effort can be stopped - proceeding with stop logic');
			$e_time 			= mktime(date('H'), date('i'), date('s'));
			if($b_time > $e_time) {
				$e_time = mktime(date('H'), date('i'), date('s'), date('m', $b_time+86400), date('d', $b_time+86400), date('Y', $b_time+86400));
			}
			$diff_time 			= $e_time - $b_time;
			$hours				= floor($diff_time / 3600);
			$minutes			= floor($diff_time / 60 -(floor($diff_time / 3600)*60));
		
			// Fix: Workaround for zero-duration efforts (stopped immediately after creation)
			if($diff_time < 60) { // Less than 1 minute
				debugLog('EFFORT_STOP', 'Zero-duration effort detected - applying 1-minute workaround');
				// Move start time 1 minute back and set duration to 1 minute
				$b_time = $b_time - 60; // 1 minute earlier
				$hours = 0;
				$minutes = 1; // 1 minute duration
				// Update begin time in data
				$this->data['begin'] = sprintf('%02d:%02d:00', date('H', $b_time), date('i', $b_time));
				debugLog('EFFORT_STOP', 'Adjusted begin time to: ' . $this->data['begin'] . ' (1 minute earlier)');
			} else {
				// Normal duration calculation for efforts running longer than 1 minute
				if($hours > 23) {
					$hours = 23;
					$minutes = 59;
				}
				if($minutes != 59) {
					$minutes		= round($minutes/5)*5;
				}
			}
		
			$e_time = $b_time + $hours*3600 + $minutes*60;
			$this->data['end'] = sprintf('%02d:%02d:00', date('H', $e_time), date('i', $e_time));
			debugLog('EFFORT_STOP', 'Calculated end time: ' . $this->data['end'] . ', hours: ' . $hours . ', minutes: ' . $minutes);
			debugLog('EFFORT_STOP', 'Calling save() method to persist stopped effort');
			$this->save();
			debugLog('EFFORT_STOP', 'save() method completed - effort should now be stopped');
		}

		function copy(&$user) {
			$__effort = new Effort($this->data, $user);
			$__effort->data['id']		= 0;
			$__effort->data['user']		= $user->giveValue('id');
			$__effort->data['date']		= date('Y-m-d');
			$__effort->data['begin']	= date('H:i:00');
			$__effort->data['end']		= $__effort->data['begin'];
			$__effort->data['billed']	= '';

			return $__effort;
		}

	}
?>
