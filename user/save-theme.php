<?php
    require_once(__DIR__ . "/../bootstrap.php");
    include_once("../include/config.inc.php");
    include_once($_PJ_include_path . '/scripts.inc.php');

    // Only accept POST requests
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        exit('Method Not Allowed');
    }

    // Check if user is authenticated
    if (!$_PJ_auth->checkPermission('agent')) {
        http_response_code(401);
        exit('Unauthorized');
    }

    // Get theme from request
    $theme = $_POST['theme'] ?? null;

    // Validate theme
    if (!in_array($theme, ['light', 'dark', 'system'])) {
        http_response_code(400);
        exit('Invalid theme');
    }

    // Update user's theme preference
    $user_id = $_PJ_auth->giveValue('id');
    $db = new Database();
    
    $query = "UPDATE " . $GLOBALS['_PJ_auth_table'] . " SET theme_preference = '" . $db->escape($theme) . "' WHERE id = " . intval($user_id);
    
    if ($db->query($query)) {
        http_response_code(200);
        echo json_encode(['success' => true, 'theme' => $theme]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database update failed']);
    }
?>