<?php

/* *********************************************************
 see error messages throuout the app                      */
if($GLOBALS['debug']){
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL);
}
/* ****************************************************** */


if(!empty($_POST)) foreach($_POST as $p_k=>$p_v) $$p_k=$p_v;
if(!empty($_GET)) foreach($_GET as $get_k=>$get_v) $$get_k=$get_v;
if(!empty($_SESSION)) foreach($_SESSION as $sess_k=>$sess_v) $$sess_k=$sess_v;

# on new apache installations everything is stored in $_SERVER, so
#this is the fix for that:
if (isset($_SERVER)) foreach($_SERVER as $s_k=>$s_v) $$s_k=$s_v;

$PHP_SELF=$_SERVER['PHP_SELF'];

require_once('fix_mysql.inc.php');
