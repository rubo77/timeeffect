<?php
	include_once("../include/aperetiv.inc.php");
	if(isset($cid)) {
		$customer 			= new Customer($cid);
	}
	if(isset($pid)) {
		$project = new Project($pid);
	}
	if(isset($eid)) {
		$effort = new Effort($eid);
	}

	$center_template	= "inventory/customer";
	if(isset($new)) {
		$center_title		= $GLOBALS['_PJ_strings']['inventory'] . ': ' . $GLOBALS['_PJ_strings']['new_customer'];
		include("$_PJ_root/templates/add.ihtml");
		exit;
	}

	if(isset($edit)) {
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
							$data[$i]['price']		= $price[$r_keys[$i]];
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
				if ($customer_name != '') {
					$data = array();
					$data['id']							= intval($id);
					$data['active']						= $active;
					$data['customer_name']				= addslashes($customer_name);
					$data['customer_desc']				= addslashes($customer_desc);
					$data['customer_desc']				= addslashes($customer_desc);
					$data['customer_budget']			= addslashes($customer_budget);
					$data['customer_budget_currency']	= addslashes($customer_budget_currency);
					$data['customer_logo']				= addslashes($customer_logo);
					$customer = new Customer($data);
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
		if(isset($confirm)) {
			$customer->delete();
		} else {
			$center_template	= "inventory/customer";
			$center_title		= $GLOBALS['_PJ_strings']['inventory'] . ': ' . $GLOBALS['_PJ_strings']['customer'] . " '" . $customer->giveValue('customer_name') . "' " . $GLOBALS['_PJ_strings']['action_delete'];
			include("$_PJ_root/templates/delete.ihtml");
			exit;
		}
	}

	$customer_list = new CustomerList($shown['ic']);
	$center_template	= "inventory/customer";
	$center_title		= $GLOBALS['_PJ_strings']['inventory'] . ': ' . $GLOBALS['_PJ_strings']['customer_list'];

	include("$_PJ_root/templates/list.ihtml");
	include_once("$_PJ_include_path/degestiv.inc.php");
?>