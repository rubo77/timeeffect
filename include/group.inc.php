<?php
if(!isset($_PJ_root)) {
    print "<b>FEHLER:</b> \$_PJ_root ist <b>nicht festgelegt</b>! (" . __FILE__ . ", Zeile: " . __LINE__ . ")";
    exit;
}

class GroupList {
    var $db;
    var $data;
    var $groups;
    var $group_count	= 0;
    var $group_cursor	= -1;
    // Properties added for PHP 8.4 compatibility - replace deprecated each() function
    var $data_keys = array();
    var $data_pointer = 0;

    function __construct() {
        $debugmessage=false;
        if($debugmessage) {
            // DEBUG: Ausgabe für Gruppenübersicht-Problem (ganz am Anfang)
            echo '<div style="background-color: #fff3cd; color: #856404; padding: 10px; margin: 10px; border: 1px solid #ffeaa7; border-radius: 4px;">';
            echo '<strong>GroupList Debug - Constructor called!</strong><br>';
        }
        
        $this->db = new Database;

        $query = "SELECT * FROM " . $GLOBALS['_PJ_gid_table'];
        $query .= " ORDER BY name";

        if($debugmessage) {
            echo 'Query: ' . htmlspecialchars($query) . '<br>';
            echo 'Table: ' . htmlspecialchars($GLOBALS['_PJ_gid_table']) . '<br>';
        }
        
        $this->db->query($query);
        $this->groups = array(); // FIX: Korrektes Array initialisieren (war vorher falsch projects)
        
        if($debugmessage) {
            echo 'DB Error: ' . ($this->db->Errno ? $this->db->Error : 'None') . '<br>';
        }
        
        while($this->db->next_record()) {
            $this->groups[] = new Group($this->db->Record);
            $this->group_count++;
            if($debugmessage) {
                echo 'Loaded group: ' . htmlspecialchars($this->db->Record['name']) . '<br>';
            }
        }
        
        if($debugmessage) {
            echo 'Total groups loaded: ' . $this->group_count . '<br>';
            echo '</div>';
        }
    }

    function reset() {
        $this->group_cursor = -1;
    }

    function nextGroup() {
        $this->group_cursor++;
        if($this->group_count == $this->group_cursor)
            return FALSE;
        return TRUE;
    }

    function giveCount() {
        return $this->group_count;
    }

    function giveGroup() {
        return $this->groups[$this->group_cursor];
    }

    function giveValue($key) {
        if(isset($this->data[$key])) return $this->data[$key];
else return null;
    }
}

class Group {
    var $data = array();
    // Properties added for PHP 8.4 compatibility - replace deprecated each() function
    var $data_keys = array();
    var $data_pointer = 0;
    var $db; // Datenbankobjekt
    var $debug_exists; // Property für Debug-Ausgabe von exists()

    function __construct($data = '') {
        if(!isset($this->db) or !is_object($this->db)) {
            $this->db = new Database;
        }
        if(is_array($data)) {
            $this->data = $data;
            // Initialize array iteration variables for each() replacement
            $this->data_keys = array_keys($this->data);
            $this->data_pointer = 0;
            return;
        }

        $this->load($data);

    }

    function load($id = '') {
        // SQL injection protection: escape the ID parameter
        $db = new Database();
        $db->connect();
        $safeId = DatabaseSecurity::escapeString($id, $db->Link_ID);
        $query = "SELECT * FROM " . $GLOBALS['_PJ_gid_table'] . " WHERE id='$safeId'";
        $this->db->query($query);

        if($this->db->next_record()) {
            $this->data = $this->db->Record;
            // Initialize array iteration variables for each() replacement
            $this->data_keys = array_keys($this->data);
            $this->data_pointer = 0;
        }
    }

    function exists($name) {
        // Variable für Debug-Ausgabe
        $debug_output = "";
        
        // Debug-Informationen sammeln
        $debug_output .= "<pre style=\"background:#eee;padding:10px;margin:10px;border:1px solid #999;\">";
        $debug_output .= "<b>[Group::exists] Name:</b> " . htmlspecialchars($name) . "<br>";
        $debug_output .= "<b>REQUEST data:</b> ";
        $debug_output .= htmlspecialchars(print_r($_REQUEST, true));
        $debug_output .= "<b>this->data BEFORE fix:</b> ";
        $debug_output .= htmlspecialchars(print_r($this->data, true));
        
        // FIX: Fehlende Formularfelder aus REQUEST übernehmen
        if (isset($_REQUEST['id']) && !isset($this->data['id'])) {
            $this->data['id'] = $_REQUEST['id'];
        }
        
        $debug_output .= "<b>this->data AFTER fix:</b> ";
        $debug_output .= htmlspecialchars(print_r($this->data, true));
        
        // Bei leerer ID einen sicheren WHERE-Teil verwenden
        $id_condition = !empty($this->data['id']) ? " AND id <> '" . $this->data['id'] . "'" : "";
        // SQL injection protection: escape the name parameter
        $db = new Database();
        $db->connect();
        $safeName = DatabaseSecurity::escapeString($name, $db->Link_ID);
        $query = "SELECT * FROM " . $GLOBALS['_PJ_gid_table'] . " WHERE name='$safeName'" . $id_condition;
        $debug_output .= "<b>SQL Query:</b> " . htmlspecialchars($query);
        $debug_output .= "</pre>";
        $this->db->query($query);
        
        // Debug-Ausgabe in Klassenvariable speichern für spätere Verwendung im Fehlerfall
        $this->debug_exists = $debug_output;

        if($this->db->next_record()) {
            return true;
        }
        return false;
    }

    function retrieve($id, $value) {
        // SQL injection protection: escape the ID parameter
        $db = new Database();
        $db->connect();
        $safeId = DatabaseSecurity::escapeString($id, $db->Link_ID);
        $query = "SELECT $value FROM " . $GLOBALS['_PJ_gid_table'] . " WHERE id='$safeId'";
        $this->db->query($query);

        if($this->db->next_record()) {
            return $this->db->f($value);;
        }
        return NULL;
    }

    function delete() {
        $query = "DELETE FROM " . $GLOBALS['_PJ_gid_table'] . " WHERE id='" . $this->giveValue('id') . "'";
        $this->db->query($query);

        if(!$this->Errno) {
            return true;
        }
        return false;
    }

    function save() {
        // Debug-Logging für Request und Session
        $log_dir = __DIR__ . '/../logs';
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0755, true);
        }
        
        $log_file = $log_dir . '/group_debug.log';
        $timestamp = date('Y-m-d H:i:s');
        $log_data = [
            'POST' => $_POST,
            'GET' => $_GET,
            'REQUEST' => $_REQUEST,
            'this_data' => $this->data,
            'session_status' => session_status(),
            'session_id' => session_id(),
            'cookies' => $_COOKIE
        ];
        $log_entry = "[$timestamp] Group save() method called | " . json_encode($log_data, JSON_UNESCAPED_SLASHES) . "\n";
        file_put_contents($log_file, $log_entry, FILE_APPEND);
        
        // Versuch, Daten aus dem Request direkt zu holen, wenn sie nicht in $this->data sind
        if ((!isset($this->data['name']) || $this->data['name'] == '') && isset($_REQUEST['name']) && $_REQUEST['name'] != '') {
            $this->data['name'] = $_REQUEST['name'];
            file_put_contents($log_file, "[$timestamp] Fixed: Set name from REQUEST: {$this->data['name']}\n", FILE_APPEND);
        }

        if(!isset($this->db) or !is_object($this->db)) {
            $this->db = new Database;
        }

        if(!isset($this->data['name']) || $this->data['name'] == '') {
            return $GLOBALS['_PJ_strings']['error_group_empty'];
        }

        if($this->exists($this->data['name'])) {
            return $GLOBALS['_PJ_strings']['error_group_exists'];
        }
        
        // Variable für Debug-Ausgabe
        $debug_output = "";
        
        // Debug-Informationen sammeln
        $debug_output .= "<pre style=\"background:#eee;padding:10px;margin:10px;border:1px solid #999;\">";
        $debug_output .= "<b>[Group::save] Before query build</b><br>";
        $debug_output .= "<b>REQUEST data:</b> ";
        $debug_output .= htmlspecialchars(print_r($_REQUEST, true));
        $debug_output .= "<b>POST data:</b> ";
        $debug_output .= htmlspecialchars(print_r($_POST, true));
        $debug_output .= "<b>this->data BEFORE fix:</b> ";
        $debug_output .= htmlspecialchars(print_r($this->data, true));
        
        // FIX: Fehlende Formularfelder aus REQUEST übernehmen
        if (isset($_REQUEST['id']) && !isset($this->data['id'])) {
            $this->data['id'] = $_REQUEST['id'];
        }
        
        $debug_output .= "<b>this->data AFTER fix:</b> ";
        $debug_output .= htmlspecialchars(print_r($this->data, true));
                
        $query = sprintf("REPLACE INTO %s (id, name) VALUES(%s, '%s')",
                            $GLOBALS['_PJ_gid_table'],
                            $this->data['id']?"'".$this->data['id']."'":"NULL",
                            $this->data['name']
                            );
        $debug_output .= "<b>SQL Query:</b> " . htmlspecialchars($query) . "</pre>";

        $this->db->query($query);
    
    // CRITICAL FIX: Set the ID after insert for new groups
    if(empty($this->data['id'])) {
        $this->data['id'] = $this->db->insert_id();
        debugLog('GROUP_SAVE', 'New group created with ID: ' . $this->data['id']);
    }
    
    // Debug-Ausgabe nur im Fehlerfall anzeigen
    // Prüfen, ob die Gruppe erfolgreich gespeichert wurde
        $success = false;
        $check_query = "SELECT COUNT(*) AS count FROM " . $GLOBALS['_PJ_gid_table'] . " WHERE name='" . $this->data['name'] . "'";
        $this->db->query($check_query);
        if ($this->db->next_record() && $this->db->Record[0] > 0) {
            $success = true;
        }
        
        if (!$success) {
            // Fehler beim Speichern - Debug-Ausgaben anzeigen
            echo isset($this->debug_exists) ? $this->debug_exists : '';
            echo $debug_output;
        } else {
            // Erfolgs-Nachricht anzeigen
            echo '<div style="background-color: #dff0d8; color: #3c763d; padding: 10px; margin: 10px; border: 1px solid #d6e9c6; border-radius: 4px;">';
            echo 'Group "' . htmlspecialchars($this->data['name']) . '" saved.';
            echo '</div>';
        }
    }

    function reset() {
        reset($this->data);
        // Reset array iteration variables for each() replacement
        $this->data_keys = array_keys($this->data);
        $this->data_pointer = 0;
    }

    function giveNext() {
        // Fixed: replaced deprecated each() function with array iteration
        if ($this->data_pointer >= count($this->data_keys)) {
            return false;
        }
        $key = $this->data_keys[$this->data_pointer];
        $val = $this->data[$key];
        $this->data_pointer++;
        return $val;
    }

    function giveValue($key) {
        if(isset($this->data[$key])) return $this->data[$key];
else return null;
    }
}