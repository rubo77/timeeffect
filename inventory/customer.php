<?php
	include_once("../include/aperetiv.inc.php");
	include_once($_PJ_include_path . '/scripts.inc.php');

	$customer 	= new Customer(@$cid, $_PJ_auth);

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
						if($id == 'new') {
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
					$data['customer_logo']				= add_slashes($customer_logo);
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
				}
				$center_template	= "inventory/customer/rates";
				$center_title		= $GLOBALS['_PJ_strings']['inventory'] . ': ' . $GLOBALS['_PJ_strings']['edit_rate'];
				include("$_PJ_root/templates/edit.ihtml");
			} else {
				$center_template	= "inventory/customer";
				$center_title		= $GLOBALS['_PJ_strings']['inventory'] . ': ' . $GLOBALS['_PJ_strings']['edit_customer'];
				include("$_PJ_root/templates/edit.ihtml");
			}
			exit;
		}
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
