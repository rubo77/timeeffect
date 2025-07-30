<?php
/**
 * Unit Test for User Management (Edit and Creation)
 * Tests the clean mode-based password validation system
 */

// Set CLI environment variables to prevent bootstrap errors
$_SERVER['REQUEST_URI'] = '/tests/unit/UserManagementTest.php';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['DOCUMENT_ROOT'] = '/var/www/timeeffect';

require_once(__DIR__ . "/../../include/config.inc.php");
require_once(__DIR__ . "/../../include/user.inc.php");

class UserManagementTest {
    private $testResults = [];
    private $testUser = null;
    
    public function __construct() {
        // Enable debug logging for tests
        $GLOBALS['_PJ_debug'] = true;
        debugLog("TEST_INIT", "Starting User Management Tests");
    }
    
    /**
     * Run all tests
     */
    public function runAllTests() {
        echo "<h2>User Management Unit Tests</h2>\n";
        echo "<div style='font-family: monospace; background: #f5f5f5; padding: 10px;'>\n";
        
        $this->testNewUserCreation();
        $this->testUserEditWithoutPasswordChange();
        $this->testUserEditWithPasswordChange();
        $this->testPasswordValidation();
        $this->cleanup();
        
        $this->printResults();
        echo "</div>\n";
    }
    
    /**
     * Test 1: New User Creation
     */
    private function testNewUserCreation() {
        debugLog("TEST_NEW_USER", "Testing new user creation with mode=new");
        
        $testData = [
            'mode' => 'new',
            'username' => 'testuser_' . time(),
            'password' => 'testpass123',
            'password_retype' => 'testpass123',
            'firstname' => 'Test',
            'lastname' => 'User',
            'email' => 'test@example.com',
            'permissions' => 'agent',
            'gids' => '',
            'allow_nc' => '0',
            'telephone' => '',
            'facsimile' => ''
        ];
        
        try {
            $user = new User($testData);
            $result = $user->save();
            
            if (empty($result)) {
                $this->testResults[] = "✅ NEW USER CREATION: SUCCESS";
                $this->testUser = $testData['username']; // Store for cleanup
                debugLog("TEST_NEW_USER", "New user created successfully");
            } else {
                $this->testResults[] = "❌ NEW USER CREATION: FAILED - " . $result;
                debugLog("TEST_NEW_USER", "New user creation failed: " . $result);
            }
        } catch (Exception $e) {
            $this->testResults[] = "❌ NEW USER CREATION: EXCEPTION - " . $e->getMessage();
            debugLog("TEST_NEW_USER", "Exception: " . $e->getMessage());
        }
    }
    
    /**
     * Test 2: User Edit without Password Change
     */
    private function testUserEditWithoutPasswordChange() {
        debugLog("TEST_EDIT_NO_PW", "Testing user edit without password change (mode=edit)");
        
        if (!$this->testUser) {
            $this->testResults[] = "⚠️ USER EDIT (NO PW): SKIPPED - No test user available";
            return;
        }
        
        // Find the created user ID
        $db = new Database();
        $db->connect();
        $safeUsername = DatabaseSecurity::escapeString($this->testUser, $db->Link_ID);
        $query = "SELECT id FROM " . $GLOBALS['_PJ_auth_table'] . " WHERE username='$safeUsername'";
        $db->query($query);
        
        if (!$db->next_record()) {
            $this->testResults[] = "❌ USER EDIT (NO PW): FAILED - Test user not found";
            return;
        }
        
        $userId = $db->Record['id'];
        
        $testData = [
            'id' => $userId,
            'mode' => 'edit',
            'username' => $this->testUser,
            'password' => '', // Empty = no password change
            'password_retype' => '',
            'firstname' => 'Test Updated',
            'lastname' => 'User Updated',
            'email' => 'test_updated@example.com',
            'permissions' => 'agent',
            'gids' => '',
            'allow_nc' => '0',
            'telephone' => '123456789',
            'facsimile' => ''
        ];
        
        try {
            $user = new User($testData);
            $result = $user->save();
            
            if (empty($result)) {
                $this->testResults[] = "✅ USER EDIT (NO PW): SUCCESS - User updated without password change";
                debugLog("TEST_EDIT_NO_PW", "User edit without password change successful");
            } else {
                $this->testResults[] = "❌ USER EDIT (NO PW): FAILED - " . $result;
                debugLog("TEST_EDIT_NO_PW", "User edit failed: " . $result);
            }
        } catch (Exception $e) {
            $this->testResults[] = "❌ USER EDIT (NO PW): EXCEPTION - " . $e->getMessage();
            debugLog("TEST_EDIT_NO_PW", "Exception: " . $e->getMessage());
        }
    }
    
    /**
     * Test 3: User Edit with Password Change
     */
    private function testUserEditWithPasswordChange() {
        debugLog("TEST_EDIT_WITH_PW", "Testing user edit with password change (mode=edit)");
        
        if (!$this->testUser) {
            $this->testResults[] = "⚠️ USER EDIT (WITH PW): SKIPPED - No test user available";
            return;
        }
        
        // Find the created user ID
        $db = new Database();
        $db->connect();
        $safeUsername = DatabaseSecurity::escapeString($this->testUser, $db->Link_ID);
        $query = "SELECT id FROM " . $GLOBALS['_PJ_auth_table'] . " WHERE username='$safeUsername'";
        $db->query($query);
        
        if (!$db->next_record()) {
            $this->testResults[] = "❌ USER EDIT (WITH PW): FAILED - Test user not found";
            return;
        }
        
        $userId = $db->Record['id'];
        
        $testData = [
            'id' => $userId,
            'mode' => 'edit',
            'username' => $this->testUser,
            'password' => 'newpassword456',
            'password_retype' => 'newpassword456',
            'firstname' => 'Test Final',
            'lastname' => 'User Final',
            'email' => 'test_final@example.com',
            'permissions' => 'agent',
            'gids' => '',
            'allow_nc' => '0',
            'telephone' => '987654321',
            'facsimile' => ''
        ];
        
        try {
            $user = new User($testData);
            $result = $user->save();
            
            if (empty($result)) {
                $this->testResults[] = "✅ USER EDIT (WITH PW): SUCCESS - User updated with password change";
                debugLog("TEST_EDIT_WITH_PW", "User edit with password change successful");
            } else {
                $this->testResults[] = "❌ USER EDIT (WITH PW): FAILED - " . $result;
                debugLog("TEST_EDIT_WITH_PW", "User edit with password failed: " . $result);
            }
        } catch (Exception $e) {
            $this->testResults[] = "❌ USER EDIT (WITH PW): EXCEPTION - " . $e->getMessage();
            debugLog("TEST_EDIT_WITH_PW", "Exception: " . $e->getMessage());
        }
    }
    
    /**
     * Test 4: Password Validation Edge Cases
     */
    private function testPasswordValidation() {
        debugLog("TEST_PW_VALIDATION", "Testing password validation edge cases");
        
        // Test 4a: New user without password
        $testData = [
            'mode' => 'new',
            'username' => 'testuser_nopw_' . time(),
            'password' => '', // Empty password for new user
            'password_retype' => '',
            'firstname' => 'Test',
            'lastname' => 'NoPassword',
            'email' => 'nopw@example.com',
            'permissions' => 'agent',
            'gids' => '',
            'allow_nc' => '0',
            'telephone' => '',
            'facsimile' => ''
        ];
        
        try {
            $user = new User($testData);
            $result = $user->save();
            
            if (!empty($result) && strpos($result, 'Passwort') !== false) {
                $this->testResults[] = "✅ PASSWORD VALIDATION: SUCCESS - New user requires password";
                debugLog("TEST_PW_VALIDATION", "Correctly rejected new user without password");
            } else {
                $this->testResults[] = "❌ PASSWORD VALIDATION: FAILED - New user without password was accepted";
                debugLog("TEST_PW_VALIDATION", "Incorrectly accepted new user without password");
            }
        } catch (Exception $e) {
            $this->testResults[] = "❌ PASSWORD VALIDATION: EXCEPTION - " . $e->getMessage();
            debugLog("TEST_PW_VALIDATION", "Exception: " . $e->getMessage());
        }
        
        // Test 4b: Password mismatch
        $testData['password'] = 'password1';
        $testData['password_retype'] = 'password2';
        
        try {
            $user = new User($testData);
            $result = $user->save();
            
            if (!empty($result) && strpos($result, 'retype') !== false) {
                $this->testResults[] = "✅ PASSWORD VALIDATION: SUCCESS - Password mismatch detected";
                debugLog("TEST_PW_VALIDATION", "Correctly rejected password mismatch");
            } else {
                $this->testResults[] = "❌ PASSWORD VALIDATION: FAILED - Password mismatch not detected";
                debugLog("TEST_PW_VALIDATION", "Failed to detect password mismatch");
            }
        } catch (Exception $e) {
            $this->testResults[] = "❌ PASSWORD VALIDATION: EXCEPTION - " . $e->getMessage();
            debugLog("TEST_PW_VALIDATION", "Exception: " . $e->getMessage());
        }
    }
    
    /**
     * Cleanup test data
     */
    private function cleanup() {
        if ($this->testUser) {
            debugLog("TEST_CLEANUP", "Cleaning up test user: " . $this->testUser);
            
            try {
                $db = new Database();
                $db->connect();
                $safeUsername = DatabaseSecurity::escapeString($this->testUser, $db->Link_ID);
                $query = "DELETE FROM " . $GLOBALS['_PJ_auth_table'] . " WHERE username='$safeUsername'";
                $db->query($query);
                
                $this->testResults[] = "🧹 CLEANUP: Test user removed";
                debugLog("TEST_CLEANUP", "Test user cleaned up successfully");
            } catch (Exception $e) {
                $this->testResults[] = "⚠️ CLEANUP: Failed to remove test user - " . $e->getMessage();
                debugLog("TEST_CLEANUP", "Cleanup failed: " . $e->getMessage());
            }
        }
    }
    
    /**
     * Print test results
     */
    private function printResults() {
        echo "<h3>Test Results:</h3>\n";
        foreach ($this->testResults as $result) {
            echo $result . "<br>\n";
        }
        
        $passed = count(array_filter($this->testResults, function($r) { return strpos($r, '✅') === 0; }));
        $failed = count(array_filter($this->testResults, function($r) { return strpos($r, '❌') === 0; }));
        $skipped = count(array_filter($this->testResults, function($r) { return strpos($r, '⚠️') === 0; }));
        
        echo "<br><strong>Summary: {$passed} passed, {$failed} failed, {$skipped} skipped/warnings</strong><br>\n";
        debugLog("TEST_SUMMARY", "Tests completed: {$passed} passed, {$failed} failed, {$skipped} skipped");
    }
}

// Auto-run tests if accessed directly
if (basename($_SERVER['PHP_SELF']) === 'UserManagementTest.php') {
    $test = new UserManagementTest();
    $test->runAllTests();
}
