# TimeEffect Security Implementation

## Overview

The TimeEffect Security Layer provides comprehensive SQL injection protection for the TimeEffect application. It is designed to work seamlessly with the existing PEAR DB infrastructure while providing modern security practices.

## Requirements

- **PHP 8.4+** (Required)
- **mysqli extension** (Required)
- **Existing PEAR DB infrastructure** (Already in place)

## Architecture

The security system consists of two main components:

### 1. DatabaseSecurity Class (`/include/security.inc.php`)

A static utility class providing secure query building and input sanitization functions.

### 2. SecureDatabase Class (extends existing Database class)

A wrapper class that adds security methods to the existing Database class.

## Core Functions

### String Escaping

```php
// Safe string escaping using mysqli_real_escape_string
$safeValue = DatabaseSecurity::escapeString($userInput, $dbConnection);
```

**Important**: The mysqli connection object is required for proper escaping.

### Integer and Float Sanitization

```php
// Safe integer conversion
$safeId = DatabaseSecurity::escapeInt($_GET['id']);

// Safe float conversion
$safePrice = DatabaseSecurity::escapeFloat($_POST['price']);
```

### Safe Query Building

#### WHERE Clauses

```php
// For integer comparisons
$whereClause = DatabaseSecurity::buildWhereId('user_id', $userId);
// Result: "user_id = 123"

// For string comparisons
$whereClause = DatabaseSecurity::buildWhereString('username', $username, '=', $dbConnection);
// Result: "username = 'escaped_username'"

// For IN clauses with multiple integers
$whereClause = DatabaseSecurity::buildWhereIn('category_id', [1, 2, 3]);
// Result: "category_id IN (1,2,3)"
```

#### Complete Query Building

```php
// UPDATE queries
$updateData = ['name' => $userName, 'email' => $userEmail];
$whereClause = DatabaseSecurity::buildWhereId('id', $userId);
$query = DatabaseSecurity::buildUpdate('users', $updateData, $whereClause, $dbConnection);

// INSERT queries
$insertData = ['name' => $userName, 'email' => $userEmail, 'created' => time()];
$query = DatabaseSecurity::buildInsert('users', $insertData, $dbConnection);

// DELETE queries
$whereClause = DatabaseSecurity::buildWhereId('id', $userId);
$query = DatabaseSecurity::buildDelete('users', $whereClause);
```

### Input Validation

```php
// Validate different input types
$safeEmail = DatabaseSecurity::validateInput($_POST['email'], 'email');
$safeInt = DatabaseSecurity::validateInput($_GET['page'], 'int', 1);
$safeFloat = DatabaseSecurity::validateInput($_POST['price'], 'float', 0.0);
$safeString = DatabaseSecurity::validateInput($_POST['description'], 'string');

// Get parameters safely from superglobals
$userId = DatabaseSecurity::getParam('user_id', 'int', 0, 'get');
$searchTerm = DatabaseSecurity::getParam('search', 'string', '', 'post');
```

## Usage Examples

### Basic Usage with Existing Database Class

```php
<?php
require_once('include/security.inc.php');
require_once('include/database.inc.php');

// Create database connection
$db = new Database();

// Safe user lookup
$userId = DatabaseSecurity::escapeInt($_GET['id']);
$query = "SELECT * FROM users WHERE " . DatabaseSecurity::buildWhereId('id', $userId);
$db->query($query);

// Safe user update
$updateData = [
    'name' => $_POST['name'],
    'email' => $_POST['email']
];
$whereClause = DatabaseSecurity::buildWhereId('id', $userId);
$query = DatabaseSecurity::buildUpdate('users', $updateData, $whereClause, $db->getConnection());
$db->query($query);
?>
```

### Advanced Usage with SecureDatabase Class

```php
<?php
require_once('include/security.inc.php');
require_once('include/database.inc.php');

// Create secure database connection
$db = new SecureDatabase();

// Use built-in escape method
$safeUsername = $db->escape($_POST['username']);

// Execute with security logging
$query = "SELECT * FROM users WHERE username = '{$safeUsername}'";
$db->secureQuery($query);
?>
```

### Form Input Processing

```php
<?php
// Process form data safely
$userData = [
    'name' => DatabaseSecurity::validateInput($_POST['name'], 'string'),
    'email' => DatabaseSecurity::validateInput($_POST['email'], 'email'),
    'age' => DatabaseSecurity::validateInput($_POST['age'], 'int', 0),
    'salary' => DatabaseSecurity::validateInput($_POST['salary'], 'float', 0.0)
];

// Build safe insert query
$db = new Database();
$query = DatabaseSecurity::buildInsert('employees', $userData, $db->getConnection());
$db->query($query);
?>
```

## Security Features

### 1. SQL Injection Prevention

- Uses `mysqli_real_escape_string()` for all string escaping
- Validates and casts integers/floats before use in queries
- Sanitizes column names and operators
- Provides safe query building functions

### 2. Input Validation

- Type-specific validation (email, URL, integer, float, boolean)
- Safe parameter extraction from superglobals
- Default value handling for missing/invalid inputs

### 3. Security Monitoring

- Logs potentially unsafe queries
- Detects direct variable interpolation in SQL
- Compatible with existing error handling

### 4. Column and Operator Sanitization

- Restricts column names to alphanumeric characters and underscores
- Validates SQL operators against whitelist
- Prevents SQL injection through column/operator manipulation

## Migration Guide

### Before (Vulnerable)

```php
// Vulnerable to SQL injection
$query = "SELECT * FROM users WHERE id='$_GET[id]'";
$query = sprintf("UPDATE users SET name='%s' WHERE id=%d", $_POST['name'], $_GET['id']);
```

### After (Secure)

```php
// Secure implementation
$whereClause = DatabaseSecurity::buildWhereId('id', $_GET['id']);
$query = "SELECT * FROM users WHERE {$whereClause}";

$updateData = ['name' => $_POST['name']];
$whereClause = DatabaseSecurity::buildWhereId('id', $_GET['id']);
$query = DatabaseSecurity::buildUpdate('users', $updateData, $whereClause, $db->getConnection());
```

## Performance Considerations

- All security functions are static methods for minimal overhead
- Uses native mysqli functions for optimal performance
- Designed to work with existing PEAR DB infrastructure
- No additional database connections required

## Error Handling

The security layer integrates with existing error handling:

```php
// Throws InvalidArgumentException for invalid mysqli connections
try {
    $escaped = DatabaseSecurity::escapeString($value, $invalidConnection);
} catch (InvalidArgumentException $e) {
    // Handle error appropriately
    error_log("Database connection error: " . $e->getMessage());
}
```

## Testing

The security implementation includes comprehensive validation:

- String escaping prevents SQL injection attempts
- Integer validation blocks non-numeric input
- Column name sanitization removes dangerous characters
- Query building produces safe SQL with proper escaping
- Input validation works correctly for all supported types

## Backward Compatibility

The security layer maintains 100% backward compatibility with:

- Existing PEAR DB infrastructure
- Current Database class implementations
- All existing TimeEffect functionality
- Legacy query patterns (when updated to use security functions)

## Best Practices

1. **Always use security functions**: Never concatenate user input directly into SQL queries
2. **Validate input early**: Use `validateInput()` or `getParam()` at the start of request processing
3. **Use appropriate types**: Specify correct data types for validation functions
4. **Test thoroughly**: Always test security implementations with various input types
5. **Monitor logs**: Review security warnings in error logs regularly
6. **Keep updated**: Ensure PHP and mysqli extension are current

## Files Secured

The security implementation has been integrated into these critical files:

- `include/auth.inc.php` - Authentication queries
- `include/customer.inc.php` - Customer CRUD operations  
- `include/project.inc.php` - Project management queries
- `include/effort.inc.php` - Time tracking queries
- `include/user.inc.php` - User management operations
- `include/rates.inc.php` - Rate calculations
- `include/statistics.inc.php` - Statistics queries

Each file now uses the security functions instead of direct variable interpolation in SQL queries.