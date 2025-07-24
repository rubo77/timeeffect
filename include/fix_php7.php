<?php

/* *********************************************************
 see error messages throuout the app                      */
if(isset($GLOBALS['debug']) && $GLOBALS['debug']){
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL);
}
/* ****************************************************** */


// WARNING: Variable variables are a security risk - consider refactoring
// Only allow safe variable names (alphanumeric + underscore)
// Log rejected variable names for security monitoring
$rejected_vars = [];
// if(!empty($_POST)) {
//     foreach($_POST as $p_k=>$p_v) {
//         if(preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $p_k)) {
//             $$p_k=$p_v;
//         } else {
//             $rejected_vars[] = 'POST:' . $p_k;
//         }
//     }
// }
// if(!empty($_GET)) {
//     foreach($_GET as $get_k=>$get_v) {
//         if(preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $get_k)) {
//             $$get_k=$get_v;
//         } else {
//             $rejected_vars[] = 'GET:' . $get_k;
//         }
//     }
// }
if(!empty($_SESSION)) {
    foreach($_SESSION as $sess_k=>$sess_v) {
        if(preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $sess_k)) {
            $$sess_k=$sess_v;
        } else {
            $rejected_vars[] = 'SESSION:' . $sess_k;
        }
    }
}

# on new apache installations everything is stored in $_SERVER, so
#this is the fix for that:
if (isset($_SERVER)) {
    foreach($_SERVER as $s_k=>$s_v) {
        if(preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $s_k)) {
            $$s_k=$s_v;
        } else {
            $rejected_vars[] = 'SERVER:' . $s_k;
        }
    }
}

// Log security events if any variables were rejected
if (!empty($rejected_vars) && isset($GLOBALS['logger'])) {
    $GLOBALS['logger']->warning('Variable variables security filter activated', [
        'rejected_variables' => $rejected_vars,
        'client_ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
    ]);
}

$PHP_SELF = isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : '';

require_once('fix_mysql.inc.php');
