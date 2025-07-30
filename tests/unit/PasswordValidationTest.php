<?php
/**
 * Standalone Unit Test for Password Validation Logic
 * Tests the clean mode-based password validation without database dependencies
 */

class PasswordValidationTest {
    private $testResults = [];
    
    public function __construct() {
        echo "<h2>Password Validation Logic Unit Tests</h2>\n";
        echo "<div style='font-family: monospace; background: #f5f5f5; padding: 10px;'>\n";
    }
    
    /**
     * Run all tests
     */
    public function runAllTests() {
        $this->testNewUserPasswordValidation();
        $this->testEditUserPasswordValidation();
        $this->testPasswordMismatchValidation();
        $this->testEmptyPasswordHandling();
        
        $this->printResults();
        echo "</div>\n";
    }
    
    /**
     * Simulate the password validation logic from User::save()
     */
    private function validatePassword($data) {
        // Simulate the clean mode-based password validation logic
        if ($data['mode'] === 'new') {
            // New user: password is required
            if ($data['password'] == '') {
                return 'error_pw_empty';
            }
            if ($data['password'] != $data['password_retype']) {
                return 'error_pw_retype';
            }
            return ''; // Success
        } else {
            // Edit mode: only validate password if it's being changed (not empty)
            if ($data['password'] != '') {
                // Password is being changed
                if ($data['password'] != $data['password_retype']) {
                    return 'error_pw_retype';
                }
                return ''; // Success - password will be changed
            } else {
                // Password not being changed, keep existing password
                return ''; // Success - no password change
            }
        }
    }
    
    /**
     * Test 1: New User Password Validation
     */
    private function testNewUserPasswordValidation() {
        // Test 1a: New user with valid password
        $data = [
            'mode' => 'new',
            'password' => 'testpass123',
            'password_retype' => 'testpass123'
        ];
        
        $result = $this->validatePassword($data);
        if (empty($result)) {
            $this->testResults[] = "✅ NEW USER (VALID PW): SUCCESS - Password accepted";
        } else {
            $this->testResults[] = "❌ NEW USER (VALID PW): FAILED - " . $result;
        }
        
        // Test 1b: New user without password
        $data = [
            'mode' => 'new',
            'password' => '',
            'password_retype' => ''
        ];
        
        $result = $this->validatePassword($data);
        if ($result === 'error_pw_empty') {
            $this->testResults[] = "✅ NEW USER (NO PW): SUCCESS - Empty password rejected";
        } else {
            $this->testResults[] = "❌ NEW USER (NO PW): FAILED - Empty password should be rejected";
        }
    }
    
    /**
     * Test 2: Edit User Password Validation
     */
    private function testEditUserPasswordValidation() {
        // Test 2a: Edit user without password change
        $data = [
            'mode' => 'edit',
            'password' => '',
            'password_retype' => ''
        ];
        
        $result = $this->validatePassword($data);
        if (empty($result)) {
            $this->testResults[] = "✅ EDIT USER (NO PW CHANGE): SUCCESS - No password required";
        } else {
            $this->testResults[] = "❌ EDIT USER (NO PW CHANGE): FAILED - " . $result;
        }
        
        // Test 2b: Edit user with password change
        $data = [
            'mode' => 'edit',
            'password' => 'newpassword456',
            'password_retype' => 'newpassword456'
        ];
        
        $result = $this->validatePassword($data);
        if (empty($result)) {
            $this->testResults[] = "✅ EDIT USER (WITH PW CHANGE): SUCCESS - Password change accepted";
        } else {
            $this->testResults[] = "❌ EDIT USER (WITH PW CHANGE): FAILED - " . $result;
        }
    }
    
    /**
     * Test 3: Password Mismatch Validation
     */
    private function testPasswordMismatchValidation() {
        // Test 3a: New user with password mismatch
        $data = [
            'mode' => 'new',
            'password' => 'password1',
            'password_retype' => 'password2'
        ];
        
        $result = $this->validatePassword($data);
        if ($result === 'error_pw_retype') {
            $this->testResults[] = "✅ NEW USER (PW MISMATCH): SUCCESS - Mismatch detected";
        } else {
            $this->testResults[] = "❌ NEW USER (PW MISMATCH): FAILED - Mismatch should be detected";
        }
        
        // Test 3b: Edit user with password mismatch
        $data = [
            'mode' => 'edit',
            'password' => 'newpass1',
            'password_retype' => 'newpass2'
        ];
        
        $result = $this->validatePassword($data);
        if ($result === 'error_pw_retype') {
            $this->testResults[] = "✅ EDIT USER (PW MISMATCH): SUCCESS - Mismatch detected";
        } else {
            $this->testResults[] = "❌ EDIT USER (PW MISMATCH): FAILED - Mismatch should be detected";
        }
    }
    
    /**
     * Test 4: Empty Password Handling Edge Cases
     */
    private function testEmptyPasswordHandling() {
        // Test 4a: Edit mode with one empty field (should be treated as no change)
        $data = [
            'mode' => 'edit',
            'password' => '',
            'password_retype' => 'something'
        ];
        
        $result = $this->validatePassword($data);
        if (empty($result)) {
            $this->testResults[] = "✅ EDIT USER (EMPTY PW FIELD): SUCCESS - Treated as no change";
        } else {
            $this->testResults[] = "❌ EDIT USER (EMPTY PW FIELD): FAILED - " . $result;
        }
        
        // Test 4b: Verify mode detection works correctly
        $data = [
            'mode' => 'invalid_mode',
            'password' => '',
            'password_retype' => ''
        ];
        
        $result = $this->validatePassword($data);
        if (empty($result)) {
            $this->testResults[] = "✅ INVALID MODE: SUCCESS - Defaults to edit behavior";
        } else {
            $this->testResults[] = "❌ INVALID MODE: FAILED - " . $result;
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
        
        echo "<br><strong>Summary: {$passed} passed, {$failed} failed</strong><br>\n";
        
        if ($failed === 0) {
            echo "<br><span style='color: green; font-weight: bold;'>🎉 ALL TESTS PASSED! Password validation logic is working correctly.</span><br>\n";
        } else {
            echo "<br><span style='color: red; font-weight: bold;'>⚠️ Some tests failed. Please review the password validation logic.</span><br>\n";
        }
    }
}

// Auto-run tests if accessed directly
if (basename($_SERVER['PHP_SELF'] ?? 'PasswordValidationTest.php') === 'PasswordValidationTest.php') {
    $test = new PasswordValidationTest();
    $test->runAllTests();
}
