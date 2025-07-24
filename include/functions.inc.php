<?php
	function formatNumber($number, $force_float = false) {
		$number = number_format($number, 2, $GLOBALS['_PJ_decimal_point'] , $GLOBALS['_PJ_thousands_seperator']);
		if(empty($force_float)) {
			$number = preg_replace("/\\" . $GLOBALS['_PJ_decimal_point'] . "00/", '', $number);
		}
		return $number;
	}

	function formatDate($date, $format = NULL) {
		if(empty($format)) {
			$format = $GLOBALS['_PJ_format_date'];
		}
		if($date == '') {
			return NULL;
		}
		list($year, $month, $day) = explode("-", $date);
		$timestamp = mktime(0, 0, 0, $month, $day, $year);
		return date($format, $timestamp);
	}

	function formatTime($time, $format = "H:i:s") {
		list($hour, $minute, $second) = explode(":", $time);
		$timestamp = mktime($hour, $minute, $second, 1, 1, 2002);
		return date($format, $timestamp);
	}

	function calculate($what, $date, $begin, $end) {
		list($year, $month, $day) = explode("-", $date);
		list($b_hour, $b_minute, $b_second) = explode(":", $begin);
		list($e_hour, $e_minute, $e_second) = explode(":", $end);
		$b_time = mktime($b_hour, $b_minute, $b_second, $month, $day, $year);
		$e_time = mktime($e_hour, $e_minute, $e_second, $month, $day, $year);

		switch ($what) {
			case 'seconds':
				return ($e_time - $b_time);
			default:
				return calculateFromSeconds($what, ($e_time - $b_time));
		}
	}
	function calculateFromSeconds($what, $seconds) {
		switch ($what) {
			case 'seconds':
				return ($seconds);
			case 'minutes':
				return ($seconds / 60);
			case 'hours':
				return ($seconds / 3600); // 60 * 60
			case 'days':
				return ($seconds / 28800); // 60 * 60 * 8
			case 'weeks':
				return ($seconds / 144000); // 60 * 60 * 8 * 5
			case 'months':
				return ($seconds / 604800); // 60 * 60 * 8 * 5 * 4.2
			case 'years':
				return ($seconds / 7257600); // 60 * 60 * 8 * 5 * 4.2 * 12
			default:
				return 0;
		}
	}

	function add_slashes($string) {
		// FIX: Null-Prüfung für PHP 8.4 Kompatibilität
		if ($string === null) {
			return '';
		}
		if(((bool) ini_get('magic_quotes_gpc'))) {
			return $string;
		}
		return addslashes($string);
	}

	function unhtmlentities($string) {
		$trans_tbl =get_html_translation_table (HTML_ENTITIES );
		$trans_tbl =array_flip ($trans_tbl );
		return strtr ($string ,$trans_tbl );
	}
?>