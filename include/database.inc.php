<?php
	if(!isset($_PJ_include_path)) {
		print "\$_PJ_include_path ist nicht festgelegt (" . __FILE__ . ", Zeile: " . __LINE__ . ")";
		exit;
	}

	include_once("$_PJ_include_path/db_mysql.inc.php");

	class Database extends DB_Sql {
		function Database($query = NULL) {
			$this->Host     = $GLOBALS['_PJ_db_host'];
			$this->Database = $GLOBALS['_PJ_db_database'];
			$this->User     = $GLOBALS['_PJ_db_user'];
			$this->Password = $GLOBALS['_PJ_db_password'];

			$this->query($query);
		}
	}
?>
