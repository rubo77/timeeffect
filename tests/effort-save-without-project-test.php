<?php
/**
 * Functional Test: Save Effort Without Project
 * 
 * This test reproduces the issue where saving an effort without a project
 * causes PHP warnings and MySQL constraint errors.
 * 
 * Test Flow:
 * 1. Login as admin/admin via direct session simulation
 * 2. Simulate POST request to save effort without project
 * 3. Capture and analyze errors
 */

// Set up environment to match web request
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['REQUEST_URI'] = '/inventory/efforts.php';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['SCRIPT_NAME'] = '/inventory/efforts.php';
$_SERVER['PHP_SELF'] = '/inventory/efforts.php';

// Simulate POST data for effort without project
$_POST = [
    'edit' => '1',
    'altered' => '1',
    'description' => 'Test effort without project',
    'note' => 'This is a test note',
    'day' => date('d'),
    'month' => date('m'),
    'year' => date('Y'),
    'hour' => '09',
    'minute' => '00',
    'hours' => '1',
    'minutes' => '30',
    'billing_day' => '',
    'billing_month' => '',
    'billing_year' => '',
    'gid' => '',
    'access_owner' => 'rw-',
    'access_group' => 'r--',
    'access_world' => '---',
    // Intentionally leaving out project data
    'cid' => '',
    'pid' => '',
    'selected_cid' => '',
    'selected_pid' => '',
    'eid' => '',
    'id' => ''
];

// Copy POST to REQUEST
$_REQUEST = $_POST;

echo "=== Effort Save Without Project Test ===\n";
echo "Date: " . date('Y-m-d H:i:s') . "\n";
echo "Simulating POST request with data:\n";
echo json_encode(array_keys($_POST), JSON_PRETTY_PRINT) . "\n\n";

// Start output buffering to capture any output/errors
ob_start();

// Enable error reporting to catch all issues
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Custom error handler to capture errors
$errors = [];
set_error_handler(function($severity, $message, $file, $line) use (&$errors) {
    $errors[] = [
        'type' => 'PHP Error',
        'severity' => $severity,
        'message' => $message,
        'file' => $file,
        'line' => $line
    ];
    return false; // Let PHP handle it normally too
});

// Custom exception handler
set_exception_handler(function($exception) use (&$errors) {
    $errors[] = [
        'type' => 'Exception',
        'message' => $exception->getMessage(),
        'file' => $exception->getFile(),
        'line' => $exception->getLine(),
        'trace' => $exception->getTraceAsString()
    ];
});

try {
    echo "Step 1: Setting up authentication simulation...\n";
    
    // We need to simulate being logged in
    // Let's check what the auth system expects
    
    echo "Step 2: Including required files...\n";
    
    // Change to the correct directory
    chdir('/var/www/timeeffect');
    
    // Include the main config
    require_once 'include/config.inc.php';
    
    echo "Step 3: Simulating authenticated user session...\n";
    
    // Start session
    if (!session_id()) {
        session_start();
    }
    
    // Simulate logged in admin user
    $_SESSION['authenticated'] = true;
    $_SESSION['user_id'] = 1;
    $_SESSION['username'] = 'admin';
    
    echo "Step 4: Including efforts.php to trigger the save process...\n";
    
    // Include the efforts page which should process our POST data
    include 'inventory/efforts.php';
    
} catch (Throwable $e) {
    $errors[] = [
        'type' => 'Fatal Error',
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ];
}

// Get any output that was generated
$output = ob_get_clean();

echo "=== TEST RESULTS ===\n\n";

echo "Captured Output (" . strlen($output) . " bytes):\n";
if (!empty($output)) {
    echo "--- OUTPUT START ---\n";
    echo $output;
    echo "\n--- OUTPUT END ---\n\n";
} else {
    echo "No output captured\n\n";
}

echo "Captured Errors (" . count($errors) . " total):\n";
if (!empty($errors)) {
    foreach ($errors as $i => $error) {
        echo "Error " . ($i + 1) . ":\n";
        echo "  Type: " . $error['type'] . "\n";
        echo "  Message: " . $error['message'] . "\n";
        echo "  File: " . $error['file'] . "\n";
        echo "  Line: " . $error['line'] . "\n";
        if (isset($error['trace'])) {
            echo "  Trace: " . substr($error['trace'], 0, 200) . "...\n";
        }
        echo "\n";
    }
} else {
    echo "No errors captured\n\n";
}

echo "=== ANALYSIS ===\n";

// Analyze the results
$hasPhpWarnings = false;
$hasFatalErrors = false;
$hasMysqlErrors = false;

foreach ($errors as $error) {
    if (strpos($error['message'], 'Undefined array key') !== false) {
        $hasPhpWarnings = true;
        echo "✓ Reproduced: PHP Warning about undefined array key\n";
    }
    if (strpos($error['message'], 'cannot be null') !== false) {
        $hasMysqlErrors = true;
        echo "✓ Reproduced: MySQL constraint error (cannot be null)\n";
    }
    if ($error['type'] === 'Fatal Error') {
        $hasFatalErrors = true;
        echo "✓ Reproduced: Fatal error\n";
    }
}

if (strpos($output, 'Fatal error') !== false) {
    $hasFatalErrors = true;
    echo "✓ Reproduced: Fatal error in output\n";
}

if (strpos($output, 'cannot be null') !== false) {
    $hasMysqlErrors = true;
    echo "✓ Reproduced: MySQL constraint error in output\n";
}

echo "\n=== RECOMMENDATIONS ===\n";
echo "1. Fix undefined array key 'project_id' in effort.inc.php:390\n";
echo "2. Check database schema: ALTER TABLE to allow NULL for project_id\n";
echo "3. Add proper validation before save to prevent constraint violations\n";
echo "4. Implement graceful error handling instead of fatal errors\n";
echo "5. Add user-friendly error messages for missing project scenarios\n";

echo "\n=== TEST COMPLETED ===\n";
echo "Issues reproduced: " . ($hasPhpWarnings || $hasFatalErrors || $hasMysqlErrors ? "YES" : "NO") . "\n";

?>
