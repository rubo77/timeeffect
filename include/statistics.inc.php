<?php
	if(!isset($_PJ_root)) {
		print "<b>FEHLER:</b> \$_PJ_root ist <b>nicht festgelegt</b>! (" . __FILE__ . ", Zeile: " . __LINE__ . ")";
		exit;
	}

	class Statistics extends EffortList {
		var $db;
		var $data;
		var $stats;
		var	$effort_count = 0;
		var $effort_cursor = -1;

		function Statistics($load = false, $customer = NULL, $project = NULL, $mode = NULL) {
			$this->customer_id	= $customer;
			$this->project_id	= $project;
			$this->mode			= $mode;
			if(!$load) {
				return;
			}
			$this->load();
		}

		function load() {
			if(!is_object($this->db)) {
				$this->db = new Database;
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
					 $GLOBALS['_PJ_effort_table'] . ".project_id=" .
					 $GLOBALS['_PJ_project_table'] . ".id AND " .
					 $GLOBALS['_PJ_project_table'] . ".customer_id=" .
					 $GLOBALS['_PJ_customer_table'] . ".id";
			if($this->customer_id) {
				$query .= " AND " .
						  $GLOBALS['_PJ_project_table'] . ".customer_id='" . $this->customer_id . "'";
			}
			if($this->project_id) {
				$query .= " AND " .
						  $GLOBALS['_PJ_effort_table'] . ".project_id='" . $this->project_id . "'";
			}
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
				} else {
					$this->months['open']["$year-$month"]	+= $seconds;
				}
				$this->data['seconds']						+= $seconds;
				$this->efforts[] = new Effort($this->db->Record);
				$this->effort_count++;
			}
			$this->data['billed_minutes']	= round(($this->data['billed_seconds']	/ 60), 2);
			$this->data['billed_hours']		= round(($this->data['billed_minutes']	/ 60), 2);
			$this->data['billed_days']		= round(($this->data['billed_hours']	/ 60 / 8), 2);
			$this->data['minutes']			= round(($this->data['seconds']	/ 60), 2);
			$this->data['hours']			= round(($this->data['minutes']	/ 60), 2);
			$this->data['days']				= round(($this->data['minutes']	/ 60 / 8), 2);
			$this->data['customer_id']		= $this->customer_id;
		}

		function loadMonth($year, $month) {
			if(!is_object($this->db)) {
				$this->db = new Database;
			}
			$next_month = formatDate("$year-" . ($month+1) . "-01", "m");
			$next_year = formatDate("$year-" . ($month+1) . "-01", "Y");

			$query = "SELECT " . $GLOBALS['_PJ_effort_table'] . ".*, " .
					 $GLOBALS['_PJ_customer_table'] . ".customer_name, " .
					 $GLOBALS['_PJ_project_table'] . ".customer_id, " .
					 $GLOBALS['_PJ_project_table'] . ".project_name " .
					 " FROM " .
					 $GLOBALS['_PJ_effort_table'] . ", " .
					 $GLOBALS['_PJ_customer_table'] . ", " .
					 $GLOBALS['_PJ_project_table'] .
					 " WHERE " .
					 $GLOBALS['_PJ_effort_table'] . ".project_id=" .
					 $GLOBALS['_PJ_project_table'] . ".id AND " .
					 $GLOBALS['_PJ_project_table'] . ".customer_id=" .
					 $GLOBALS['_PJ_customer_table'] . ".id";
			if($this->customer_id) {
				$query .= " AND " .
						  $GLOBALS['_PJ_project_table'] . ".customer_id='" . $this->customer_id . "'";
			}
			if($this->project_id) {
				$query .= " AND " .
						  $GLOBALS['_PJ_effort_table'] . ".project_id='" . $this->project_id . "'";
			}
			$query .= " AND " .
					  $GLOBALS['_PJ_effort_table'] . ".date >= '$year-$month-01'" .
					  " AND " .
					  $GLOBALS['_PJ_effort_table'] .
					  ".date < '$next_year-$next_month-01'" .
					  " AND " .
					  $GLOBALS['_PJ_effort_table'] . ".billed IS ";
			if($this->mode == 'billed') {
				$query .= "NOT ";
			}
			$query .= "NULL ORDER BY " . $GLOBALS['_PJ_effort_table'] . ".date ASC, " .
			$GLOBALS['_PJ_effort_table'] . ".begin ASC";

			$this->db->query($query);

			while($this->db->next_record()) {
				list($year, $month, $day) = explode("-", $this->db->Record['date']);
				$seconds = calculate('seconds', $this->db->Record['date'], $this->db->Record['begin'], $this->db->Record['end']);
				$this->data['seconds']	+= $seconds;
				$this->days[$day]		+= $seconds;
				$this->db->Record['seconds'] = $seconds;
				$this->efforts[]		 = new Effort($this->db->Record);
				$this->effort_count++;
			}
			$this->data['minutes']			= round(($this->data['seconds']	/ 60), 2);
			$this->data['hours']			= round(($this->data['minutes']	/ 60), 2);
			$this->data['days']				= round(($this->data['minutes']	/ 60 / 8), 2);
			$this->data['customer_id']		= $this->customer_id;
		}

		function loadProject($year, $month, $pid) {
			if(!is_object($this->db)) {
				$this->db = new Database;
			}
			$next_date = formatDate("$year-" . ($month+1) . "-01", "Y-m-d");

			$query = "SELECT " . $GLOBALS['_PJ_effort_table'] . ".*, " .
					 $GLOBALS['_PJ_project_table'] . ".project_name, " .
					 $GLOBALS['_PJ_project_table'] . ".customer_id " .
					 " FROM " .
					 $GLOBALS['_PJ_effort_table'] . ", " .
					 $GLOBALS['_PJ_project_table'] .
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
					 $GLOBALS['_PJ_effort_table'] . ".billed IS ";
			if($this->mode == 'billed') {
				$query .= "NOT ";
			}
			$query .= "NULL ORDER BY " . $GLOBALS['_PJ_effort_table'] . ".date, " .
			$GLOBALS['_PJ_effort_table'] . ".begin";

			$this->db->query($query);

			while($this->db->next_record()) {
				list($year, $month, $day) = explode("-", $this->db->Record['date']);
				$seconds = calculate('seconds', $this->db->Record['date'], $this->db->Record['begin'], $this->db->Record['end']);
				$this->data['seconds']	+= $seconds;
				$this->days[$day]		+= $seconds;
				$this->db->Record['seconds'] = $seconds;
				$this->efforts[]		 = new Effort($this->db->Record);
				$this->effort_count++;
			}
			$this->data['minutes']			= round(($this->data['seconds']	/ 60), 2);
			$this->data['hours']			= round(($this->data['minutes']	/ 60), 2);
			$this->data['days']				= round(($this->data['minutes']	/ 60 / 8), 2);
			$this->data['customer_id']		= $this->customer_id;
		}

		function loadProjectTime($start, $end, $pid) {
			if(!is_object($this->db)) {
				$this->db = new Database;
			}

			$query = "SELECT " . $GLOBALS['_PJ_effort_table'] . ".*, " .
					 $GLOBALS['_PJ_project_table'] . ".project_name, " .
					 $GLOBALS['_PJ_project_table'] . ".customer_id " .
					 " FROM " .
					 $GLOBALS['_PJ_effort_table'] . ", " .
					 $GLOBALS['_PJ_project_table'] .
					 " WHERE " .
					 $GLOBALS['_PJ_effort_table'] . ".project_id=" .
					 $GLOBALS['_PJ_project_table'] . ".id" .
					 " AND " .
					 $GLOBALS['_PJ_effort_table'] . ".project_id=$pid" .
					 " AND " .
					 $GLOBALS['_PJ_project_table'] . ".customer_id='" . $this->customer_id . "'" .
					 " AND " .
					 $GLOBALS['_PJ_effort_table'] . ".date >= '$start'" .
					 " AND " .
					 $GLOBALS['_PJ_effort_table'] . ".date <= '$end'" .
					 " AND " .
					 $GLOBALS['_PJ_effort_table'] . ".billed IS ";
			if($this->mode == 'billed') {
				$query .= "NOT ";
			}
			$query .= "NULL ORDER BY " . $GLOBALS['_PJ_effort_table'] . ".date, " .
			$GLOBALS['_PJ_effort_table'] . ".begin";

			$this->db->query($query);

			while($this->db->next_record()) {
				list($year, $month, $day) = explode("-", $this->db->Record['date']);
				$seconds = calculate('seconds', $this->db->Record['date'], $this->db->Record['begin'], $this->db->Record['end']);
				$this->data['seconds']	+= $seconds;
				$this->days[$day]		+= $seconds;
				$this->db->Record['seconds'] = $seconds;
				$this->efforts[]		 = new Effort($this->db->Record);
				$this->effort_count++;
			}
			$this->data['minutes']			= round(($this->data['seconds']	/ 60), 2);
			$this->data['hours']			= round(($this->data['minutes']	/ 60), 2);
			$this->data['days']				= round(($this->data['minutes']	/ 60 / 8), 2);
			$this->data['customer_id']		= $this->customer_id;
		}

		function loadTime($start, $end = '') {
			if(!is_object($this->db)) {
				$this->db = new Database;
			}

			if($end == '') {
				$end = date("Y-m-d");
			} else {
				$end = date("Y-m-d", strtotime($end));
			}

			$start = date("Y-m-d", strtotime($start));


			$query = "SELECT " . $GLOBALS['_PJ_effort_table'] . ".*, " .
					 $GLOBALS['_PJ_customer_table'] . ".customer_name, " .
					 $GLOBALS['_PJ_project_table'] . ".customer_id, " .
					 $GLOBALS['_PJ_project_table'] . ".project_name " .
					 " FROM " .
					 $GLOBALS['_PJ_effort_table'] . ", " .
					 $GLOBALS['_PJ_customer_table'] . ", " .
					 $GLOBALS['_PJ_project_table'] .
					 " WHERE " .
					 $GLOBALS['_PJ_effort_table'] . ".project_id=" .
					 $GLOBALS['_PJ_project_table'] . ".id AND " .
					 $GLOBALS['_PJ_project_table'] . ".customer_id=" .
					 $GLOBALS['_PJ_customer_table'] . ".id";
			if($this->customer_id) {
				$query .= " AND " .
						  $GLOBALS['_PJ_project_table'] . ".customer_id='" . $this->customer_id . "'";
			}
			if($this->project_id) {
				$query .= " AND " .
						  $GLOBALS['_PJ_effort_table'] . ".project_id='" . $this->project_id . "'";
			}
			$query .= " AND " .
					 $GLOBALS['_PJ_effort_table'] . ".date >= '$start'" .
					 " AND " .
					 $GLOBALS['_PJ_effort_table'] . ".date <= '$end'";

			if($this->mode != 'billed') {
				$query .= " AND " . $GLOBALS['_PJ_effort_table'] . ".billed IS NULL";
			}

			$query .= " ORDER BY " . $GLOBALS['_PJ_effort_table'] . ".date ASC, " .
			$GLOBALS['_PJ_effort_table'] . ".begin ASC";

			$this->db->query($query);
			while($this->db->next_record()) {
				list($year, $month, $day) = explode("-", $this->db->Record['date']);
				$seconds = calculate('seconds', $this->db->Record['date'], $this->db->Record['begin'], $this->db->Record['end']);
				$this->data['seconds']	+= $seconds;
				$this->days[$day]		+= $seconds;
				$this->db->Record['seconds'] = $seconds;
				$this->efforts[]		 = new Effort($this->db->Record);
				$this->effort_count++;
			}
			$this->data['minutes']			= round(($this->data['seconds']	/ 60), 2);
			$this->data['hours']			= round(($this->data['minutes']	/ 60), 2);
			$this->data['days']				= round(($this->data['minutes']	/ 60 / 8), 2);
			$this->data['customer_id']		= $this->customer_id;
		}

		function giveValue($key) {
			return $this->data[$key];
		}

		function calculatePrice($starttime, $endtime) {
			$query = "SELECT price, currency FROM " . $GLOBALS['_PJ_rate_table'] .
					 " WHERE customer_id=" . $this->customer_id .
					 " AND starttime >= '" . $startime . "'".
					 " AND endtime >= '" . $endime . "'";
			$this->db->query($query);
		}

		function count() {
			return $this->effort_count;
		}
	}
?>