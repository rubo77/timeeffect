<?php
	class Data {
		function giveValue($key) {
			return $this->data[$key];
		}

		function formatNumber($number, $force_float = false, $decimals = 2) {
			$number = str_replace(',', '.', $number);
			$number = number_format($number, $decimals, "," , ".");
			if(!$force_float) {
				$number = preg_replace("/[0]*$/", '', $number);
				$number = preg_replace("/[\,]*$/", '', $number);
			}
			return $number;
		} /* End of function DataList::formatNumber() */

		function formatDate($date, $format = "d.m.Y") {
			if($date == '') {
				return NULL;
			}
			list($year, $month, $day) = explode("-", $date);
			/* workaround for Windows because Windows does not support dates before 1.1.1970 */
			if(strstr(PHP_OS, "WIN")) {
				$date = str_replace("d", $day, $format);
				$date = str_replace("m", $month, $date);
				$date = str_replace("Y", $year, $date);
				return $date;
			}
			$timestamp = mktime(0, 0, 0, $month, $day, $year);
			$error_reporting = error_reporting(0);
			$date = date($format, $timestamp);
			error_reporting($error_reporting);
			return $date;
		} /* End of function DataList::formatDate() */

		function formatTime($time, $format = "H:i:s") {
			if($time == '') {
				return NULL;
			}
			list($hour, $minute, $second) = explode(":", $time);
			/* workaround for Windows because Windows does not support dates before 1.1.1970 */
			if(strstr(PHP_OS, "WIN")) {
				$date = str_replace("H", $hour, $format);
				$date = str_replace("i", $minute, $date);
				$date = str_replace("s", $second, $date);
				return $date;
			}
			$timestamp = mktime($hour, $minute, $second, 1, 1, 2002);
			$error_reporting = error_reporting(0);
			$date = date($format, $timestamp);
			error_reporting($error_reporting);
			return $date;
		} /* End of function DataList::formatTime() */

		function checkUserAccess($mode = 'read') {
			return $this->user_access[$mode];
		}

		function getUserAccess() {
			if($this->user->checkPermission('admin')) {
				return array('read' => true, 'write' => true, 'new' => true);
			}
			$u_gids			= explode(',', $this->user->giveValue('gids'));
			$access_owner	= substr($this->giveValue('access'), 0, 3);
			$access_group	= substr($this->giveValue('access'), 3, 3);
			$access_world	= substr($this->giveValue('access'), 6, 3);
			if(substr($access_world, 0, 1) == 'r' ||
			   (in_array($this->giveValue('gid'), $u_gids) && substr($access_group, 0, 1) == 'r') ||
			   ($this->giveValue('user') == $this->user->giveValue('id') && substr($access_owner, 0, 1) == 'r')
			   ) {
				$user_access['read']		= true;
			} else {
				$user_access['read']		= false;
			}
			if((!$this->giveValue('billed') && $this->giveValue('billed') != '0000-00-00') && (
			    substr($access_world, 1, 1) == 'w' ||
			   (in_array($this->giveValue('gid'), $u_gids) && substr($access_group, 1, 1) == 'w') ||
			   ($this->giveValue('user') == $this->user->giveValue('id') && substr($access_owner, 1, 1) == 'w')
			   )) {
				$user_access['write']		= true;
			} else {
				$user_access['write']		= false;
			}
			if(substr($access_world, 2, 1) == 'x' ||
			   (in_array($this->giveValue('gid'), $u_gids) && substr($access_group, 2, 1) == 'x') ||
			   ($this->giveValue('user') == $this->user->giveValue('id') && substr($access_owner, 2, 1) == 'x')
			   ) {
				$user_access['new']		= true;
			} else {
				$user_access['new']		= false;
			}
			return $user_access;
		} /* End of function Data::getUserAccess() */

	} /* End of class Data*/	
?>