<?php
/**
 * PEAR DB Compatibility Layer for PHP 8.4
 * 
 * This file provides backward compatibility for legacy PEAR DB code
 * by mapping old PEAR DB calls to modern Doctrine DBAL
 */

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

// Global connection instance
$GLOBALS['_MODERN_DB_CONNECTION'] = null;

/**
 * Modern DB class that mimics PEAR DB interface
 */
class ModernDB {
    private static $connection = null;
    
    /**
     * Connect to database using modern Doctrine DBAL
     */
    public static function connect($dsn, $options = array()) {
        if (self::$connection !== null) {
            return self::$connection;
        }
        
        try {
            // Parse old PEAR DSN format
            $dsnInfo = self::parseDSN($dsn);
            
            // Convert to Doctrine DBAL connection params
            $connectionParams = [
                'dbname' => $dsnInfo['database'],
                'user' => $dsnInfo['username'],
                'password' => $dsnInfo['password'],
                'host' => $dsnInfo['hostspec'],
                'driver' => self::mapDriver($dsnInfo['phptype']),
            ];
            
            if (isset($dsnInfo['port'])) {
                $connectionParams['port'] = $dsnInfo['port'];
            }
            
            self::$connection = DriverManager::getConnection($connectionParams);
            $GLOBALS['_MODERN_DB_CONNECTION'] = self::$connection;
            
            return new ModernDBWrapper(self::$connection);
            
        } catch (Exception $e) {
            error_log("ModernDB Connection Error: " . $e->getMessage());
            return new PEAR_Error("Connection failed: " . $e->getMessage());
        }
    }
    
    /**
     * Parse PEAR DSN format
     */
    private static function parseDSN($dsn) {
        if (is_array($dsn)) {
            return $dsn;
        }
        
        // Parse DSN string format: phptype://username:password@hostspec/database
        $parsed = parse_url($dsn);
        
        return [
            'phptype' => $parsed['scheme'] ?? 'mysql',
            'username' => $parsed['user'] ?? '',
            'password' => $parsed['pass'] ?? '',
            'hostspec' => $parsed['host'] ?? 'localhost',
            'port' => $parsed['port'] ?? null,
            'database' => ltrim($parsed['path'] ?? '', '/'),
        ];
    }
    
    /**
     * Map old PEAR driver names to Doctrine DBAL drivers
     */
    private static function mapDriver($pearDriver) {
        $mapping = [
            'mysql' => 'pdo_mysql',
            'mysqli' => 'pdo_mysql',
            'pgsql' => 'pdo_pgsql',
            'sqlite' => 'pdo_sqlite',
            'oci8' => 'pdo_oci',
            'mssql' => 'pdo_sqlsrv',
        ];
        
        return $mapping[$pearDriver] ?? 'pdo_mysql';
    }
    
    /**
     * Check if value is an error
     */
    public static function isError($value) {
        return $value instanceof PEAR_Error || $value instanceof Exception;
    }
    
    /**
     * Check if value is a connection
     */
    public static function isConnection($value) {
        return $value instanceof ModernDBWrapper || $value instanceof Connection;
    }
}

/**
 * Wrapper class that provides PEAR DB interface using Doctrine DBAL
 */
class ModernDBWrapper {
    private $connection;
    
    public function __construct(Connection $connection) {
        $this->connection = $connection;
    }
    
    /**
     * Execute query
     */
    public function query($sql) {
        try {
            $result = $this->connection->executeQuery($sql);
            return new ModernDBResult($result);
        } catch (Exception $e) {
            error_log("ModernDB Query Error: " . $e->getMessage() . " SQL: " . $sql);
            return new PEAR_Error("Query failed: " . $e->getMessage());
        }
    }
    
    /**
     * Get one row
     */
    public function getRow($sql, $fetchmode = null) {
        try {
            $result = $this->connection->executeQuery($sql);
            return $result->fetchAssociative() ?: null;
        } catch (Exception $e) {
            error_log("ModernDB GetRow Error: " . $e->getMessage() . " SQL: " . $sql);
            return new PEAR_Error("GetRow failed: " . $e->getMessage());
        }
    }
    
    /**
     * Get all rows
     */
    public function getAll($sql, $fetchmode = null) {
        try {
            $result = $this->connection->executeQuery($sql);
            return $result->fetchAllAssociative();
        } catch (Exception $e) {
            error_log("ModernDB GetAll Error: " . $e->getMessage() . " SQL: " . $sql);
            return new PEAR_Error("GetAll failed: " . $e->getMessage());
        }
    }
    
    /**
     * Get one value
     */
    public function getOne($sql) {
        try {
            $result = $this->connection->executeQuery($sql);
            return $result->fetchOne();
        } catch (Exception $e) {
            error_log("ModernDB GetOne Error: " . $e->getMessage() . " SQL: " . $sql);
            return new PEAR_Error("GetOne failed: " . $e->getMessage());
        }
    }
    
    /**
     * Quote string
     */
    public function quoteSmart($value) {
        if (is_null($value)) {
            return 'NULL';
        }
        if (is_numeric($value)) {
            return $value;
        }
        return $this->connection->quote($value);
    }
    
    /**
     * Disconnect
     */
    public function disconnect() {
        $this->connection->close();
        return true;
    }
}

/**
 * Result wrapper
 */
class ModernDBResult {
    private $result;
    
    public function __construct($result) {
        $this->result = $result;
    }
    
    public function fetchRow($fetchmode = null) {
        try {
            return $this->result->fetchAssociative();
        } catch (Exception $e) {
            return null;
        }
    }
    
    public function numRows() {
        return $this->result->rowCount();
    }
    
    public function free() {
        $this->result = null;
        return true;
    }
}

// Create compatibility aliases for old PEAR classes
if (!class_exists('DB')) {
    class DB extends ModernDB {}
}

// Simple PEAR_Error class for compatibility
if (!class_exists('PEAR_Error')) {
    class PEAR_Error {
        private $message;
        private $code;
        
        public function __construct($message = '', $code = null) {
            $this->message = $message;
            $this->code = $code;
        }
        
        public function getMessage() {
            return $this->message;
        }
        
        public function getCode() {
            return $this->code;
        }
        
        public function __toString() {
            return $this->message;
        }
    }
}

// Log compatibility layer activation
error_log("PEAR DB Compatibility Layer activated - using Doctrine DBAL backend");
