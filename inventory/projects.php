<?php
	include_once("../include/aperetiv.inc.php");

	$customer	= new Customer($cid);
	$project	= new Project($pid);

	if(isset($eid)) {
		$effort = new Effort($eid);
	}

	$center_template	= "inventory/project";
	if(isset($new)) {
		$center_title		= $GLOBALS['_PJ_strings']['inventory'] . ': ' . $GLOBALS['_PJ_strings']['new_project'];
		include("$_PJ_root/templates/add.ihtml");
		exit;
	}

	if(isset($edit)) {
		if(isset($altered)) {
			$data = array();
			$data['id']							= $pid;
			$data['customer_id']				= $cid;
			$data['project_name']				= $project_name;
			$data['project_desc']				= $project_desc;
			$data['project_budget']				= $project_budget;
			$data['project_budget_currency']	= $project_budget_currency;
			$data['closed']						= $closed;
			$project = new Project($data);
			$project->save();
			$list = 1;
		} else {
			$project_id 		= $pid;
			$center_title		= $GLOBALS['_PJ_strings']['inventory'] . ': ' . $GLOBALS['_PJ_strings']['edit_project'];
			include("$_PJ_root/templates/edit.ihtml");
			exit;
		}
	}

	if(isset($delete) && !isset($cancel)) {
		if(isset($confirm)) {
			$project->delete();
			$list = 1;
		} else {
			$center_title		= $GLOBALS['_PJ_strings']['inventory'] . ': ' . $GLOBALS['_PJ_strings']['project'] . " '" . $project->giveValue('project_name') . "' " . $GLOBALS['_PJ_strings']['action_delete'];
			include("$_PJ_root/templates/delete.ihtml");
			exit;
		}
	}

	if($expand) {
		$efforts = new EffortList($pid);
	}
	$projects = new ProjectList($cid, $shown['cp']);

	$center_title		= $GLOBALS['_PJ_strings']['inventory'] . ': ' . $GLOBALS['_PJ_strings']['project_list'] . " " . $customer->giveValue('customer_name');
	include("$_PJ_root/templates/list.ihtml");

	include_once("$_PJ_include_path/degestiv.inc.php");
?>