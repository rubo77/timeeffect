# TimeEffect Security Quick Reference

## Essential Functions (PHP 8.4+)

### Basic Escaping
```php
// String escaping (requires mysqli connection)
$safe = DatabaseSecurity::escapeString($input, $dbConnection);

// Integer/Float conversion
$safeId = DatabaseSecurity::escapeInt($input);
$safePrice = DatabaseSecurity::escapeFloat($input);
```

### Query Building
```php
// WHERE clauses
$where = DatabaseSecurity::buildWhereId('id', $userId);                    // id = 123
$where = DatabaseSecurity::buildWhereString('name', $name, $db, '=');      // name = 'escaped'
$where = DatabaseSecurity::buildWhereIn('category_id', [1,2,3]);          // category_id IN (1,2,3)

// Complete queries
$query = DatabaseSecurity::buildUpdate('table', $data, $where, $db);      // UPDATE table SET...
$query = DatabaseSecurity::buildInsert('table', $data, $db);              // INSERT INTO table...
$query = DatabaseSecurity::buildDelete('table', $where);                  // DELETE FROM table...
```

### Input Validation
```php
// Type validation
$email = DatabaseSecurity::validateInput($_POST['email'], 'email');
$int = DatabaseSecurity::validateInput($_GET['id'], 'int', 0);
$float = DatabaseSecurity::validateInput($_POST['price'], 'float', 0.0);

// Parameter extraction
$userId = DatabaseSecurity::getParam('user_id', 'int', 0, 'get');
$search = DatabaseSecurity::getParam('search', 'string', '', 'post');
```

## Common Patterns

### User Authentication
```php
$username = DatabaseSecurity::validateInput($_POST['username'], 'string');
$where = DatabaseSecurity::buildWhereString('username', $username, $db->getConnection());
$query = "SELECT * FROM users WHERE {$where}";
```

### Data Updates
```php
$data = [
    'name' => DatabaseSecurity::validateInput($_POST['name'], 'string'),
    'email' => DatabaseSecurity::validateInput($_POST['email'], 'email')
];
$where = DatabaseSecurity::buildWhereId('id', $_GET['id']);
$query = DatabaseSecurity::buildUpdate('users', $data, $where, $db->getConnection());
```

### Search Queries
```php
$searchTerm = DatabaseSecurity::getParam('search', 'string');
$where = DatabaseSecurity::buildWhereString('description', "%{$searchTerm}%", $db->getConnection(), 'LIKE');
$query = "SELECT * FROM projects WHERE {$where}";
```

## Migration Examples

### Before (Vulnerable)
```php
$query = "SELECT * FROM users WHERE id='$_GET[id]'";
$query = "UPDATE users SET name='$_POST[name]' WHERE id=$_GET[id]";
$query = sprintf("INSERT INTO logs (message) VALUES ('%s')", $_POST['message']);
```

### After (Secure)
```php
$where = DatabaseSecurity::buildWhereId('id', $_GET['id']);
$query = "SELECT * FROM users WHERE {$where}";

$data = ['name' => $_POST['name']];
$where = DatabaseSecurity::buildWhereId('id', $_GET['id']);
$query = DatabaseSecurity::buildUpdate('users', $data, $where, $db->getConnection());

$data = ['message' => $_POST['message']];
$query = DatabaseSecurity::buildInsert('logs', $data, $db->getConnection());
```

## Requirements
- PHP 8.4+
- mysqli extension
- Valid mysqli connection object for string escaping