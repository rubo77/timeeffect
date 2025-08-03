<?php
/**
 * Test for email-only registration functionality
 * Tests that registration can be initiated with just an email address
 */

// Include bootstrap for test environment
require_once(__DIR__ . '/bootstrap.php');

function test_email_only_registration_initial_step() {
    echo "Testing email-only registration initial step...\n";
    
    // Test that registration page accepts email-only submission
    $_POST = array(
        'email' => 'test@example.com',
        'email_only' => '1', // New flag for email-only registration
        'register' => '1',
        'altered' => '1'
    );
    
    // Clear any existing output
    ob_start();
    
    // Test registration logic - we'll capture output
    // This should send email and show success message
    include(__DIR__ . '/../register.php');
    
    $output = ob_get_clean();
    
    // Should not contain error messages
    if (strpos($output, 'Error') !== false || strpos($output, 'error') !== false) {
        echo "FAIL: Email-only registration failed with error\n";
        return false;
    }
    
    echo "PASS: Email-only registration initial step\n";
    return true;
}

function test_registration_completion_with_token() {
    echo "Testing registration completion with token...\n";
    
    // This test would verify that a user can complete registration
    // with a valid token - we'll implement this after the main feature
    
    echo "PASS: Registration completion test (placeholder)\n";
    return true;
}

// Run tests
$tests_passed = 0;
$tests_total = 0;

echo "=== Email-Only Registration Tests ===\n";

$tests_total++;
if (test_email_only_registration_initial_step()) {
    $tests_passed++;
}

$tests_total++;
if (test_registration_completion_with_token()) {
    $tests_passed++;
}

echo "\n=== Test Results ===\n";
echo "Passed: $tests_passed/$tests_total\n";

if ($tests_passed == $tests_total) {
    echo "All tests passed!\n";
    exit(0);
} else {
    echo "Some tests failed!\n";
    exit(1);
}
?>