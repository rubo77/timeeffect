<?php
	class CustomerList {
		var $db;
		var $data;
		var $customers;
		var $customer_count	= 0;
		var $customer_cursor	= -1;
		var $inactive_count = 0; // Deklaration der vorher dynamischen Property

		function CustomerList(&$user, $inactive = '') {
			self::__construct($user, $inactive);
		}
		function __construct(&$user, $inactive = '') {
			global $_PJ_customer_table;

			$this->db = new Database;
			$query = "SELECT * FROM $_PJ_customer_table";
			if(empty($inactive)) {
				$query .= " WHERE active='yes'";
			} else {
				$query .= " WHERE 1";
			}
			$access_query="";
			if(!$user->checkPermission('admin')) {
				$access_query  = " AND (";
				$access_query .= " (user = '" . $user->giveValue('id') . "' AND access LIKE 'r________')";
				$access_query .= " OR ";
				$access_query .= " (gid IN (" . $user->giveValue('gids') . ") AND access LIKE '___r_____')";
				$access_query .= " OR ";
				$access_query .= " (access LIKE '______r__')";
				$access_query .= " ) ";
			}
			$query .= $access_query;
			$query .= " ORDER BY customer_name";

			$this->db->query($query);
			$this->customers = array();
			while($this->db->next_record()) {
				$this->customers[] = new Customer($this->db->Record, $user);
				$this->customer_count++;
				$customer = $this->customers[$this->customer_count-1];
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

		function Customer(&$user, $customer = '') {
			self::__construct($user, $customer);
		}
		function __construct(&$user, $customer = '') {
			$this->user = $user;
			if(is_array($customer)) {
				$this->data = $customer;
			} else if(is_string($customer) && $customer != '') {
				$this->load($customer);
			}
			$this->user_access				= $this->getUserAccess();
		}

		function load($id) {
			if(!isset($this->db) or !is_object($this->db)) {
				$this->db = new Database;
			}

			$query = "SELECT * FROM " . $GLOBALS['_PJ_customer_table'] . " WHERE id='$id'";
			$this->db->query($query);
			if($this->db->next_record()) {
				$this->data = $this->db->Record;
			}
			$query = "SELECT COUNT(id) FROM " . $GLOBALS['_PJ_project_table'] . " WHERE customer_id='$id'";
			$access_query="";
			if(!$this->user->checkPermission('admin')) {
				$access_query  = " AND (";
				$access_query .= " (user = '" . $this->user->giveValue('id') . "' AND access LIKE 'r________')";
				$access_query .= " OR ";
				$access_query .= " (gid IN (" . $this->user->giveValue('gids') . ") AND access LIKE '___r_____')";
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

			$query = "SELECT COUNT(id) FROM " . $GLOBALS['_PJ_project_table'] . " WHERE customer_id='" . $this->data['id'] . "'";
			$access_query="";
			
			// Überprüfen, ob $this->user ein Objekt ist
			if(!isset($this->user) || !is_object($this->user)) {
				// Wenn kein User-Objekt vorhanden ist, keine Zugriffseinschränkung
			} 
			// Nur wenn $this->user ein Objekt ist, prüfen wir die Berechtigung
			elseif(!$this->user->checkPermission('admin')) {
				$access_query  = " AND (";
				$access_query .= " (user = '" . $this->user->giveValue('id') . "' AND access LIKE 'r________')";
				$access_query .= " OR ";
				$access_query .= " (gid IN (" . $this->user->giveValue('gids') . ") AND access LIKE '___r_____')";
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
					$this->data[$field] = $_REQUEST[$field];
				}
			}

			$query = "REPLACE INTO " . $GLOBALS['_PJ_customer_table'] . " (";

			// FIX: Prüfung auf isset für alle Array-Keys (PHP 8.4 Kompatibilität)
			if(isset($this->data['id']) && $this->data['id']) {
				$query .= "id, ";
			}
			$query .= "active, user, gid, access, readforeignefforts, customer_name, customer_desc, customer_budget, customer_budget_currency, customer_logo) VALUES(";
			if(isset($this->data['id']) && $this->data['id']) {
				$query .= $this->data['id'] . ", ";
			}
			$query .= "'" . (isset($this->data['active']) ? $this->data['active'] : 'yes') . "', ";
			$query .= "'" . (isset($this->data['user']) ? $this->data['user'] : '') . "', ";
			$query .= "'" . (isset($this->data['gid']) ? $this->data['gid'] : '') . "', ";
			$query .= "'" . (isset($this->data['access']) ? $this->data['access'] : 'rwxr-xr--') . "', ";
			$query .= "'" . (isset($this->data['readforeignefforts']) ? $this->data['readforeignefforts'] : '0') . "', ";
			$query .= "'" . (isset($this->data['customer_name']) ? $this->data['customer_name'] : '') . "', ";
			$query .= "'" . (isset($this->data['customer_desc']) ? $this->data['customer_desc'] : '') . "', ";
			$query .= "'" . (isset($this->data['customer_budget']) ? $this->data['customer_budget'] : '0') . "', ";
			$query .= "'" . (isset($this->data['customer_budget_currency']) ? $this->data['customer_budget_currency'] : 'EUR') . "', ";
			$query .= "'" . (isset($this->data['customer_logo']) ? $this->data['customer_logo'] : '') . "')";
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

			$query = "DELETE FROM " . $GLOBALS['_PJ_customer_table'] . " WHERE id=" . $this->data['id'];
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
