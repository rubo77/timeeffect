<?php
/**
 * TimeEffect Security Layer - PHP 8.4+
 * 
 * This class provides comprehensive SQL injection protection for the TimeEffect application.
 * It works with the existing PEAR DB infrastructure and mysqli connections.
 * 
 * Requirements:
 * - PHP 8.4+
 * - mysqli extension
 * 
 * @author TimeEffect Security Layer
 * @version 2.0
 * @since PHP 8.4
 */

class DatabaseSecurity {
    
    /**
     * Safely escape a string for MySQL queries using mysqli
     * Requires PHP 8.4+ and mysqli extension
     * 
     * @param string $value The value to escape
     * @param mysqli $link Database connection (required)
     * @return string The escaped value
     */
    public static function escapeString($value, $link) {
        if (!$link instanceof mysqli) {
            throw new InvalidArgumentException('Database connection must be a mysqli object');
        }
        
        return mysqli_real_escape_string($link, (string) $value);
    }
    
    /**
     * Safely escape an integer value
     * 
     * @param mixed $value The value to convert to integer
     * @return int The safe integer value
     */
    public static function escapeInt($value) {
        return (int) $value;
    }
    
    /**
     * Safely escape a float value
     * 
     * @param mixed $value The value to convert to float
     * @return float The safe float value
     */
    public static function escapeFloat($value) {
        return (float) $value;
    }
    
    /**
     * Build a safe WHERE clause for ID comparison
     * 
     * @param string $column The column name
     * @param mixed $value The value to compare
     * @param string $operator The comparison operator (=, !=, >, <, etc.)
     * @return string Safe WHERE clause
     */
    public static function buildWhereId($column, $value, $operator = '=') {
        $safeColumn = self::sanitizeColumnName($column);
        $safeValue = self::escapeInt($value);
        $safeOperator = self::sanitizeOperator($operator);
        
        return "{$safeColumn} {$safeOperator} {$safeValue}";
    }
    
    /**
     * Build a safe WHERE clause for string comparison
     * 
     * @param string $column The column name
     * @param string $value The value to compare
     * @param mysqli $link Database connection (required)
     * @param string $operator The comparison operator (=, !=, LIKE, etc.)
     * @return string Safe WHERE clause
     */
    public static function buildWhereString($column, $value, $link, $operator = '=') {
        $safeColumn = self::sanitizeColumnName($column);
        $safeValue = self::escapeString($value, $link);
        $safeOperator = self::sanitizeOperator($operator);
        
        return "{$safeColumn} {$safeOperator} '{$safeValue}'";
    }
    
    /**
     * Build a safe IN clause for integers
     * 
     * @param string $column The column name
     * @param array $values Array of integer values
     * @return string Safe IN clause
     */
    public static function buildWhereIn($column, $values) {
        if (empty($values) || !is_array($values)) {
            return "1=0"; // Returns no results for empty array
        }
        
        $safeColumn = self::sanitizeColumnName($column);
        $safeValues = array_map([self::class, 'escapeInt'], $values);
        
        return "{$safeColumn} IN (" . implode(',', $safeValues) . ")";
    }
    
    /**
     * Sanitize column names to prevent SQL injection in column references
     * 
     * @param string $column The column name
     * @return string Safe column name
     */
    public static function sanitizeColumnName($column) {
        // Allow only alphanumeric characters, underscores, and dots (for table.column)
        // Remove semicolons and other dangerous characters
        $column = preg_replace('/[^a-zA-Z0-9_.]/', '', $column);
        
        // Additional safety: limit length and ensure it starts with letter or underscore
        $column = substr($column, 0, 64);
        if (!preg_match('/^[a-zA-Z_]/', $column)) {
            return 'id'; // Default safe column name
        }
        
        return $column;
    }
    
    /**
     * Sanitize SQL operators
     * 
     * @param string $operator The operator
     * @return string Safe operator
     */
    public static function sanitizeOperator($operator) {
        $allowedOperators = ['=', '!=', '<>', '>', '<', '>=', '<=', 'LIKE', 'NOT LIKE', 'IN', 'NOT IN'];
        $operator = strtoupper(trim($operator));
        
        if (in_array($operator, $allowedOperators)) {
            return $operator;
        }
        
        return '='; // Default to equals if operator is not recognized
    }
    
    /**
     * Build a safe UPDATE query
     * 
     * @param string $table The table name
     * @param array $data Associative array of column => value pairs
     * @param string $whereClause Safe WHERE clause (must be pre-built using other methods)
     * @param mysqli $link Database connection (required)
     * @return string Safe UPDATE query
     */
    public static function buildUpdate($table, $data, $whereClause, $link) {
        $safeTable = self::sanitizeColumnName($table);
        $setParts = [];
        
        foreach ($data as $column => $value) {
            $safeColumn = self::sanitizeColumnName($column);
            
            if (is_int($value) || is_float($value)) {
                $setParts[] = "{$safeColumn} = " . ($value + 0); // Ensure it's numeric
            } else {
                $safeValue = self::escapeString($value, $link);
                $setParts[] = "{$safeColumn} = '{$safeValue}'";
            }
        }
        
        return "UPDATE {$safeTable} SET " . implode(', ', $setParts) . " WHERE {$whereClause}";
    }
    
    /**
     * Build a safe INSERT query
     * 
     * @param string $table The table name
     * @param array $data Associative array of column => value pairs
     * @param mysqli $link Database connection (required)
     * @return string Safe INSERT query
     */
    public static function buildInsert($table, $data, $link) {
        $safeTable = self::sanitizeColumnName($table);
        $columns = [];
        $values = [];
        
        foreach ($data as $column => $value) {
            $safeColumn = self::sanitizeColumnName($column);
            $columns[] = $safeColumn;
            
            if (is_int($value) || is_float($value)) {
                $values[] = ($value + 0); // Ensure it's numeric
            } else {
                $safeValue = self::escapeString($value, $link);
                $values[] = "'{$safeValue}'";
            }
        }
        
        return "INSERT INTO {$safeTable} (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $values) . ")";
    }
    
    /**
     * Build a safe DELETE query
     * 
     * @param string $table The table name
     * @param string $whereClause Safe WHERE clause (must be pre-built using other methods)
     * @return string Safe DELETE query
     */
    public static function buildDelete($table, $whereClause) {
        $safeTable = self::sanitizeColumnName($table);
        return "DELETE FROM {$safeTable} WHERE {$whereClause}";
    }
    
    /**
     * Validate and sanitize user input based on expected type
     * 
     * @param mixed $input The input to validate
     * @param string $type Expected type (int, float, string, email, etc.)
     * @param mixed $default Default value if validation fails
     * @return mixed Sanitized value
     */
    public static function validateInput($input, $type = 'string', $default = null) {
        switch ($type) {
            case 'int':
            case 'integer':
                return filter_var($input, FILTER_VALIDATE_INT) !== false ? (int)$input : $default;
                
            case 'float':
            case 'double':
                return filter_var($input, FILTER_VALIDATE_FLOAT) !== false ? (float)$input : $default;
                
            case 'email':
                return filter_var($input, FILTER_VALIDATE_EMAIL) !== false ? $input : $default;
                
            case 'url':
                return filter_var($input, FILTER_VALIDATE_URL) !== false ? $input : $default;
                
            case 'boolean':
            case 'bool':
                return filter_var($input, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) !== null;
                
            case 'string':
            default:
                // Basic string sanitization - remove null bytes and control characters
                return preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', (string)$input);
        }
    }
    
    /**
     * Safely get a parameter from $_GET, $_POST, or $_REQUEST with validation
     * 
     * @param string $key The parameter key
     * @param string $type Expected type
     * @param mixed $default Default value
     * @param string $source Source superglobal ('get', 'post', 'request')
     * @return mixed Sanitized parameter value
     */
    public static function getParam($key, $type = 'string', $default = null, $source = 'request') {
        $value = null;
        
        switch (strtolower($source)) {
            case 'get':
                $value = $_GET[$key] ?? $default;
                break;
            case 'post':
                $value = $_POST[$key] ?? $default;
                break;
            case 'request':
            default:
                $value = $_REQUEST[$key] ?? $default;
                break;
        }
        
        return self::validateInput($value, $type, $default);
    }
}

/**
 * Compatibility wrapper for legacy database class
 * This extends the existing Database class to add security methods for PHP 8.4+
 */
if (class_exists('Database')) {
    class SecureDatabase extends Database {
        
        /**
         * Execute a query with automatic SQL injection protection logging
         * 
         * @param string $query The SQL query
         * @return mixed Query result
         */
        public function secureQuery($query) {
            // Log potentially unsafe queries for monitoring
            if (preg_match('/WHERE.*\$|INSERT.*\$|UPDATE.*\$|DELETE.*\$/', $query)) {
                debugLog("SECURITY WARNING", "Potentially unsafe query detected: " . $query);
            }
            
            return $this->query($query);
        }
        
        /**
         * Get the mysqli database connection
         * 
         * @return mysqli Database connection
         */
        public function getConnection() {
            return $this->Link_ID;
        }
        
        /**
         * Safely escape a string using the current database connection
         * 
         * @param string $value The value to escape
         * @return string Escaped value
         */
        public function escape($value) {
            return DatabaseSecurity::escapeString($value, $this->getConnection());
        }
    }
}

?>