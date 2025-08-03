<?php
// Unified header include for all TimeEffect pages
// Manages dynamic favicon, CSS, and common meta tags

// Get user theme preference for data-theme attribute
$user_theme = 'system'; // default
if(isset($_PJ_auth) && is_object($_PJ_auth) && method_exists($_PJ_auth, 'giveValue')) {
    $theme_pref = $_PJ_auth->giveValue('theme_preference');
    if(!empty($theme_pref)) {
        $user_theme = $theme_pref;
    }
}

// Dynamic favicon logic based on page context
$favicon_path = '/favicon.png'; // default

// Check for specific page contexts
if (isset($favicon)) {
    // Use explicitly set favicon
    $favicon_path = $favicon;
} else {
    // Auto-detect favicon based on page context
    $current_script = $_SERVER['SCRIPT_NAME'] ?? '';
    $query_string = $_SERVER['QUERY_STRING'] ?? '';
    
    // Check for effort-related pages
    if (strpos($current_script, 'efforts.php') !== false) {
        // Check if it's a new effort page
        if (strpos($query_string, 'action=new') !== false || 
            strpos($query_string, 'action=add') !== false ||
            (!isset($_REQUEST['eid']) && !isset($_REQUEST['action']))) {
            $favicon_path = $GLOBALS['_PJ_image_path'] . '/start.png';
        }
        // Check if it's a stop all page
        elseif (strpos($query_string, 'stop_all=1') !== false || 
                strpos($query_string, 'action=stop_all') !== false) {
            $favicon_path = $GLOBALS['_PJ_image_path'] . '/stop.png';
        }
    }
    
    // Check for specific action parameters
    if (isset($_REQUEST['action'])) {
        switch ($_REQUEST['action']) {
            case 'new':
            case 'add':
                $favicon_path = $GLOBALS['_PJ_image_path'] . '/start.png';
                break;
            case 'stop_all':
                $favicon_path = $GLOBALS['_PJ_image_path'] . '/stop.png';
                break;
        }
    }
    
    // Check for stop_all parameter
    if (isset($_REQUEST['stop_all']) && $_REQUEST['stop_all'] == '1') {
        $favicon_path = $GLOBALS['_PJ_image_path'] . '/stop.png';
    }
}

// Set default title if not provided
$page_title = isset($center_title) ? $center_title : 'TimeEffect';
?>
<HTML<?php if($user_theme !== 'system') echo ' data-theme="' . htmlspecialchars($user_theme) . '"'; ?>>
<HEAD>
<TITLE>TIMEEFFECT - <?= htmlspecialchars($page_title) ?></TITLE>

<!-- Mobile viewport and PWA meta tags -->
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="theme-color" content="#007bff">

<!-- Dynamic Favicon -->
<link rel="icon" href="<?= htmlspecialchars($favicon_path) ?>" type="image/x-icon">
<link rel="shortcut icon" href="<?= htmlspecialchars($favicon_path) ?>" type="image/x-icon">

<!-- CSS Stylesheets -->
<link rel="stylesheet" href="<?= $GLOBALS['_PJ_css_path'] ?>/project.css" type="text/css">
<?php if (file_exists($_SERVER['DOCUMENT_ROOT'] . $GLOBALS['_PJ_css_path'] . '/responsive.css')): ?>
<link rel="stylesheet" href="<?= $GLOBALS['_PJ_css_path'] ?>/responsive.css" type="text/css">
<?php endif; ?>
<link rel="stylesheet" href="<?= $GLOBALS['_PJ_css_path'] ?>/modern.css" type="text/css">
<link rel="stylesheet" href="<?= $GLOBALS['_PJ_css_path'] ?>/layout.css" type="text/css">

<!-- JavaScript -->
<script src="<?= $GLOBALS['_PJ_http_root'] ?>/include/functions.js" type="text/javascript"></script>

<?php if (isset($additional_head_content)): ?>
<!-- Additional head content -->
<?= $additional_head_content ?>
<?php endif; ?>
</HEAD>

<?php if (isset($body_attributes)): ?>
<BODY <?= $body_attributes ?>>
<?php else: ?>
<BODY>
<?php endif; ?>

<?php if ($user_theme !== 'system'): ?>
<script>
// Initialize user theme preference from PHP
(function() {
    var userTheme = '<?php echo htmlspecialchars($user_theme); ?>';
    if (userTheme !== 'system') {
        document.documentElement.setAttribute('data-theme', userTheme);
    }
})();
</script>
<?php endif; ?>

<?php if (isset($additional_body_scripts)): ?>
<!-- Additional body scripts -->
<?= $additional_body_scripts ?>
<?php endif; ?>
