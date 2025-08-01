<?php
	if(!isset($_PJ_root)) {
		print "<b>FEHLER:</b> \$_PJ_root ist <b>nicht festgelegt</b>! (" . __FILE__ . ", Zeile: " . __LINE__ . ")";
		exit;
	}

	// Include security layer
	require_once(__DIR__ . '/security.inc.php');

	class Statistics extends EffortList {
		var $db;
		var $data;
		var $stats;
		var $months;
		var	$effort_count			= 0;
		var $effort_cursor			= -1;
		var $billed_effort_count	= 0;
		// Fix: Explicit property declarations for PHP 8.4 compatibility
		var $mode;
		var $users;
		var $show_unassigned;

		// Fix: Replace deprecated PHP4-style constructor with modern __construct for PHP 8.4 compatibility
	function __construct(&$user, $load = false, $customer = NULL, $project = NULL, $users = NULL, $mode = NULL, $show_unassigned = false) {
			$this->customer	= $customer;
			$this->project	= $project;
			$this->mode		= $mode;
			$this->user		= $user;
			$this->users	= $users;
			$this->show_unassigned = $show_unassigned;
			// Fix: Initialize arrays to prevent undefined array key warnings
			$this->data = array('seconds' => 0, 'billed_seconds' => 0);
			$this->months = array('open' => array(), 'billed' => array());
			if(empty($load)) {
				return;
			}
			$this->load();
		}

		function load() {
			if(!isset($this->db) or !is_object($this->db)) {
				$this->db = new Database;
			}

			if(!$this->user->checkPermission('admin')) {
				$access_query  = " AND (";
				$access_query .= " ("	. $GLOBALS['_PJ_effort_table'] . ".user = '" . $this->user->giveValue('id') . "' AND "	. $GLOBALS['_PJ_effort_table'] . ".access LIKE 'r________')";
				$access_query .= " OR ";
				$access_query .= " ("	. $GLOBALS['_PJ_effort_table'] . ".gid IN (" . $this->user->giveValue('gids') . ") AND "	. $GLOBALS['_PJ_effort_table'] . ".access LIKE '___r_____')";
				$access_query .= " OR ";
				$access_query .= " ("	. $GLOBALS['_PJ_effort_table'] . ".access LIKE '______r__')";
				$access_query .= " ) ";
				$raw_access_query  = " AND (";
				$raw_access_query .= " (user = '" . $this->user->giveValue('id') . "' AND access LIKE 'r________')";
				$raw_access_query .= " OR ";
				$raw_access_query .= " (gid IN (" . $this->user->giveValue('gids') . ") AND access LIKE '___r_____')";
				$raw_access_query .= " OR ";
				$raw_access_query .= " (access LIKE '______r__')";
				$raw_access_query .= " ) ";
				$this->db->query("SELECT id FROM " . $GLOBALS['_PJ_customer_table'] . " WHERE active = 'yes' $raw_access_query");
				while($this->db->next_record()) {
					if(!empty($cids)) {
						$cids .= ',';
					}
					$cids .= $this->db->f('id');
				}
				if(empty($cids)) {
					return;
				}
				$this->db->query("SELECT id FROM " . $GLOBALS['_PJ_project_table'] . " WHERE closed = 'No' AND customer_id IN ($cids) $raw_access_query");
				while($this->db->next_record()) {
					if(!empty($pids)) {
						$pids .= ',';
					}
					$pids .= $this->db->f('id');
				}
				if(empty($pids)) {
					return;
				}
			}

			$query = "SELECT " . $GLOBALS['_PJ_effort_table'] . ".*, " .
					 $GLOBALS['_PJ_customer_table'] . ".customer_name, " .
					 $GLOBALS['_PJ_project_table'] . ".customer_id, " .
					 $GLOBALS['_PJ_project_table'] . ".project_name " .
					 " FROM " .
					 $GLOBALS['_PJ_effort_table'] . ", " .
					 $GLOBALS['_PJ_customer_table'] . ", " .
					 $GLOBALS['_PJ_project_table'] .
					 " WHERE " .
					 $GLOBALS['_PJ_effort_table'] . ".project_id=" . $GLOBALS['_PJ_project_table'] . ".id " . 
					 " AND " .
					 $GLOBALS['_PJ_project_table'] . ".customer_id=" . $GLOBALS['_PJ_customer_table'] . ".id";
			if(is_object($this->customer) && $this->customer->giveValue('id')) {
				$query .= " AND " .
						  $GLOBALS['_PJ_project_table'] . ".customer_id='" . $this->customer->giveValue('id') . "'";
			}
			if(is_object($this->project) && $this->project->giveValue('id')) {
				$query .= " AND " .
						  $GLOBALS['_PJ_effort_table'] . ".project_id='" . $this->project->giveValue('id') . "'";
			}
			if(is_array($this->users) && count($this->users)) {
				$query .= " AND " .
						  $GLOBALS['_PJ_effort_table'] . ".user  IN (" . implode(',', $this->users) . ")";
			}
			if($this->mode != 'billed') {
				$query .= " AND " . $GLOBALS['_PJ_effort_table'] . ".billed IS NULL";
			}
			if(!$this->user->checkPermission('admin')) {
				$query .= " AND (" . $GLOBALS['_PJ_customer_table'] . ".readforeignefforts = 1 OR " . $GLOBALS['_PJ_effort_table'] . ".user = '" . $this->user->giveValue('id') . "')";
				$query .= " AND project_id IN ($pids)";
				$query .= $access_query;
			}
			$query .= " ORDER BY " . $GLOBALS['_PJ_effort_table'] . ".date ASC, " .
					  $GLOBALS['_PJ_effort_table'] . ".begin ASC";

			$this->db->query($query);
			while($this->db->next_record()) {
				list($year, $month, $day) = explode("-", $this->db->Record['date']);
				$seconds = calculate('seconds', $this->db->Record['date'], $this->db->Record['begin'], $this->db->Record['end']);
				if($this->db->Record['billed'] != '') {
					$this->data['billed_seconds']			+= $seconds;
					if (!isset($this->months['billed']["$year-$month"])) $this->months['billed']["$year-$month"] = 0;
					$this->months['billed']["$year-$month"] += $seconds;
					$this->billed_effort_count++;
					if (!isset($this->months['open']["$year-$month"])) $this->months['open']["$year-$month"] = 0;
					$this->months['open']["$year-$month"] += $seconds;
					$this->months['open']["$year-$month"]	+= $seconds;
				}
				$this->data['seconds']						+= $seconds;
				$this->efforts[] = new Effort($this->db->Record, $this->user);
				$this->effort_count++;
			}
			$this->data['billed_minutes']	= round(($this->data['billed_seconds']	/ 60), 2);
			$this->data['billed_hours']		= round(($this->data['billed_minutes']	/ 60), 2);
			$this->data['billed_days']		= round(($this->data['billed_hours']	/ 60 / 8), 2);
			$this->data['minutes']			= round(($this->data['seconds']	/ 60), 2);
			$this->data['hours']			= round(($this->data['minutes']	/ 60), 2);
			$this->data['days']				= round(($this->data['minutes']	/ 60 / 8), 2);
			$this->data['customer_id']		= $this->project->giveValue('id');
		}

		function loadMonth($year, $month) {
			if(!isset($this->db) or !is_object($this->db)) {
				$this->db = new Database;
			}
			$next_month = formatDate("$year-" . ($month+1) . "-01", "m");
			$next_year = formatDate("$year-" . ($month+1) . "-01", "Y");

			if(!$this->user->checkPermission('admin')) {
				$access_query  = " AND (";
				$access_query .= " ("	. $GLOBALS['_PJ_effort_table'] . ".user = '" . $this->user->giveValue('id') . "' AND "	. $GLOBALS['_PJ_effort_table'] . ".access LIKE 'r________')";
				$access_query .= " OR ";
				$access_query .= " ("	. $GLOBALS['_PJ_effort_table'] . ".gid IN (" . $this->user->giveValue('gids') . ") AND "	. $GLOBALS['_PJ_effort_table'] . ".access LIKE '___r_____')";
				$access_query .= " OR ";
				$access_query .= " ("	. $GLOBALS['_PJ_effort_table'] . ".access LIKE '______r__')";
				$access_query .= " ) ";
				$raw_access_query  = " AND (";
				$raw_access_query .= " (user = '" . $this->user->giveValue('id') . "' AND access LIKE 'r________')";
				$raw_access_query .= " OR ";
				$raw_access_query .= " (gid IN (" . $this->user->giveValue('gids') . ") AND access LIKE '___r_____')";
				$raw_access_query .= " OR ";
				$raw_access_query .= " (access LIKE '______r__')";
				$raw_access_query .= " ) ";
				$this->db->query("SELECT id FROM " . $GLOBALS['_PJ_customer_table'] . " WHERE active = 'yes' $raw_access_query");
				while($this->db->next_record()) {
					if(!empty($cids)) {
						$cids .= ',';
					}
					$cids .= $this->db->f('id');
				}
				if(empty($cids)) {
					return;
				}
				$this->db->query("SELECT id FROM " . $GLOBALS['_PJ_project_table'] . " WHERE closed = 'No' AND customer_id IN ($cids) $raw_access_query");
				while($this->db->next_record()) {
					if(!empty($pids)) {
						$pids .= ',';
					}
					$pids .= $this->db->f('id');
				}
				if(empty($pids)) {
					return;
				}
			}

			$query = "SELECT " . $GLOBALS['_PJ_effort_table'] . ".*, " .
					 $GLOBALS['_PJ_customer_table'] . ".customer_name, " .
					 $GLOBALS['_PJ_project_table'] . ".customer_id, " .
					 $GLOBALS['_PJ_project_table'] . ".project_name " .
					 " FROM " .
					 $GLOBALS['_PJ_effort_table'] . ", " .
					 $GLOBALS['_PJ_customer_table'] . ", " .
					 $GLOBALS['_PJ_project_table'] .
					 " WHERE " .
					 $GLOBALS['_PJ_effort_table'] . ".project_id=" . $GLOBALS['_PJ_project_table'] . ".id " . 
					 " AND " .
					 $GLOBALS['_PJ_project_table'] . ".customer_id=" . $GLOBALS['_PJ_customer_table'] . ".id";
			if(!$this->user->checkPermission('admin')) {
				$query .= " AND project_id IN ($pids)";
				$query .= $access_query;
			}
			if(is_object($this->customer) && $this->customer->giveValue('id')) {
				$query .= " AND " .
						  $GLOBALS['_PJ_project_table'] . ".customer_id='" . $this->customer->giveValue('id') . "'";
			}
			if(is_object($this->project) && $this->project->giveValue('id')) {
				$query .= " AND " .
						  $GLOBALS['_PJ_effort_table'] . ".project_id='" . $this->prjoect->giveValue('id') . "'";
			}
			if(is_array($this->users) && count($this->users)) {
				$query .= " AND " .
						  $GLOBALS['_PJ_effort_table'] . ".user  IN (" . implode(',', $this->users) . ")";
			}
			$query .= " AND " .
					  $GLOBALS['_PJ_effort_table'] . ".date >= '$year-$month-01'" .
					  " AND " .
					  $GLOBALS['_PJ_effort_table'] .
					  ".date < '$next_year-$next_month-01'";
			if($this->mode != 'billed') {
				$query .= " AND " . $GLOBALS['_PJ_effort_table'] . ".billed IS NULL";
			}
			$query .= " ORDER BY " . $GLOBALS['_PJ_effort_table'] . ".date ASC, " .
			$GLOBALS['_PJ_effort_table'] . ".begin ASC";

			$this->db->query($query);

			while($this->db->next_record()) {
				list($year, $month, $day) = explode("-", $this->db->Record['date']);
				$seconds = calculate('seconds', $this->db->Record['date'], $this->db->Record['begin'], $this->db->Record['end']);
				if($this->db->Record['billed'] != '') {
					$this->data['billed_seconds']			+= $seconds;
					$this->months['billed']["$year-$month"] += $seconds;
					$this->billed_effort_count++;
				} else {
					$this->months['open']["$year-$month"]	+= $seconds;
				}
				$this->data['seconds']	+= $seconds;
				$this->days[$day]		+= $seconds;
				$this->db->Record['seconds'] = $seconds;
				$this->efforts[]		 = new Effort($this->db->Record, $this->user);
				$this->effort_count++;
			}
			$this->data['minutes']			= round(($this->data['seconds']	/ 60), 2);
			$this->data['hours']			= round(($this->data['minutes']	/ 60), 2);
			$this->data['days']				= round(($this->data['minutes']	/ 60 / 8), 2);
			$this->data['customer_id']		= $this->customer->giveValue('id');
		}

		function loadProject($year, $month, $pid) {
			if(!isset($this->db) or !is_object($this->db)) {
				$this->db = new Database;
			}
			if(!$this->user->checkPermission('admin')) {
				$access_query  = " AND (";
				$access_query .= " ("	. $GLOBALS['_PJ_effort_table'] . ".user = '" . $this->user->giveValue('id') . "' AND "	. $GLOBALS['_PJ_effort_table'] . ".access LIKE 'r________')";
				$access_query .= " OR ";
				$access_query .= " ("	. $GLOBALS['_PJ_effort_table'] . ".gid IN (" . $this->user->giveValue('gids') . ") AND "	. $GLOBALS['_PJ_effort_table'] . ".access LIKE '___r_____')";
				$access_query .= " OR ";
				$access_query .= " ("	. $GLOBALS['_PJ_effort_table'] . ".access LIKE '______r__')";
				$access_query .= " ) ";
				$raw_access_query  = " AND (";
				$raw_access_query .= " (user = '" . $this->user->giveValue('id') . "' AND access LIKE 'r________')";
				$raw_access_query .= " OR ";
				$raw_access_query .= " (gid IN (" . $this->user->giveValue('gids') . ") AND access LIKE '___r_____')";
				$raw_access_query .= " OR ";
				$raw_access_query .= " (access LIKE '______r__')";
				$raw_access_query .= " ) ";
				$this->db->query("SELECT id FROM " . $GLOBALS['_PJ_customer_table'] . " WHERE active = 'yes' $raw_access_query");
				while($this->db->next_record()) {
					if(!empty($cids)) {
						$cids .= ',';
					}
					$cids .= $this->db->f('id');
				}
				if(empty($cids)) {
					return;
				}
				$this->db->query("SELECT id FROM " . $GLOBALS['_PJ_project_table'] . " WHERE closed = 'No' AND customer_id IN ($cids) $raw_access_query");
				while($this->db->next_record()) {
					if(!empty($pids)) {
						$pids .= ',';
					}
					$pids .= $this->db->f('id');
				}
				if(empty($pids)) {
					return;
				}
			}

			$next_date = formatDate("$year-" . ($month+1) . "-01", "Y-m-d");

			$query = "SELECT " . $GLOBALS['_PJ_effort_table'] . ".*, " .
					 $GLOBALS['_PJ_project_table'] . ".project_name, " .
					 $GLOBALS['_PJ_project_table'] . ".customer_id " .
					 " FROM " .
					 $GLOBALS['_PJ_effort_table'] . ", " .
					 $GLOBALS['_PJ_project_table'] . ", " .
					 $GLOBALS['_PJ_customer_table'] .
					 " WHERE " .
					 $GLOBALS['_PJ_project_table'] . ".id=" .
					 $GLOBALS['_PJ_effort_table'] . ".project_id" .
					 " AND " .
					 $GLOBALS['_PJ_effort_table'] . ".project_id=$pid" .
					 " AND " .
					 $GLOBALS['_PJ_effort_table'] . ".date >= '$year-$month-01'" .
					 " AND " .
					 $GLOBALS['_PJ_effort_table'] .
					 ".date < '$next_date'" .
					 " AND " .
					 $GLOBALS['_PJ_project_table'] . ".customer_id=" . $GLOBALS['_PJ_customer_table'] . ".id";
			if(is_array($this->users) && count($this->users)) {
				$query .= " AND " .
						  $GLOBALS['_PJ_effort_table'] . ".user  IN (" . implode(',', $this->users) . ")";
			}
			if($this->mode != 'billed') {
				$query .= " AND " . $GLOBALS['_PJ_effort_table'] . ".billed IS NULL";
			}
			if(!$this->user->checkPermission('admin')) {
				$query .= " AND project_id IN ($pids)";
				$query .= $access_query;
			}
			$query .= " ORDER BY " . $GLOBALS['_PJ_effort_table'] . ".date, " . $GLOBALS['_PJ_effort_table'] . ".begin";

			$this->db->query($query);

			while($this->db->next_record()) {
				list($year, $month, $day) = explode("-", $this->db->Record['date']);
				$seconds = calculate('seconds', $this->db->Record['date'], $this->db->Record['begin'], $this->db->Record['end']);
				if($this->db->Record['billed'] != '') {
					$this->data['billed_seconds']			+= $seconds;
					$this->months['billed']["$year-$month"] += $seconds;
					$this->billed_effort_count++;
				} else {
					$this->months['open']["$year-$month"]	+= $seconds;
				}
				$this->data['seconds']	+= $seconds;
				$this->days[$day]		+= $seconds;
				$this->db->Record['seconds'] = $seconds;
				$this->efforts[]		 = new Effort($this->db->Record, $this->user);
				$this->effort_count++;
			}
			$this->data['minutes']			= round(($this->data['seconds']	/ 60), 2);
			$this->data['hours']			= round(($this->data['minutes']	/ 60), 2);
			$this->data['days']				= round(($this->data['minutes']	/ 60 / 8), 2);
			$this->data['customer_id']		= $this->customer->giveValue('id');
		}

		function loadProjectTime($start, $end, $pid) {
			if(!isset($this->db) or !is_object($this->db)) {
				$this->db = new Database;
			}

			if(!$this->user->checkPermission('admin')) {
				$access_query  = " AND (";
				$access_query .= " ("	. $GLOBALS['_PJ_effort_table'] . ".user = '" . $this->user->giveValue('id') . "' AND "	. $GLOBALS['_PJ_effort_table'] . ".access LIKE 'r________')";
				$access_query .= " OR ";
				$access_query .= " ("	. $GLOBALS['_PJ_effort_table'] . ".gid IN (" . $this->user->giveValue('gids') . ") AND "	. $GLOBALS['_PJ_effort_table'] . ".access LIKE '___r_____')";
				$access_query .= " OR ";
				$access_query .= " ("	. $GLOBALS['_PJ_effort_table'] . ".access LIKE '______r__')";
				$access_query .= " ) ";
				$raw_access_query  = " AND (";
				$raw_access_query .= " (user = '" . $this->user->giveValue('id') . "' AND access LIKE 'r________')";
				$raw_access_query .= " OR ";
				$raw_access_query .= " (gid IN (" . $this->user->giveValue('gids') . ") AND access LIKE '___r_____')";
				$raw_access_query .= " OR ";
				$raw_access_query .= " (access LIKE '______r__')";
				$raw_access_query .= " ) ";
				$this->db->query("SELECT id FROM " . $GLOBALS['_PJ_customer_table'] . " WHERE active = 'yes' $raw_access_query");
				while($this->db->next_record()) {
					if(!empty($cids)) {
						$cids .= ',';
					}
					$cids .= $this->db->f('id');
				}
				if(empty($cids)) {
					return;
				}
				$this->db->query("SELECT id FROM " . $GLOBALS['_PJ_project_table'] . " WHERE closed = 'No' AND customer_id IN ($cids) $raw_access_query");
				while($this->db->next_record()) {
					if(!empty($pids)) {
						$pids .= ',';
					}
					$pids .= $this->db->f('id');
				}
				if(empty($pids)) {
					return;
				}
			}

			$query = "SELECT " . $GLOBALS['_PJ_effort_table'] . ".*, " .
					 $GLOBALS['_PJ_project_table'] . ".project_name, " .
					 $GLOBALS['_PJ_project_table'] . ".customer_id " .
					 " FROM " .
					 $GLOBALS['_PJ_effort_table'] . ", " .
					 $GLOBALS['_PJ_project_table'] . ", " .
					 $GLOBALS['_PJ_customer_table'] .
					 " WHERE " .
					 $GLOBALS['_PJ_effort_table'] . ".project_id=" .
					 $GLOBALS['_PJ_project_table'] . ".id" .
					 " AND " .
					 $GLOBALS['_PJ_effort_table'] . ".project_id=$pid" .
					 " AND " .
					 $GLOBALS['_PJ_project_table'] . ".customer_id='" . $GLOBALS['_PJ_customer_table'] . ".id" .
					 " AND " .
					 $GLOBALS['_PJ_customer_table'] . ".id='" . $this->customer->giveValue('id') . "'" .
					 " AND " .
					 $GLOBALS['_PJ_effort_table'] . ".date >= '$start'" .
					 " AND " .
					 $GLOBALS['_PJ_effort_table'] . ".date <= '$end'";
			if($this->mode != 'billed') {
				$query .= " AND " . $GLOBALS['_PJ_effort_table'] . ".billed IS NULL";
			}
			if(is_array($this->users) && count($this->users)) {
				$query .= " AND " .
						  $GLOBALS['_PJ_effort_table'] . ".user  IN (" . implode(',', $this->users) . ")";
			}
			if(!$this->user->checkPermission('admin')) {
				$query .= " AND project_id IN ($pids)";
				$query .= $access_query;
			}
			$query .= " ORDER BY " . $GLOBALS['_PJ_effort_table'] . ".date, " . $GLOBALS['_PJ_effort_table'] . ".begin";

			$this->db->query($query);

			while($this->db->next_record()) {
				list($year, $month, $day) = explode("-", $this->db->Record['date']);
				$seconds = calculate('seconds', $this->db->Record['date'], $this->db->Record['begin'], $this->db->Record['end']);
				if($this->db->Record['billed'] != '') {
					$this->data['billed_seconds']			+= $seconds;
					$this->months['billed']["$year-$month"] += $seconds;
					$this->billed_effort_count++;
				} else {
					$this->months['open']["$year-$month"]	+= $seconds;
				}
				$this->data['seconds']	+= $seconds;
				$this->days[$day]		+= $seconds;
				$this->db->Record['seconds'] = $seconds;
				$this->efforts[]		 = new Effort($this->db->Record, $this->user);
				$this->effort_count++;
			}
			$this->data['minutes']			= round(($this->data['seconds']	/ 60), 2);
			$this->data['hours']			= round(($this->data['minutes']	/ 60), 2);
			$this->data['days']				= round(($this->data['minutes']	/ 60 / 8), 2);
			$this->data['customer_id']		= $this->customer->giveValue('id');
		}

		function loadTime($start, $end = '') {
			if(!isset($this->db) or !is_object($this->db)) {
				$this->db = new Database;
			}

			if($end == '') {
				$end = date("Y-m-d");
			} else {
				$end = date("Y-m-d", strtotime($end));
			}

			$start = date("Y-m-d", strtotime($start));

			if(!$this->user->checkPermission('admin')) {
				$access_query  = " AND (";
				$access_query .= " ("	. $GLOBALS['_PJ_effort_table'] . ".user = '" . $this->user->giveValue('id') . "' AND "	. $GLOBALS['_PJ_effort_table'] . ".access LIKE 'r________')";
				$access_query .= " OR ";
				$access_query .= " ("	. $GLOBALS['_PJ_effort_table'] . ".gid IN (" . $this->user->giveValue('gids') . ") AND "	. $GLOBALS['_PJ_effort_table'] . ".access LIKE '___r_____')";
				$access_query .= " OR ";
				$access_query .= " ("	. $GLOBALS['_PJ_effort_table'] . ".access LIKE '______r__')";
				$access_query .= " ) ";
				$raw_access_query  = " AND (";
				$raw_access_query .= " (user = '" . $this->user->giveValue('id') . "' AND access LIKE 'r________')";
				$raw_access_query .= " OR ";
				$raw_access_query .= " (gid IN (" . $this->user->giveValue('gids') . ") AND access LIKE '___r_____')";
				$raw_access_query .= " OR ";
				$raw_access_query .= " (access LIKE '______r__')";
				$raw_access_query .= " ) ";
				$this->db->query("SELECT id FROM " . $GLOBALS['_PJ_customer_table'] . " WHERE active = 'yes' $raw_access_query");
				while($this->db->next_record()) {
					if(!empty($cids)) {
						$cids .= ',';
					}
					$cids .= $this->db->f('id');
				}
				if(empty($cids)) {
					return;
				}
				$this->db->query("SELECT id FROM " . $GLOBALS['_PJ_project_table'] . " WHERE closed = 'No' AND customer_id IN ($cids) $raw_access_query");
				while($this->db->next_record()) {
					if(!empty($pids)) {
						$pids .= ',';
					}
					$pids .= $this->db->f('id');
				}
				if(empty($pids)) {
					return;
				}
			}

			// Build query with LEFT JOIN for unassigned efforts or INNER JOIN for regular reports
			if ($this->show_unassigned) {
				// Use LEFT JOIN to include efforts without project/customer assignment
				$query = "SELECT " . $GLOBALS['_PJ_effort_table'] . ".*, " .
						 $GLOBALS['_PJ_customer_table'] . ".customer_name, " .
						 $GLOBALS['_PJ_project_table'] . ".customer_id, " .
						 $GLOBALS['_PJ_project_table'] . ".project_name " .
						 " FROM " . $GLOBALS['_PJ_effort_table'] .
						 " LEFT JOIN " . $GLOBALS['_PJ_project_table'] . " ON " . $GLOBALS['_PJ_effort_table'] . ".project_id = " . $GLOBALS['_PJ_project_table'] . ".id" .
						 " LEFT JOIN " . $GLOBALS['_PJ_customer_table'] . " ON " . $GLOBALS['_PJ_project_table'] . ".customer_id = " . $GLOBALS['_PJ_customer_table'] . ".id" .
						 " WHERE (" . $GLOBALS['_PJ_effort_table'] . ".project_id = 0 OR " . $GLOBALS['_PJ_effort_table'] . ".project_id IS NULL)";
			} else {
				// Use INNER JOIN for regular reports (existing behavior)
				$query = "SELECT " . $GLOBALS['_PJ_effort_table'] . ".*, " .
						 $GLOBALS['_PJ_customer_table'] . ".customer_name, " .
						 $GLOBALS['_PJ_project_table'] . ".customer_id, " .
						 $GLOBALS['_PJ_project_table'] . ".project_name " .
						 " FROM " .
						 $GLOBALS['_PJ_effort_table'] . ", " .
						 $GLOBALS['_PJ_customer_table'] . ", " .
						 $GLOBALS['_PJ_project_table'] .
						 " WHERE " .
						 $GLOBALS['_PJ_effort_table'] . ".project_id=" . $GLOBALS['_PJ_project_table'] . ".id" . 
						 "  AND " .
						 $GLOBALS['_PJ_project_table'] . ".customer_id=" . $GLOBALS['_PJ_customer_table'] . ".id";
			}
			if(is_object($this->customer) && $this->customer->giveValue('id')) {
				$query .= " AND " .
						  $GLOBALS['_PJ_project_table'] . ".customer_id='" . $this->customer->giveValue('id') . "'";
			}
			if(is_object($this->project) && $this->project->giveValue('id')) {
				$query .= " AND " .
						  $GLOBALS['_PJ_effort_table'] . ".project_id='" . $this->project->giveValue('id') . "'";
			}
			if(is_array($this->users) && count($this->users)) {
				$query .= " AND " .
						  $GLOBALS['_PJ_effort_table'] . ".user  IN (" . implode(',', $this->users) . ")";
			}
			$query .= " AND " .
					 $GLOBALS['_PJ_effort_table'] . ".date >= '$start'" .
					 " AND " .
					 $GLOBALS['_PJ_effort_table'] . ".date <= '$end'";

			if(is_array($this->users) && count($this->users)) {
				$query .= " AND " .
						  $GLOBALS['_PJ_effort_table'] . ".user  IN (" . implode(',', $this->users) . ")";
			}
			if($this->mode != 'billed') {
				$query .= " AND " . $GLOBALS['_PJ_effort_table'] . ".billed IS NULL";
			}

			if(!$this->user->checkPermission('admin')) {
				$query .= " AND project_id IN ($pids)";
				$query .= $access_query;
			}
			$query .= " ORDER BY " . $GLOBALS['_PJ_effort_table'] . ".date, " . $GLOBALS['_PJ_effort_table'] . ".begin ASC";

			$this->db->query($query);
			while($this->db->next_record()) {
				list($year, $month, $day) = explode("-", $this->db->Record['date']);
				$seconds = calculate('seconds', $this->db->Record['date'], $this->db->Record['begin'], $this->db->Record['end']);
				if($this->db->Record['billed'] != '') {
					@$this->data['billed_seconds']			+= $seconds;
					@$this->months['billed']["$year-$month"] += $seconds;
					$this->billed_effort_count++;
				} else {
					@$this->months['open']["$year-$month"]	+= $seconds;
				}
				@$this->data['seconds']	+= $seconds;
				@$this->days[$day]		+= $seconds;
				$this->db->Record['seconds'] = $seconds;
				$this->efforts[]		 = new Effort($this->db->Record, $this->user);
				@$this->effort_count++;
			}
			if(!isset($this->data['seconds'])) $this->data['seconds'] = 0;
			$this->data['minutes']			= round(($this->data['seconds']	/ 60), 2);
			$this->data['hours']			= round(($this->data['minutes']	/ 60), 2);
			$this->data['days']				= round(($this->data['minutes']	/ 60 / 8), 2);
			// Fix: Auto-detect customer from project if customer is null but project is set
			if($this->customer) {
				$this->data['customer_id'] = $this->customer->giveValue('id');
			} elseif($this->project && $this->project->giveValue('customer_id')) {
				// Auto-detect customer from project
				$this->data['customer_id'] = $this->project->giveValue('customer_id');
				debugLog("LOG_STATISTICS_AUTOFIX", "Auto-detected customer ID from project: " . $this->data['customer_id']);
			} else {
				$this->data['customer_id'] = null;
			}
		}

		function giveValue($key) {
			if(isset($this->data[$key])) return $this->data[$key];
else return null;
		}

		function count($billed = false) {
			if(!empty($billed)) {
				return ($this->billed_effort_count);
			}
			return $this->effort_count;
		}
	}
?>
