<?php
	if(!isset($_PJ_root)) {
		print "<b>FEHLER:</b> \$_PJ_root ist <b>nicht festgelegt</b>! (" . __FILE__ . ", Zeile: " . __LINE__ . ")";
		exit;
	}

	class EffortList {
		var $db;
		var $data;
		var $efforts;
		var $show_billed = false;
		var $effort_count	= 0;
		var $effort_cursor	= -1;

		function EffortList(&$customer, &$project, &$user, $show_billed = false, $limit = NULL) {
			$this->customer	= $customer;
			$this->project	= $project;
			$this->user		= $user;
			$this->db = new Database;
			$this->showBilled($show_billed);

			if(!$user->checkPermission('admin')) {
				$access_query  = " AND (";
				$access_query .= " ("	. $GLOBALS['_PJ_effort_table'] . ".user = '" . $user->giveValue('id') . "' AND "	. $GLOBALS['_PJ_effort_table'] . ".access LIKE 'r________')";
				$access_query .= " OR ";
				$access_query .= " ("	. $GLOBALS['_PJ_effort_table'] . ".gid IN (" . $user->giveValue('gids') . ") AND "	. $GLOBALS['_PJ_effort_table'] . ".access LIKE '___r_____')";
				$access_query .= " OR ";
				$access_query .= " ("	. $GLOBALS['_PJ_effort_table'] . ".access LIKE '______r__')";
				$access_query .= " ) ";
				$raw_access_query  = " AND (";
				$raw_access_query .= " (user = '" . $user->giveValue('id') . "' AND access LIKE 'r________')";
				$raw_access_query .= " OR ";
				$raw_access_query .= " (gid IN (" . $user->giveValue('gids') . ") AND access LIKE '___r_____')";
				$raw_access_query .= " OR ";
				$raw_access_query .= " (access LIKE '______r__')";
				$raw_access_query .= " ) ";
			}

			$query  = "SELECT "	. $GLOBALS['_PJ_effort_table'] . ".* ";
			$query .= " FROM "	. $GLOBALS['_PJ_effort_table'];
			$query .= ", " 		. $GLOBALS['_PJ_project_table'];
			$query .= ", " 		. $GLOBALS['_PJ_customer_table'];
			$query .= " WHERE "	. $GLOBALS['_PJ_effort_table'] . ".project_id=";
			$query .= $GLOBALS['_PJ_project_table'] . ".id";
			$query .= " AND "	. $GLOBALS['_PJ_project_table'] . ".customer_id=";
			$query .= $GLOBALS['_PJ_customer_table'] . ".id";
			if(is_object($project) && $project->giveValue('id')) {
				$query .= " AND project_id='" . $project->giveValue('id') . "'";
				$order_query = ' ORDER BY billed, date, begin';
				$limit_query = '';
			} else if(is_object($customer) && $customer->giveValue('id')) {
				$query .= " AND "	. $GLOBALS['_PJ_customer_table'] . ".id='" . $customer->giveValue('id') . "'";
				$order_query = ' ORDER BY billed, last DESC, date, begin';
				$limit_query = ' LIMIT 1000';
			} else {
				$this->db->query("SELECT id FROM " . $GLOBALS['_PJ_customer_table'] . " WHERE 1 $raw_access_query");
				while($this->db->next_record()) {
					if($cids) {
						$cids .= ',';
					}
					$cids .= $this->db->f('id');
				}
				if(!$cids) {
					return;
				}
				$this->db->query("SELECT id FROM " . $GLOBALS['_PJ_project_table'] . " WHERE customer_id IN ($cids) $raw_access_query");
				while($this->db->next_record()) {
					if($pids) {
						$pids .= ',';
					}
					$pids .= $this->db->f('id');
				}
				if(!$pids) {
					return;
				}
				$query .= " AND project_id IN ($pids)";
				$order_query = ' ORDER BY billed, last DESC, date, begin';
				$limit_query = ' LIMIT 1000';
			}
			if($limit) {
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
	}

	class Effort extends Data {
		var $db;
		var $data;

		function Effort($effort = '', &$user) {
			$this->user = $user;
			if(is_array($effort)) {
				$this->data = $effort;
			} else if($effort != '') {
				$this->load($effort);
			}
			$this->user_access = $this->getUserAccess();
			$this->initEffort();
		}

		function load($id) {
			if(!is_object($this->db)) {
				$this->db = new Database;
			}

			$query = "SELECT * FROM " . $GLOBALS['_PJ_effort_table'] . " WHERE id='$id'";
			$this->db->query($query);
			if($this->db->next_record()) {
				$this->data = $this->db->Record;
			}
		}

		function initEffort () {
			list($year, $month, $day) = explode("-", $this->data['date']);
			list($b_hour, $b_minute, $b_second) = explode(":", $this->data['begin']);
			list($e_hour, $e_minute, $e_second) = explode(":", $this->data['end']);
			$b_time = mktime($b_hour, $b_minute, $b_second, $month, $day, $year);
			$e_time = mktime($e_hour, $e_minute, $e_second, $month, $day, $year);

			if($this->data['billed'] != '') {
				$this->data['billed_seconds']	= ($e_time - $b_time);
				$this->data['billed_minutes']	= round($this->data['billed_seconds']	/ 60, 0);
				$this->data['billed_hours']		= round($this->data['billed_seconds']	/ 3600, 2);
				$this->data['billed_days']		= round($this->data['billed_seconds']	/ 28800, 2);
			}
			$this->data['seconds']	= ($e_time - $b_time);
			$this->data['minutes']	= round($this->data['seconds']	/ 60, 0);
			$this->data['hours']	= round($this->data['seconds']	/ 3600, 2);
			$this->data['days']		= round($this->data['seconds']	/ 28800, 2);
			$this->data['costs']	+= $this->data['hours'] * $this->data['rate'];
		}

		function save () {
			if(!is_object($this->db)) {
				$this->db = new Database;
			}

			list($year, $month, $day) = explode("-", $this->data['date']);
			list($b_hour, $b_minute, $b_second) = explode(":", $this->data['begin']);
			list($e_hour, $e_minute, $e_second) = explode(":", $this->data['end']);

			$b_timestamp = mktime($b_hour, $b_minute, $b_second, $month, $day, $year);
			$e_timestamp = mktime($e_hour, $e_minute, $e_second, $month, $day, $year);

			if((date("Y", $b_timestamp) <= 1970) ||
			   (date("Y", $e_timestamp) <= 1970	))
				return;

			if(date("H", $b_timestamp) > date("H", $e_timestamp)) {
				$b_time	= "00:00:00";
				$date	= date("Y-m-d", $b_timestamp+86400);
				$e_time	= date("H:i:s", $e_timestamp);

				$query = "INSERT INTO " . $GLOBALS['_PJ_effort_table'] . " (project_id, gid, access, date, begin, end, description, note, rate, user, billed, last)";
				$query .= " VALUES(";
				$query .= "'" . $this->data['project_id'] . "', ";
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
			}

			$query = "REPLACE INTO " . $GLOBALS['_PJ_effort_table'] . " (id, project_id, gid, access, date, begin, end, description, note, rate, user, billed)";
			$query .= " VALUES(";
			$query .= "'" . $this->data['id'] . "', ";
			$query .= "'" . $this->data['project_id'] . "', ";
			$query .= "'" . $this->data['gid'] . "', ";
			$query .= "'" . $this->data['access'] . "', ";
			$query .= "'" . $this->data['date'] . "', ";
			$query .= "'" . $b_time . "', ";
			$query .= "'" . $e_time . "', ";
			$query .= "'" . $this->data['description'] . "', ";
			$query .= "'" . $this->data['note'] . "', ";
			$query .= "'" . $this->data['rate'] . "', ";
			$query .= "'" . $this->data['user'] . "', ";
			$query .= $this->data['billed'] . ")";
			$this->db->query($query);

			$query = "UPDATE " . $GLOBALS['_PJ_project_table'] . " SET last=NOW() WHERE id='" . $this->data['project_id'] . "'";
			$this->db->query($query);
		}

		function delete() {
			if(!is_object($this->db)) {
				$this->db = new Database;
			}

			if(!$this->data['id']) {
				return;
			}
			$query = "DELETE FROM " . $GLOBALS['_PJ_effort_table'] . " WHERE id=" . $this->data['id'];
			$this->db->query($query);
		}

		function setEndTime ($effort) {
			list($year, $month, $day) = explode("-", $this->data['date']);
			list($b_hour, $b_minute, $b_second) = explode(":", $this->data['begin']);
			list($e_hour, $e_minute, $e_second) = explode(":", $effort);
			$b_timestamp = mktime($b_hour, $b_minute, $b_second, $month, $day, $year);
			$e_timestamp = $b_timestamp + $e_hour*3600 + $e_minute*60 + $e_second;
			$this->data['end'] = date("H:i:s", $e_timestamp);
		}
	}
?>