<?php
	// Include security layer
	require_once(__DIR__ . '/security.inc.php');
	// Include secure defaults for new user permissions
	require_once(__DIR__ . '/secure_defaults.inc.php');
	// Include centralized ACL query functions
	require_once(__DIR__ . '/acl_query.inc.php');
	
	class CustomerList {
		var $db;
		var $data;
		var $customers;
		var $customer_count	= 0;
		var $customer_cursor	= -1;
		var $inactive_count = 0; // Deklaration der vorher dynamischen Property

		function __construct(&$user, $inactive = '') {
			$debugmessage=false;
			if($debugmessage) {
				// DEBUG: Ausgabe für Customer-Listen-Problem
				echo '<div style="background-color: #fff3cd; color: #856404; padding: 10px; margin: 10px; border: 1px solid #ffeaa7; border-radius: 4px;">';
				echo '<strong>CustomerList Debug - Constructor called!</strong><br>';
			}
		
			global $_PJ_customer_table;

			$this->db = new Database;
			$query = "SELECT * FROM $_PJ_customer_table";
			if(empty($inactive)) {
				$query .= " WHERE active='yes'";
			} else {
				$query .= " WHERE 1";
			}
			$access_query="";
			// Use centralized ACL query function
			$access_query = buildCustomerAclQuery($user);
			
			// ACL_DEBUG: Log user permissions and ACL query
			debugLog("ACL_DEBUG", "xxxCustomerList: user_id=" . $user->giveValue('id') . ", is_admin=" . ($user->checkPermission('admin') ? 'YES' : 'NO') . ", gids=" . $user->giveValue('gids'));
			if(!empty($access_query)) {
				debugLog("ACL_DEBUG", "CustomerList access_query: " . $access_query);
			} else {
				debugLog("ACL_DEBUG", "CustomerList: User is admin - no ACL filtering applied");
			}
			$query .= $access_query;
			$query .= " ORDER BY customer_name";
			debugLog("ACL_DEBUG", "CustomerList final query: " . $query);
		
			if($debugmessage) {
				echo 'Query: ' . htmlspecialchars($query) . '<br>';
				echo 'Table: ' . htmlspecialchars($_PJ_customer_table) . '<br>';
			}

			$this->db->query($query);
			$this->customers = array();
			while($this->db->next_record()) {
				if($debugmessage) {
					echo 'Raw DB Record: <pre>' . print_r($this->db->Record, true) . '</pre>';
				}
				$customer = new Customer($user, $this->db->Record);
				debugLog("ACL_DEBUG", "CustomerList loaded customer: id=" . $this->db->Record['id'] . ", name=" . $this->db->Record['customer_name'] . ", access=" . $this->db->Record['access'] . ", user=" . $this->db->Record['user'] . ", gid=" . $this->db->Record['gid']);
				$this->customers[] = $customer;
				$name = $customer->giveValue('customer_name');
				$id = $customer->giveValue('id');
				if($debugmessage) {
					echo 'Loaded customer: "' . ($name ? htmlspecialchars($name) : 'NULL/EMPTY') . '" (ID: "' . ($id ? htmlspecialchars($id) : 'NULL/EMPTY') . '")<br>';
				}
				$this->customer_count++;
			}
			if($debugmessage) {
				echo 'Total customers loaded: ' . $this->customer_count . '<br>';
				echo '</div>';
			}

			$this->inactive_count = 0;
			$query = "SELECT count(id) FROM $_PJ_customer_table WHERE active='no'" . $access_query;
			$this->db->query($query);
			if($this->db->next_record()) {
				$this->inactive_count = $this->db->Record[0];
			}
		}

		function nextCustomer() {
			$this->customer_cursor++;
			if($this->customer_count == $this->customer_cursor)
				return FALSE;
			return TRUE;
		}

		function giveCustomer() {
			return $this->customers[$this->customer_cursor];
		}
	}

	class Customer extends Data {
		var $db;
		var $data;
		var $project_count	= '';
		var $user; // Deklaration der vorher dynamischen Property
		var $user_access; // Deklaration der vorher dynamischen Property

		function __construct(&$user, $customer = '') {
			$debugmessage=false;
			$this->user = $user;
			if(is_array($customer)) {
				$this->data = $customer;
				if($debugmessage) {
					echo 'Customer constructor: Data set from array. customer_name=' . (isset($customer['customer_name']) ? $customer['customer_name'] : 'NOT_SET') . ', id=' . (isset($customer['id']) ? $customer['id'] : 'NOT_SET') . '<br>';
				}
			} else if(is_string($customer) && $customer != '') {
				$this->load($customer);
				if($debugmessage) {
					echo 'Customer constructor: Data loaded from DB for ID=' . $customer . '<br>';
				}
			} else {
				if($debugmessage) {
					echo 'Customer constructor: No data provided<br>';
				}
			}
			// Always call getUserAccess() - it handles null access values properly
			$this->user_access = $this->getUserAccess();
		}

		function load($id) {
			if(!isset($this->db) or !is_object($this->db)) {
				$this->db = new Database;
			}

			$safeId = DatabaseSecurity::escapeInt($id);
			$safeTable = DatabaseSecurity::sanitizeColumnName($GLOBALS['_PJ_customer_table']);
			$query = "SELECT * FROM {$safeTable} WHERE id={$safeId}";
			$this->db->query($query);
			if($this->db->next_record()) {
				$this->data = $this->db->Record;
			}
			
			$safeProjectTable = DatabaseSecurity::sanitizeColumnName($GLOBALS['_PJ_project_table']);
			$query = "SELECT COUNT(id) FROM {$safeProjectTable} WHERE customer_id={$safeId}";
			$access_query="";
			if(!$this->user->checkPermission('admin')) {
				// Ensure database connection is established
				if(empty($this->db->Link_ID)) {
					$this->db->connect(
						$GLOBALS['_PJ_db_database'],
						$GLOBALS['_PJ_db_host'],
						$GLOBALS['_PJ_db_user'],
						$GLOBALS['_PJ_db_password']
					);
				}
				$safeUserId = DatabaseSecurity::escapeInt($this->user->giveValue('id'));
				$safeUserGids = DatabaseSecurity::escapeString($this->user->giveValue('gids'), $this->db->Link_ID);
				$access_query  = " AND (";
				$access_query .= " (user = '{$safeUserId}' AND access LIKE 'r________')";
				$access_query .= " OR ";
				$access_query .= " (gid IN ({$safeUserGids}) AND access LIKE '___r_____')";
				$access_query .= " OR ";
				$access_query .= " (access LIKE '______r__')";
				$access_query .= " ) ";
			}
			$query .= $access_query;
			$this->db->query($query);
			if($this->db->next_record()) {
				$this->project_count = $this->db->Record[0];
			}
		}

		function count($closed = false) {
			if($this->project_count != '') {
//				return $this->project_count;
			}
			if(!isset($this->db) or !is_object($this->db)) {
				$this->db = new Database;
			}

			// Sicherheitsprüfung für $this->data['id']
			if(!isset($this->data['id'])) {
				return 0; // Wenn keine ID vorhanden ist, gibt es keine Projekte
			}

			$safeCustomerId = DatabaseSecurity::escapeInt($this->data['id']);
			$safeProjectTable = DatabaseSecurity::sanitizeColumnName($GLOBALS['_PJ_project_table']);
			$query = "SELECT COUNT(id) FROM {$safeProjectTable} WHERE customer_id={$safeCustomerId}";
			$access_query="";
			
			// Überprüfen, ob $this->user ein Objekt ist
			if(!isset($this->user) || !is_object($this->user)) {
				// Wenn kein User-Objekt vorhanden ist, keine Zugriffseinschränkung
			} 
			// Nur wenn $this->user ein Objekt ist, prüfen wir die Berechtigung
			elseif(!$this->user->checkPermission('admin')) {
				// Ensure database connection is established
				if(empty($this->db->Link_ID)) {
					$this->db->connect(
						$GLOBALS['_PJ_db_database'],
						$GLOBALS['_PJ_db_host'],
						$GLOBALS['_PJ_db_user'],
						$GLOBALS['_PJ_db_password']
					);
				}
				$safeUserId = DatabaseSecurity::escapeInt($this->user->giveValue('id'));
				$safeUserGids = DatabaseSecurity::escapeString($this->user->giveValue('gids'), $this->db->Link_ID);
				$access_query  = " AND (";
				$access_query .= " (user = '{$safeUserId}' AND access LIKE 'r________')";
				$access_query .= " OR ";
				$access_query .= " (gid IN ({$safeUserGids}) AND access LIKE '___r_____')";
				$access_query .= " OR ";
				$access_query .= " (access LIKE '______r__')";
				$access_query .= " ) ";
			}
			if(empty($closed)) {
					$query .= " AND closed = 'No'";
			}
			$query .= $access_query;
			$this->db->query($query);
			if($this->db->next_record()) {
				$this->project_count = $this->db->Record[0];
				return $this->project_count;
			}
		}

		function loadEffort () {
			if(!$this->data['id'] || ($this->data['seconds'] > 0))
				return;

			$project_list = new ProjectList($this, $this->user);
			while($project_list->nextProject()) {
				$project = $project_list->giveProject();
				$this->data['seconds']	+= $project->giveValue('seconds');
				$this->data['minutes']	+= $project->giveValue('minutes');
				$this->data['hours']	+= $project->giveValue('hours');
				$this->data['days']		+= $project->giveValue('days');
				$this->data['billed_seconds']	+= $project->giveValue('billed_seconds');
				$this->data['billed_minutes']	+= $project->giveValue('billed_minutes');
				$this->data['billed_hours']		+= $project->giveValue('billed_hours');
				$this->data['billed_days']		+= $project->giveValue('billed_days');
			}
		}

		function save () {
			if(!isset($this->db) or !is_object($this->db)) {
				$this->db = new Database;
			}

			// FIX: Fehlende Formularfelder aus REQUEST übernehmen (analog zu Group/User)
			foreach (array('id', 'user', 'gid', 'active', 'customer_name') as $field) {
				if (isset($_REQUEST[$field]) && (!isset($this->data[$field]) || empty($this->data[$field]))) {
					$this->data[$field] = DatabaseSecurity::validateInput($_REQUEST[$field], 'string');
				}
			}
			
			// Apply secure defaults for new customers if enabled
			if (isSecureDefaultsEnabled()) {
				global $_PJ_auth;
				if (isset($_PJ_auth) && is_object($_PJ_auth)) {
					$current_user_id = $_PJ_auth->giveValue('id');
					$current_group_id = $_PJ_auth->giveValue('gid');
					
					// Apply secure customer defaults
					$this->data = applySecureCustomerDefaults($this->data, $current_user_id, $current_group_id);
					
					// Log security action for audit
					logSecurityAction('customer_created', $current_user_id, $this->data);
				}
			}

			$safeTable = DatabaseSecurity::sanitizeColumnName($GLOBALS['_PJ_customer_table']);
			$query = "REPLACE INTO {$safeTable} (";

			// FIX: Prüfung auf isset für alle Array-Keys (PHP 8.4 Kompatibilität)
			if(isset($this->data['id']) && $this->data['id']) {
				$query .= "id, ";
			}
			$query .= "active, user, gid, access, readforeignefforts, customer_name, customer_desc, customer_budget, customer_budget_currency, customer_logo) VALUES(";
			if(isset($this->data['id']) && $this->data['id']) {
				$safeId = DatabaseSecurity::escapeInt($this->data['id']);
				$query .= $safeId . ", ";
			}
			
			// Sanitize all input values
			$safeActive = DatabaseSecurity::escapeString(isset($this->data['active']) ? $this->data['active'] : 'yes');
			$safeUser = DatabaseSecurity::escapeString(isset($this->data['user']) ? $this->data['user'] : '');
			$safeGid = DatabaseSecurity::escapeString(isset($this->data['gid']) ? $this->data['gid'] : '');
			$safeAccess = DatabaseSecurity::escapeString(isset($this->data['access']) ? $this->data['access'] : 'rwxr-xr--');
			$safeReadForeignEfforts = DatabaseSecurity::escapeString(isset($this->data['readforeignefforts']) ? $this->data['readforeignefforts'] : '0');
			$safeCustomerName = DatabaseSecurity::escapeString(isset($this->data['customer_name']) ? $this->data['customer_name'] : '');
			$safeCustomerDesc = DatabaseSecurity::escapeString(isset($this->data['customer_desc']) ? $this->data['customer_desc'] : '');
			$safeCustomerBudget = DatabaseSecurity::escapeString(isset($this->data['customer_budget']) ? $this->data['customer_budget'] : '0');
			$safeCustomerBudgetCurrency = DatabaseSecurity::escapeString(isset($this->data['customer_budget_currency']) ? $this->data['customer_budget_currency'] : 'EUR');
			$safeCustomerLogo = DatabaseSecurity::escapeString(isset($this->data['customer_logo']) ? $this->data['customer_logo'] : '');
			
			$query .= "'{$safeActive}', ";
			$query .= "'{$safeUser}', ";
			$query .= "'{$safeGid}', ";
			$query .= "'{$safeAccess}', ";
			$query .= "'{$safeReadForeignEfforts}', ";
			$query .= "'{$safeCustomerName}', ";
			$query .= "'{$safeCustomerDesc}', ";
			$query .= "'{$safeCustomerBudget}', ";
			$query .= "'{$safeCustomerBudgetCurrency}', ";
			$query .= "'{$safeCustomerLogo}')";
			
			if($this->db->query($query)) {
				$this->data['id'] = $this->db->insert_id();
				
				// Erfolgsmeldung anzeigen
				echo '<div style="background-color: #dff0d8; color: #3c763d; padding: 15px; margin: 15px; border: 1px solid #d6e9c6; border-radius: 4px;">';
				echo '<strong>Erfolg!</strong> Der Kunde wurde erfolgreich angelegt:<br><br>';
				echo '<strong>Name:</strong> ' . htmlspecialchars(isset($this->data['customer_name']) ? $this->data['customer_name'] : 'Unbekannt') . '<br>';
				echo '<strong>Budget:</strong> ' . htmlspecialchars(isset($this->data['customer_budget']) ? $this->data['customer_budget'] : '0') . ' ' . htmlspecialchars(isset($this->data['customer_budget_currency']) ? $this->data['customer_budget_currency'] : 'EUR') . '<br>';
				echo '<strong>Status:</strong> ' . (isset($this->data['active']) && $this->data['active'] == 'yes' ? 'Aktiv' : 'Inaktiv') . '<br>';
				if (isset($this->data['customer_desc']) && !empty($this->data['customer_desc'])) {
					echo '<strong>Beschreibung:</strong> ' . htmlspecialchars(substr($this->data['customer_desc'], 0, 100)) . (strlen($this->data['customer_desc']) > 100 ? '...' : '') . '<br>';
				}
				echo '</div>';
			} else {
				// Fehler beim Speichern - Debug-Ausgaben anzeigen
				echo '<div style="background-color: #f2dede; color: #a94442; padding: 15px; margin: 15px; border: 1px solid #ebccd1; border-radius: 4px;">';
				echo '<strong>Fehler!</strong> Der Kunde konnte nicht gespeichert werden.<br><br>';
				echo '<strong>Datenbankfehler:</strong> ' . htmlspecialchars($this->db->Error) . '<br>';
				echo '<strong>SQL-Query:</strong> ' . htmlspecialchars($query) . '<br>';
				echo '</div>';
			}
		}

		function delete() {
			if(!isset($this->db) or !is_object($this->db)) {
				$this->db = new Database;
			}

			if(!$this->data['id']) {
				return;
			}

			$safeTable = DatabaseSecurity::sanitizeColumnName($GLOBALS['_PJ_customer_table']);
			$safeId = DatabaseSecurity::escapeInt($this->data['id']);
			$query = "DELETE FROM {$safeTable} WHERE id={$safeId}";
			$project_list = new ProjectList($this, $this->user);
			while($project_list->nextProject()) {
				$project = $project_list->giveProject();
				$project->delete();
			}
			$this->db->query($query);
		}

		function bill($from, $to, $date) {
			if(!isset($this->db) or !is_object($this->db)) {
				$this->db = new Database;
			}

			if(!$this->data['id']) {
				return;
			}

			$query = "SELECT id FROM " . $GLOBALS['_PJ_project_table'] .
					  " WHERE customer_id=" . $this->data['id'];

			$this->db->query($query);
			while($this->db->next_record()) {
				$projects = new Project($this, $this->user, $this->db->Record);
				$projects->bill($from, $to, $date);
			}
		}

		function unbill($from, $to) {
			if(!isset($this->db) or !is_object($this->db)) {
				$this->db = new Database;
			}

			if(!$this->data['id']) {
				return;
			}

			$query = "SELECT id FROM " . $GLOBALS['_PJ_project_table'] .
					  " WHERE customer_id=" . $this->data['id'];

			$this->db->query($query);
			while($this->db->next_record()) {
				$projects = new Project($this, $this->user, $this->db->Record);
				$projects->unbill($from, $to, $date);
			}
		}
	}
?>
