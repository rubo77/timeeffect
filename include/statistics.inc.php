<?php
	if(!isset($_PJ_root)) {
		print "<b>FEHLER:</b> \$_PJ_root ist <b>nicht festgelegt</b>! (" . __FILE__ . ", Zeile: " . __LINE__ . ")";
		exit;
	}

	class Statistics extends EffortList {
		var $db;
		var $data;
		var $stats;
		var	$effort_count			= 0;
		var $effort_cursor			= -1;
		var $billed_effort_count	= 0;

		function Statistics(&$user, $load = false, $customer = NULL, $project = NULL, $users = NULL, $mode = NULL) {
			$this->customer	= $customer;
			$this->project	= $project;
			$this->mode		= $mode;
			$this->user		= $user;
			$this->users	= $users;
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
					$this->months['billed']["$year-$month"] += $seconds;
					$this->billed_effort_count++;
				} else {
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

		function giveValue($key) {
			return $this->data[$key];
		}

		function count($billed = false) {
			if(!empty($billed)) {
				return ($this->billed_effort_count);
			}
			return $this->effort_count;
		}
	}
?>