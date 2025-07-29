<<<<<<< HEAD
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

	// Run migrations automatically when this file is included during login process
	// This ensures migrations run after database connection is established but before authentication
	if (php_sapi_name() !== 'cli' && isset($GLOBALS['_PJ_auth_table'])) {
		runDatabaseMigrations();
	}
=======
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
>>>>>>> master
?>
