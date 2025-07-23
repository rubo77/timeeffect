<?php
/**
 * Modern PEAR DB Parser for PHP 8.4
 * 
 * This file provides DSN parsing capability for legacy PEAR DB code
 */

// Define PEAR DB constants for compatibility
if (!defined('DB_FETCHMODE_ASSOC')) {
    define('DB_FETCHMODE_ASSOC', 2);
}

/**
 * Parse a DSN string and return its components
 * 
 * @param string $dsn The DSN string to parse (mysql://user:pass@host/db)
 * @return array Associative array with connection parameters
 */
function parseDSN($dsn) {
    $result = [];
    
    // Log what we're parsing
    error_log("Parsing DSN: " . preg_replace('/:[^@]*@/', ':***@', $dsn));
    
    // Extract protocol (mysql, pgsql etc.)
    if (preg_match('/^([^:]+):\/\//', $dsn, $matches)) {
        $result['phptype'] = $matches[1];
        $dsn = substr($dsn, strlen($matches[0]));
    }
    
    // Extract username, password if present
    if (preg_match('/^([^:@]+)(:([^@]+))?@/', $dsn, $matches)) {
        $result['username'] = $matches[1];
        if (isset($matches[3])) {
            $result['password'] = $matches[3];
        }
        $dsn = substr($dsn, strlen($matches[0]));
    }
    
    // Extract host and optional port
    if (preg_match('/^([^\/]+)/', $dsn, $matches)) {
        $hostport = $matches[1];
        $dsn = substr($dsn, strlen($matches[0]));
        
        if (strpos($hostport, ':') !== false) {
            list($host, $port) = explode(':', $hostport, 2);
            $result['hostspec'] = $host;
            $result['port'] = $port;
        } else {
            $result['hostspec'] = $hostport;
        }
    }
    
    // Extract database name
    if (preg_match('/\/(.*)/', $dsn, $matches)) {
        $result['database'] = $matches[1];
    }
    
    error_log("Parsed DSN components: " . json_encode(array_diff_key($result, ['password' => ''])));
    return $result;
}

/**
 * Override PEAR DB::Connect to handle DSNs correctly in PHP 8.4
 * 
 * @param string $dsn DSN connection string
 * @return object DB connection object
 */
function DB_Connect($dsn) {
    $parsed = parseDSN($dsn);
    
    if (empty($parsed['hostspec']) || $parsed['hostspec'] === 'mysql') {
        // Override the host if it's set incorrectly (common problem)
        $parsed['hostspec'] = 'db';
        error_log("WARNING: Host was empty or 'mysql' in DSN, overriding to 'db'");
    }
    
    // Create connection using mysqli
    // Ensure port is integer or null, never a string
    $port = null;
    if (isset($parsed['port']) && is_numeric($parsed['port'])) {
        $port = (int)$parsed['port'];
    }
    
    // Create a mysqli connection
    $mysqli = @mysqli_connect(
        $parsed['hostspec'],
        $parsed['username'] ?? null,
        $parsed['password'] ?? null,
        $parsed['database'] ?? null,
        $port
    );
    
    if (!$mysqli) {
        error_log("DB Connection Error: " . mysqli_connect_error());
        return new PEAR_Error("DB Connection failed: " . mysqli_connect_error(), 1);
    }
    
    // Create a wrapper that adds PEAR DB compatibility methods to mysqli
    return new MySQLiPEARCompat($mysqli);
}

/**
 * Wrapper class for mysqli that implements PEAR DB compatibility methods
 * 
 * This class wraps a mysqli connection object and adds compatibility methods
 * that were available in the original PEAR DB classes but not in mysqli
 */
class MySQLiPEARCompat {
    /**
     * The wrapped mysqli connection object
     * @var mysqli
     */
    private $mysqli;
    
    /**
     * Constructor
     * 
     * @param mysqli $mysqli The mysqli connection to wrap
     */
    public function __construct($mysqli) {
        $this->mysqli = $mysqli;
    }
    
    /**
     * Quote a string for use in a query
     * 
     * @param string $text Text to quote
     * @return string The quoted string
     */
    public function quoteString($text) {
        return $this->mysqli->real_escape_string($text);
    }
    
    /**
     * Get a row from a result set
     * 
     * @param string $query SQL query
     * @param array $params Not used in this implementation
     * @param int $fetchmode Fetch mode (not used)
     * @return array|null Result row as associative array
     */
    public function getRow($query, $params = null, $fetchmode = null) {
        $result = $this->mysqli->query($query);
        if (!$result) {
            return null;
        }
        $row = $result->fetch_assoc();
        $result->free();
        return $row;
    }
    
    /**
     * Execute a query and return a wrapped result
     * 
     * @param string $query SQL query
     * @return MySQLiResultCompat|null Wrapped mysqli_result or null on error
     */
    public function query($query) {
        $result = $this->mysqli->query($query);
        if (!$result) {
            return null;
        }
        return new MySQLiResultCompat($result);
    }
    
    /**
     * Forward any method call to the underlying mysqli object
     * 
     * @param string $method Method name
     * @param array $args Method arguments
     * @return mixed Result of the method call
     */
    public function __call($method, $args) {
        return call_user_func_array([$this->mysqli, $method], $args);
    }
    
    /**
     * Forward any property access to the underlying mysqli object
     * 
     * @param string $name Property name
     * @return mixed Property value
     */
    public function __get($name) {
        return $this->mysqli->$name;
    }
}

/**
 * Wrapper class for mysqli_result that implements PEAR DB compatibility methods
 * 
 * This class wraps a mysqli_result object and adds compatibility methods
 * that were available in the original PEAR DB classes but not in mysqli_result
 */
class MySQLiResultCompat {
    /**
     * The wrapped mysqli_result object
     * @var mysqli_result
     */
    private $result;
    
    /**
     * Constructor
     * 
     * @param mysqli_result $result The mysqli_result to wrap
     */
    public function __construct($result) {
        $this->result = $result;
    }
    
    /**
     * Fetch a row from the result set
     * 
     * @param int $fetchmode Fetch mode (DB_FETCHMODE_ASSOC = associative array)
     * @return array|null Result row or null if no more rows
     */
    public function fetchRow($fetchmode = null) {
        if ($fetchmode == DB_FETCHMODE_ASSOC) {
            return $this->result->fetch_assoc();
        } else {
            return $this->result->fetch_array();
        }
    }
    
    /**
     * Free the result set
     */
    public function free() {
        return $this->result->free();
    }
    
    /**
     * Forward any method call to the underlying mysqli_result object
     * 
     * @param string $method Method name
     * @param array $args Method arguments
     * @return mixed Result of the method call
     */
    public function __call($method, $args) {
        return call_user_func_array([$this->result, $method], $args);
    }
}

// Direct override of DB class's Connect method
// We'll manually patch the DB.php file to use our connection function
function DB_Override_Install() {
    // Path to original DB.php
    $db_file = __DIR__ . '/DB.php';
    
    if (file_exists($db_file)) {
        // Check if it's already patched
        $content = file_get_contents($db_file);
        if (strpos($content, 'DB_Connect($dsn)') !== false) {
            // Already patched
            return true;
        }
        
        // Look for the connect function
        if (preg_match('/public static function connect\(\$dsn, \$options = array\(\)\)/i', $content)) {
            // Insert our function call
            $patched = preg_replace(
                '/(public static function connect\(\$dsn, \$options = array\(\)\)[\s\n]*{)/i',
                '$1' . "\n        // Use our DB_Connect function for better DSN parsing\n        \$result = DB_Connect(\$dsn);\n        if (!empty(\$result) && !DB::isError(\$result)) {\n            return \$result;\n        }\n",
                $content
            );
            
            if ($patched !== $content) {
                file_put_contents($db_file, $patched);
                error_log("DB.php patched successfully with DB_Connect override");
            }
        }
    }
}

// Apply the patch
DB_Override_Install();
?>
