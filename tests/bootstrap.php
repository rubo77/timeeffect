<?php

// Bootstrap file for PHPUnit tests
// Sets up required global variables and includes

// Set the root path (adjust for host vs container)
$_PJ_root = __DIR__ . '/..';

// Include the configuration file to set up database globals
require_once $_PJ_root . '/include/config.inc.php';

// Mock database connection for testing
class MockDatabase {
    public $Record = [];
    
    public function query($sql) {
        return true;
    }
    
    public function next_record() {
        return false;
    }
    
    public function f($field) {
        return isset($this->Record[$field]) ? $this->Record[$field] : null;
    }
    
    public function insert_id() {
        return 1;
    }
}

// Override Database class for testing
if (!class_exists('Database')) {
    class Database extends MockDatabase {}
}

// Set up global table names if not already set
if (!isset($GLOBALS['_PJ_customer_table'])) {
    $GLOBALS['_PJ_customer_table'] = 'customer';
}
if (!isset($GLOBALS['_PJ_project_table'])) {
    $GLOBALS['_PJ_project_table'] = 'project';
}
if (!isset($GLOBALS['_PJ_effort_table'])) {
    $GLOBALS['_PJ_effort_table'] = 'effort';
}

// Suppress error output during testing to focus on our specific tests
error_reporting(E_ERROR | E_PARSE);
