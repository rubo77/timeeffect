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

	// Load and run automatic database migrations after Database class is available
	if (!defined('MIGRATIONS_LOADED')) {
		define('MIGRATIONS_LOADED', true);
		
		// Ensure config variables are loaded before running migrations
		if (!isset($GLOBALS['_PJ_db_prefix'])) {
			// Config not loaded yet - defer migration until config is available
			return;
		}
		
		require_once(__DIR__ . '/migrations.inc.php');
		
		// Run migrations automatically (non-blocking)
		try {
			$migrations_run = checkAndRunMigrations();
			if (!empty($migrations_run) && isset($GLOBALS['logger'])) {
				$GLOBALS['logger']->info('Automatic database migrations completed', ['migrations' => $migrations_run]);
			}
		} catch (Exception $e) {
			if (isset($GLOBALS['logger'])) {
				$GLOBALS['logger']->error('Database migration check failed', ['error' => $e->getMessage()]);
			}
			error_log('TimeEffect migration error: ' . $e->getMessage());
		}
	}
?>
