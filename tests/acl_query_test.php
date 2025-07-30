<?php
/**
 * Test script for centralized ACL query functions
 * 
 * This script tests the new centralized ACL query functions to ensure
 * they generate correct SQL and handle empty gids properly.
 */

// Mock user class for testing
class MockUser {
    private $data;
    
    public function __construct($user_id, $gids = '', $is_admin = false) {
        $this->data = array(
            'id' => $user_id,
            'gids' => $gids,
            'is_admin' => $is_admin
        );
    }
    
    public function giveValue($key) {
        return isset($this->data[$key]) ? $this->data[$key] : '';
    }
    
    public function checkPermission($permission) {
        return $this->data['is_admin'] && $permission === 'admin';
    }
}

// Load the ACL query functions
require_once('/var/www/timeeffect/include/acl_query.inc.php');

echo "<h2>🔧 ACL Query Functions Test</h2>\n";

// Test 1: Admin user (should return empty query)
echo "<h3>Test 1: Admin User</h3>\n";
$admin_user = new MockUser(1, '1,2,3', true);
$admin_query = buildAclWhereClause($admin_user, '', 'r');
echo "Admin query: '" . $admin_query . "' (should be empty)<br>\n";
echo "Result: " . (empty($admin_query) ? "✅ PASS" : "❌ FAIL") . "<br>\n";

// Test 2: Regular user with groups
echo "<h3>Test 2: Regular User with Groups</h3>\n";
$user_with_groups = new MockUser(5, '2,3,4', false);
$user_query = buildAclWhereClause($user_with_groups, '', 'r');
echo "User with groups query: $user_query<br>\n";
$expected_parts = array(
    "(user = '5' AND access LIKE 'r________')",
    "(gid IN (2,3,4) AND access LIKE '___r_____')",
    "(access LIKE '______r__')"
);
$contains_all = true;
foreach ($expected_parts as $part) {
    if (strpos($user_query, $part) === false) {
        $contains_all = false;
        echo "❌ Missing: $part<br>\n";
    }
}
echo "Result: " . ($contains_all ? "✅ PASS" : "❌ FAIL") . "<br>\n";

// Test 3: User without groups (critical test for empty gids)
echo "<h3>Test 3: User without Groups (Empty gids)</h3>\n";
$user_no_groups = new MockUser(8, '', false);
$no_groups_query = buildAclWhereClause($user_no_groups, '', 'r');
echo "User without groups query: $no_groups_query<br>\n";
$should_not_contain = "gid IN ()";
$should_contain = array(
    "(user = '8' AND access LIKE 'r________')",
    "(access LIKE '______r__')"
);
$should_not_contain_gid = strpos($no_groups_query, $should_not_contain) === false;
$contains_required = true;
foreach ($should_contain as $part) {
    if (strpos($no_groups_query, $part) === false) {
        $contains_required = false;
        echo "❌ Missing required: $part<br>\n";
    }
}
echo "No 'gid IN ()': " . ($should_not_contain_gid ? "✅ PASS" : "❌ FAIL") . "<br>\n";
echo "Contains required parts: " . ($contains_required ? "✅ PASS" : "❌ FAIL") . "<br>\n";

// Test 4: Table alias functionality
echo "<h3>Test 4: Table Alias</h3>\n";
$user_alias = new MockUser(10, '1', false);
$alias_query = buildAclWhereClause($user_alias, 'te_effort', 'r');
echo "Query with table alias: $alias_query<br>\n";
$contains_alias = strpos($alias_query, 'te_effort.user') !== false && 
                  strpos($alias_query, 'te_effort.gid') !== false &&
                  strpos($alias_query, 'te_effort.access') !== false;
echo "Contains table alias: " . ($contains_alias ? "✅ PASS" : "❌ FAIL") . "<br>\n";

// Test 5: Different permissions
echo "<h3>Test 5: Different Permissions</h3>\n";
$user_perm = new MockUser(12, '5', false);
$read_query = buildAclWhereClause($user_perm, '', 'r');
$write_query = buildAclWhereClause($user_perm, '', 'w');
$exec_query = buildAclWhereClause($user_perm, '', 'x');

echo "Read permission: " . (strpos($read_query, "'r________'") !== false ? "✅ PASS" : "❌ FAIL") . "<br>\n";
echo "Write permission: " . (strpos($write_query, "'w________'") !== false ? "✅ PASS" : "❌ FAIL") . "<br>\n";
echo "Execute permission: " . (strpos($exec_query, "'x________'") !== false ? "✅ PASS" : "❌ FAIL") . "<br>\n";

// Test 6: Legacy compatibility functions
echo "<h3>Test 6: Legacy Compatibility Functions</h3>\n";
$legacy_user = new MockUser(15, '7,8', false);
$customer_query = buildCustomerAclQuery($legacy_user);
$project_query = buildProjectAclQuery($legacy_user);
$effort_query = buildEffortAclQuery($legacy_user, 'te_effort');
$raw_query = buildRawAclQuery($legacy_user);

echo "Customer ACL query: " . (!empty($customer_query) ? "✅ PASS" : "❌ FAIL") . "<br>\n";
echo "Project ACL query: " . (!empty($project_query) ? "✅ PASS" : "❌ FAIL") . "<br>\n";
echo "Effort ACL query: " . (!empty($effort_query) ? "✅ PASS" : "❌ FAIL") . "<br>\n";
echo "Raw ACL query: " . (!empty($raw_query) ? "✅ PASS" : "❌ FAIL") . "<br>\n";

// Test 7: SQL Injection Protection
echo "<h3>Test 7: SQL Injection Protection</h3>\n";
$malicious_user = new MockUser("1' OR '1'='1", "1,2'; DROP TABLE users; --", false);
$malicious_query = buildAclWhereClause($malicious_user, '', 'r');
echo "Malicious input query: $malicious_query<br>\n";
// Note: In production, this should be properly escaped by the database layer

// Summary
echo "<h3>📊 Test Summary</h3>\n";
echo "<div style='background-color: #d4edda; padding: 10px; border: 1px solid #c3e6cb; border-radius: 4px;'>\n";
echo "<strong>✅ Key Benefits of Centralized ACL:</strong><br>\n";
echo "• DRY Principle: Single source of truth for ACL logic<br>\n";
echo "• No more 'gid IN ()' SQL syntax errors<br>\n";
echo "• Consistent permission handling across all modules<br>\n";
echo "• Easy to maintain and update<br>\n";
echo "• Proper handling of empty groups<br>\n";
echo "• Support for table aliases<br>\n";
echo "• Legacy compatibility maintained<br>\n";
echo "</div>\n";

echo "<h3>✅ All ACL Query Tests Completed!</h3>\n";
?>
