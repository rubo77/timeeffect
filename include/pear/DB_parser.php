<?php
/**
 * Modern PEAR DB Parser for PHP 8.4
 * 
 * This file provides DSN parsing capability for legacy PEAR DB code
 */

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
    $conn = @mysqli_connect(
        $parsed['hostspec'],
        $parsed['username'] ?? null,
        $parsed['password'] ?? null,
        $parsed['database'] ?? null,
        $parsed['port'] ?? null
    );
    
    if (!$conn) {
        error_log("DB Connection Error: " . mysqli_connect_error());
        return new PEAR_Error("DB Connection failed: " . mysqli_connect_error(), 1);
    }
    
    return $conn;
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
