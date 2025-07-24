<?php
	// Modern PHP 8.4 compatibility and Composer autoloading
	require_once(__DIR__ . "/../bootstrap.php");
	include_once(__DIR__ . "/../include/config.inc.php");
	include_once($_PJ_include_path . '/scripts.inc.php');

	// Initialize variables from request
	$cid = $_REQUEST['cid'] ?? '';
	$pid = $_REQUEST['pid'] ?? '';
	$eid = $_REQUEST['eid'] ?? '';
	$new = $_REQUEST['new'] ?? null;
	$edit = $_REQUEST['edit'] ?? null;
	$rates = $_REQUEST['rates'] ?? null;
	$altered = $_REQUEST['altered'] ?? null;
	$delete = $_REQUEST['delete'] ?? null;
	$cancel = $_REQUEST['cancel'] ?? null;
	$confirm = $_REQUEST['confirm'] ?? null;
	$name = $_REQUEST['name'] ?? [];
	$customer_logo = $_REQUEST['customer_logo'] ?? '';

	$customer 	= new Customer($_PJ_auth, $cid);

	if(isset($pid)) {
		$project = new Project($customer, $_PJ_auth, $pid);
	}
	if(isset($eid)) {
		$effort = new Effort($eid, $_PJ_auth);
	}

	$center_template	= "inventory/customer";
	if(isset($new)) {
		if(!$_PJ_auth->checkPermission('admin') && !intval($_PJ_auth->giveValue('allow_nc'))) {
			$error_message		= $GLOBALS['_PJ_strings']['error_access'];
			include("$_PJ_root/templates/error.ihtml");
			include_once("$_PJ_include_path/degestiv.inc.php");
			exit;
		}
		$center_title		= $GLOBALS['_PJ_strings']['inventory'] . ': ' . $GLOBALS['_PJ_strings']['new_customer'];
		include("$_PJ_root/templates/add.ihtml");
		exit;
	}

	if(isset($edit)) {
		if($cid && !$customer->checkUserAccess('write')) {
			$error_message		= $GLOBALS['_PJ_strings']['error_access'];
			include("$_PJ_root/templates/error.ihtml");
			include_once("$_PJ_include_path/degestiv.inc.php");
			exit;
		}
		if(isset($rates)) {
			if(isset($altered)) {
				if ($cid != '') {
					$r_keys = array_keys($name);
					$r_cnt = count($r_keys);
					for($i=0;$i<$r_cnt;$i++) {
						$id = $r_keys[$i];
						if(!empty($id) and $id == 'new') {
							$id = '';
						}
						if($id != '' || $name[$r_keys[$i]] != '') {
							$data[$i]['id']			= $id;
							$data[$i]['cid']		= $cid;
							$data[$i]['name']		= $name[$r_keys[$i]];
							$data[$i]['price']		= str_replace($GLOBALS['_PJ_decimal_point'], '.', $price[$r_keys[$i]]);
							$data[$i]['currency']	= $currency[$r_keys[$i]];
						}
					}
					$rates = new Rates($data);
					$rates->save();
				}
				unset($rates);
				$center_template	= "inventory/customer/rates";
				$center_title		= $GLOBALS['_PJ_strings']['inventory'] . ': ' . $GLOBALS['_PJ_strings']['edit_rate'];
				include("$_PJ_root/templates/edit.ihtml");
			} else {
				$center_template	= "inventory/customer/rates";
				$center_title		= $GLOBALS['_PJ_strings']['inventory'] . ': ' . $GLOBALS['_PJ_strings']['edit_rate'];
				include("$_PJ_root/templates/edit.ihtml");
			}
			exit;
		} else {
			if(isset($altered)) {
				if($customer_name != '') {
					$data = array();
					$data['id']							= intval($id);
					$data['active']						= $active;
					$data['customer_name']				= add_slashes($customer_name);
					$data['customer_desc']				= add_slashes($customer_desc);
					$data['customer_desc']				= add_slashes($customer_desc);
					$data['customer_budget']			= add_slashes($customer_budget);
					$data['customer_budget_currency']	= add_slashes($customer_budget_currency);
					// FIX: customer_logo Variable initialisieren falls nicht gesetzt
					$data['customer_logo']				= add_slashes(isset($customer_logo) ? $customer_logo : '');
					$data['user']						= $user;
					$data['gid']						= $gid;
					$data['access']						= $access_owner . $access_group . $access_world;
					$data['readforeignefforts']			= $readforeignefforts;
					if($data['user'] == '') {
						$data['user']	= $customer->giveValue('user');
					}
					if($data['user'] == '') {
						$data['user']	= $_PJ_auth->giveValue('id');
					}
					if($data['gid'] == '') {
						$data['gid']	= $customer->giveValue('gid');
					}
					if($data['access'] == '') {
						$data['access']	= $customer->giveValue('access');
					}
					if($data['readforeignefforts'] == '') {
						$data['readforeignefforts']	= $customer->giveValue('readforeignefforts');
					}
					$customer = new Customer($data,  $_PJ_auth);
					$customer->save();
					
					// Nach erfolgreichem Speichern zur Kundenliste weiterleiten
					echo '<script type="text/javascript">';
					echo 'setTimeout(function() { window.location.href = "' . $GLOBALS['_PJ_customer_inventory_script'] . '?list=1"; }, 1000);';
					echo '</script>';
					echo '<div style="text-align: center; margin: 20px; color: #666;">';
					echo 'Sie werden zur Kundenliste weitergeleitet...';
					echo '</div>';
					exit;
				}
			}
			$center_template	= "inventory/customer";
			$center_title		= $GLOBALS['_PJ_strings']['edit_customer'];
			include("$_PJ_root/templates/edit.ihtml");
		}
		exit;
	}

	if(isset($delete) && !isset($cancel)) {
		if(!$customer->checkUserAccess('write') || (!$_PJ_auth->checkPermission('accountant') && !$GLOBALS['_PJ_agents_allow_delete'])) {
			$error_message		= $GLOBALS['_PJ_strings']['error_access'];
			include("$_PJ_root/templates/error.ihtml");
			include_once("$_PJ_include_path/degestiv.inc.php");
			exit;
		}
		if(isset($confirm)) {
			$customer->delete();
		} else {
			$center_template	= "inventory/customer";
			$center_title		= $GLOBALS['_PJ_strings']['inventory'] . ': ' . $GLOBALS['_PJ_strings']['customer'] . " '" . $customer->giveValue('customer_name') . "' " . $GLOBALS['_PJ_strings']['action_delete'];
			include("$_PJ_root/templates/delete.ihtml");
			exit;
		}
	}

	$customer_list = new CustomerList($_PJ_auth, @$shown['ic']);
	$center_template	= "inventory/customer";
	$center_title		= $GLOBALS['_PJ_strings']['inventory'] . ': ' . $GLOBALS['_PJ_strings']['customer_list'];

	include("$_PJ_root/templates/list.ihtml");
	include_once("$_PJ_include_path/degestiv.inc.php");
?>
