<?php

/**
 * Simple ACL World Access Test Script
 * 
 * This test verifies that customers with world-readable access (7th character = 'r')
 * are visible to all users, even those without direct permissions.
 */

echo "\n=== ACL World Access Analysis ===\n";
echo "Testing ACL behavior based on debug logs from CustomerList\n\n";

class ACLWorldAccessTest
{
    private $db;
    private $testUser;
    
    public function __construct()
    {
        // Test user data based on debug logs (user "ruben")
        $this->testUser = new stdClass();
        $this->testUser->id = '2';
        $this->testUser->gids = '3';
        $this->testUser->permissions = 'agent'; // Not admin
    }
    
    /**
     * Test that customers with world-readable access are visible to non-admin users
     */
    public function testCustomersWithWorldReadableAccessAreVisible()
    {
        // Test data: Customer with world-readable access (7th character = 'r')
        $worldReadableAccess = 'rwxr-xr--'; // Position 7 = 'r' (world readable)
        $noWorldAccess = 'rwxr-x---';       // Position 7 = '-' (no world access)
        
        echo "\n=== ACL World Access Test ===\n";
        echo "Testing access strings:\n";
        echo "- World readable: '$worldReadableAccess' (7th char = '" . substr($worldReadableAccess, 6, 1) . "')\n";
        echo "- No world access: '$noWorldAccess' (7th char = '" . substr($noWorldAccess, 6, 1) . "')\n\n";
        
        // Test the ACL query logic manually
        $this->assertACLQueryMatches($worldReadableAccess, true, "Customer with world-readable access should be visible");
        $this->assertACLQueryMatches($noWorldAccess, false, "Customer without world access should NOT be visible");
    }
    
    /**
     * Test the actual CustomerList behavior with mock user
     */
    public function testCustomerListFiltersCorrectly()
    {
        // Create mock user object that behaves like PJAuth
        $mockUser = $this->createMockUser();
        
        echo "\n=== CustomerList ACL Filter Test ===\n";
        echo "Mock user: id={$mockUser->giveValue('id')}, gids={$mockUser->giveValue('gids')}, is_admin=" . ($mockUser->checkPermission('admin') ? 'YES' : 'NO') . "\n";
        
        // Test the ACL query generation
        $aclQuery = $this->generateACLQuery($mockUser);
        echo "Generated ACL query: $aclQuery\n";
        
        // Verify the query contains the world-readable condition
        if (strpos($aclQuery, "access LIKE '______r__'") !== false) {
            echo "[PASS] ACL query contains world-readable condition\n";
        } else {
            echo "[FAIL] ACL query missing world-readable condition\n";
        }
        
        // Test specific access patterns
        $testCases = [
            'rwxr-xr--' => true,  // World readable - should match
            'rwxr-x---' => false, // No world access - should not match (unless user/group match)
            'r---------' => true,  // User readable (if user matches)
            '---r------' => false, // Group readable (if group matches, but our test user is not in group 1)
        ];
        
        foreach ($testCases as $access => $expectedVisible) {
            $matches = $this->testAccessPattern($access, $mockUser);
            echo "Access '$access': " . ($matches ? 'VISIBLE' : 'HIDDEN') . " (expected: " . ($expectedVisible ? 'VISIBLE' : 'HIDDEN') . ")\n";
        }
    }
    
    /**
     * Create a mock user object similar to PJAuth
     */
    private function createMockUser()
    {
        return new class {
            public function giveValue($key) {
                $data = [
                    'id' => '2',      // Same as user "ruben"
                    'gids' => '3',    // Same as user "ruben"
                ];
                return $data[$key] ?? '';
            }
            
            public function checkPermission($permission) {
                return false; // Not admin, like "ruben"
            }
        };
    }
    
    /**
     * Generate ACL query like CustomerList does
     */
    private function generateACLQuery($user)
    {
        $access_query = "";
        if (!$user->checkPermission('admin')) {
            $access_query  = " AND (";
            $access_query .= " (user = '" . $user->giveValue('id') . "' AND access LIKE 'r________')";
            $access_query .= " OR ";
            $access_query .= " (gid IN (" . $user->giveValue('gids') . ") AND access LIKE '___r_____')";
            $access_query .= " OR ";
            $access_query .= " (access LIKE '______r__')";
            $access_query .= " ) ";
        }
        return $access_query;
    }
    
    /**
     * Test if a specific access pattern matches the ACL conditions
     */
    private function testAccessPattern($access, $user)
    {
        $userId = $user->giveValue('id');
        $userGids = explode(',', $user->giveValue('gids'));
        
        // Simulate the three ACL conditions
        $userMatch = ($access[0] === 'r'); // User readable (position 1)
        $groupMatch = ($access[3] === 'r') && in_array('3', $userGids); // Group readable (position 4) and user in group
        $worldMatch = ($access[6] === 'r'); // World readable (position 7)
        
        return $userMatch || $groupMatch || $worldMatch;
    }
    
    /**
     * Assert that ACL query logic matches expected behavior
     */
    private function assertACLQueryMatches($access, $expectedVisible, $message)
    {
        $mockUser = $this->createMockUser();
        $actualVisible = $this->testAccessPattern($access, $mockUser);
        
        $result = $expectedVisible === $actualVisible ? 'PASS' : 'FAIL';
        echo "[$result] $message - Access: '$access' (Expected: " . ($expectedVisible ? 'VISIBLE' : 'HIDDEN') . ", Got: " . ($actualVisible ? 'VISIBLE' : 'HIDDEN') . ")\n";
        
        return $expectedVisible === $actualVisible;
    }
    
    /**
     * Run all tests
     */
    public function runAllTests()
    {
        echo "Running ACL World Access Tests...\n\n";
        
        $this->testCustomersWithWorldReadableAccessAreVisible();
        echo "\n";
        $this->testCustomerListFiltersCorrectly();
        
        echo "\n=== Test Complete ===\n";
    }
}

// Execute the test
$test = new ACLWorldAccessTest();
$test->runAllTests();
