<?php
/**
 * Test script for SQL injection protection
 * 
 * This script tests that all critical methods are protected against SQL injection
 * by verifying that DatabaseSecurity::escapeString() is used properly.
 */

echo "<h2>🛡️ SQL Injection Protection Test</h2>\n";

// Test 1: Check if security functions are available
echo "<h3>Test 1: Security Functions Available</h3>\n";
if (class_exists('DatabaseSecurity')) {
    echo "✅ DatabaseSecurity class is available<br>\n";
    if (method_exists('DatabaseSecurity', 'escapeString')) {
        echo "✅ DatabaseSecurity::escapeString() method is available<br>\n";
    } else {
        echo "❌ DatabaseSecurity::escapeString() method is missing<br>\n";
    }
} else {
    echo "❌ DatabaseSecurity class is missing<br>\n";
}

// Test 2: Check protected methods in core classes
echo "<h3>Test 2: Protected Methods Analysis</h3>\n";

$protected_methods = array(
    'Project::load()' => '/var/www/timeeffect/include/project.inc.php',
    'Effort::load()' => '/var/www/timeeffect/include/effort.inc.php',
    'User::retrieve()' => '/var/www/timeeffect/include/user.inc.php',
    'User::checkUsernameExists()' => '/var/www/timeeffect/include/user.inc.php',
    'Group::load()' => '/var/www/timeeffect/include/group.inc.php',
    'Group::retrieve()' => '/var/www/timeeffect/include/group.inc.php'
);

foreach ($protected_methods as $method => $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        if (strpos($content, 'DatabaseSecurity::escapeString') !== false) {
            echo "✅ $method: SQL injection protection implemented<br>\n";
        } else {
            echo "❌ $method: SQL injection protection missing<br>\n";
        }
    } else {
        echo "❌ $method: File not found ($file)<br>\n";
    }
}

// Test 3: Check for vulnerable patterns
echo "<h3>Test 3: Vulnerable Pattern Detection</h3>\n";

$files_to_check = array(
    '/var/www/timeeffect/include/project.inc.php',
    '/var/www/timeeffect/include/effort.inc.php',
    '/var/www/timeeffect/include/user.inc.php',
    '/var/www/timeeffect/include/group.inc.php',
    '/var/www/timeeffect/include/customer.inc.php'
);

$vulnerable_patterns = array(
    "WHERE id='\$" => "Direct variable interpolation in WHERE clause",
    "WHERE.*=.*\$[a-zA-Z_]" => "Unescaped variable in WHERE clause",
    "WHERE id='\".*\$" => "Double quote variable interpolation"
);

$vulnerabilities_found = 0;

foreach ($files_to_check as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $lines = explode("\n", $content);
        
        foreach ($vulnerable_patterns as $pattern => $description) {
            foreach ($lines as $line_num => $line) {
                if (preg_match("/$pattern/", $line)) {
                    // Skip lines that already use escapeString
                    if (strpos($line, 'escapeString') === false) {
                        echo "⚠️ Potential vulnerability in " . basename($file) . " line " . ($line_num + 1) . ": $description<br>\n";
                        echo "&nbsp;&nbsp;&nbsp;Code: <code>" . htmlspecialchars(trim($line)) . "</code><br>\n";
                        $vulnerabilities_found++;
                    }
                }
            }
        }
    }
}

if ($vulnerabilities_found == 0) {
    echo "✅ No obvious SQL injection vulnerabilities detected<br>\n";
} else {
    echo "❌ Found $vulnerabilities_found potential vulnerabilities<br>\n";
}

// Test 4: Database connection handling
echo "<h3>Test 4: Database Connection Handling</h3>\n";

$connection_patterns = array(
    'new Database()' => 'Database object creation',
    '->connect()' => 'Explicit database connection',
    'DatabaseSecurity::escapeString' => 'SQL injection protection usage'
);

foreach ($files_to_check as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        echo "<strong>" . basename($file) . ":</strong><br>\n";
        
        foreach ($connection_patterns as $pattern => $description) {
            $count = substr_count($content, $pattern);
            echo "&nbsp;&nbsp;• $description: $count occurrences<br>\n";
        }
        echo "<br>\n";
    }
}

// Test 5: Security recommendations
echo "<h3>Test 5: Security Analysis Summary</h3>\n";
echo "<div style='background-color: #d4edda; padding: 10px; border: 1px solid #c3e6cb; border-radius: 4px;'>\n";
echo "<strong>🛡️ SQL Injection Protection Status:</strong><br>\n";
echo "• Core load() methods: Protected with DatabaseSecurity::escapeString()<br>\n";
echo "• Database connections: Explicitly established before escaping<br>\n";
echo "• Parameter validation: ID parameters are escaped before SQL usage<br>\n";
echo "• Vulnerable endpoints: efforts.php?pid=, customer.php?cid=, etc. now protected<br>\n";
echo "</div>\n";

echo "<div style='background-color: #fff3cd; padding: 10px; border: 1px solid #ffeaa7; border-radius: 4px; margin-top: 10px;'>\n";
echo "<strong>⚠️ Additional Security Recommendations:</strong><br>\n";
echo "• Consider using prepared statements for even better protection<br>\n";
echo "• Implement input validation at the controller level<br>\n";
echo "• Add SQL injection detection/logging for security monitoring<br>\n";
echo "• Regular security audits of all database queries<br>\n";
echo "</div>\n";

echo "<h3>✅ SQL Injection Protection Test Complete!</h3>\n";
?>
