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
	} /* End of class Data*/
?>