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

	/**
	 * Run database migrations automatically when database connection is established
	 * This ensures all schema updates are applied before the application runs
	 */
	function runDatabaseMigrations() {
		// Skip migrations for certain pages to avoid conflicts
		$current_script = basename($_SERVER['SCRIPT_NAME']);
		if ($current_script === 'migrate.php' || 
			strpos($_SERVER['REQUEST_URI'], '/install/') !== false ||
			strpos($_SERVER['REQUEST_URI'], '/sql/') !== false) {
			return;
		}

		// Only run migrations if configuration is properly loaded
		if (!isset($GLOBALS['_PJ_auth_table']) || 
			!isset($GLOBALS['_PJ_db_host']) || 
			!isset($GLOBALS['_PJ_db_prefix'])) {
			return; // Configuration not ready
		}

		try {
			include_once(__DIR__ . '/migrations.inc.php');
			$migrationManager = new MigrationManager();
			
			if ($migrationManager->migrationsNeeded()) {
				$migrations_run = $migrationManager->runPendingMigrations();
				
				if ($migrations_run !== false && count($migrations_run) > 0) {
					// Log successful migrations
					if (isset($GLOBALS['logger'])) {
						$GLOBALS['logger']->info('Database migrations completed', [
							'migrations_run' => $migrations_run
						]);
					}
				}
			}
		} catch (Exception $e) {
			// Log migration errors but don't break the application
			if (isset($GLOBALS['logger'])) {
				$GLOBALS['logger']->error('Migration system error', ['error' => $e->getMessage()]);
			}
			error_log("Migration system error: " . $e->getMessage());
		}
	}

	/**
	 * Trigger database migrations after auth initialization is complete
	 * Call this function from auth.inc.php after $_PJ_auth is properly initialized
	 */
	function triggerDatabaseMigrations() {
		if (!defined('MIGRATIONS_TRIGGERED')) {
			define('MIGRATIONS_TRIGGERED', true);
			
			// Only run migrations if we're not in CLI mode and auth is initialized
			if (php_sapi_name() !== 'cli' && 
				isset($GLOBALS['_PJ_auth_table']) && 
				isset($GLOBALS['_PJ_auth']) && 
				is_object($GLOBALS['_PJ_auth'])) {
				runDatabaseMigrations();
			}
		}
	}
?>
