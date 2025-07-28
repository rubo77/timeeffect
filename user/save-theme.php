<?php
    // Start output buffering to prevent headers-already-sent
    ob_start();
    
    // Set no_login flag to prevent auth redirect
    $no_login = true;
    
    require_once(__DIR__ . "/../bootstrap.php");
    include_once("../include/config.inc.php");
    include_once($_PJ_include_path . '/scripts.inc.php');
    
    // Clear any output from includes
    ob_clean();
    
    // Now load auth
    unset($no_login);
    include_once($_PJ_include_path . '/auth.inc.php');

    // Allow both GET and POST requests
    if (!in_array($_SERVER['REQUEST_METHOD'], ['GET', 'POST'])) {
        ob_end_clean();
        header('HTTP/1.1 405 Method Not Allowed');
        exit('Method Not Allowed');
    }

    // Check if user is authenticated
    if (!isset($_PJ_auth) || !$_PJ_auth->checkPermission('agent')) {
        ob_end_clean();
        header('HTTP/1.1 401 Unauthorized');
        exit('Unauthorized');
    }

    // Get theme from request (support both GET and POST)
    $theme = $_REQUEST['theme'] ?? null;

    // Validate theme
    if (!in_array($theme, ['light', 'dark', 'system'])) {
        ob_end_clean();
        header('HTTP/1.1 400 Bad Request');
        exit('Invalid theme');
    }

    // Update user's theme preference
    $user_id = $_PJ_auth->giveValue('id');
    $db = new Database();
    
    $query = "UPDATE " . $GLOBALS['_PJ_auth_table'] . " SET theme_preference = '" . addslashes($theme) . "' WHERE id = " . intval($user_id);
    
    if ($db->query($query)) {
        ob_end_clean();
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'theme' => $theme]);
    } else {
        ob_end_clean();
        header('HTTP/1.1 500 Internal Server Error');
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Database update failed']);
    }
?>