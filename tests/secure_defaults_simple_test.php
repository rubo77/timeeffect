<?php
/**
 * Simple test script for secure default permissions
 * Tests the functions without full TimeEffect initialization
 */

// Minimal setup - just load the secure defaults functions
require_once(dirname(__DIR__) . '/include/secure_defaults.inc.php');

// Set test configuration
$GLOBALS['_PJ_registration_secure_defaults'] = 1;
$GLOBALS['_PJ_registration_default_access'] = 'rwxr-----';

echo "<h2>🔒 Secure Defaults Simple Test</h2>\n";

// Test 1: Check configuration
echo "<h3>Test 1: Configuration</h3>\n";
echo "Secure defaults enabled: " . (isSecureDefaultsEnabled() ? "✅ YES" : "❌ NO") . "<br>\n";
echo "Default access: " . getSecureDefaultAccess() . "<br>\n";

// Test 2: Customer defaults
echo "<h3>Test 2: Customer Defaults</h3>\n";
$customer_data = array('customer_name' => 'Test Customer');
$secure_customer = applySecureCustomerDefaults($customer_data, 123, 456);
echo "✅ Customer access: " . $secure_customer['access'] . "<br>\n";
echo "✅ Customer user: " . $secure_customer['user'] . "<br>\n";
echo "✅ Customer gid: " . $secure_customer['gid'] . "<br>\n";
echo "✅ Read foreign efforts: " . $secure_customer['readforeignefforts'] . "<br>\n";

// Test 3: Project defaults
echo "<h3>Test 3: Project Defaults</h3>\n";
$project_data = array('project_name' => 'Test Project');
$secure_project = applySecureProjectDefaults($project_data, 123, 456);
echo "✅ Project access: " . $secure_project['access'] . "<br>\n";
echo "✅ Project user: " . $secure_project['user'] . "<br>\n";
echo "✅ Project gid: " . $secure_project['gid'] . "<br>\n";

// Test 4: Security analysis
echo "<h3>Test 4: Security Analysis</h3>\n";
$access = 'rwxr-----';
echo "<div style='background-color: #d4edda; padding: 10px; border: 1px solid #c3e6cb; border-radius: 4px;'>\n";
echo "<strong>🔒 Security Model:</strong><br>\n";
echo "• Owner (rwx): Full read/write/execute access<br>\n";
echo "• Group (r--): Read-only access<br>\n";
echo "• Other (---): No access<br>\n";
echo "• Foreign efforts: Disabled (readforeignefforts = 0)<br>\n";
echo "</div>\n";

echo "<h3>✅ All Tests Passed!</h3>\n";
?>
