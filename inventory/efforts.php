<?php
	include_once("../include/aperetiv.inc.php");

	$effort = new Effort($eid);
	if($pid == '') {
		if(is_object($effort)) {
			$pid = $effort->giveValue('project_id');
		} else {
			exit;
		}
	}
	$project = new Project($pid);

	if($cid == '') {
		if(is_object($project)) {
			$cid = $project->giveValue('customer_id');
		} else {
			exit;
		}
	}
	$customer = new Customer($cid);
	$center_template	= "inventory/effort";

	if(isset($new)) {
		$center_title		= $GLOBALS['_PJ_strings']['inventory'] . ': ' . $GLOBALS['_PJ_strings']['new_effort'];
		include("$_PJ_root/templates/add.ihtml");
		exit;
	}

	if(isset($edit)) {
		if(isset($altered)) {
			if(($hours > 0) || ($minutes > 0)) {
				$data = array();
				$data['id']				= $eid;
				$data['project_id']		= $pid;
				$data['date']			= "$year-$month-$day";
				$data['begin']			= "$hour:$minute:$second";
				$data['description']	= $description;
				$data['note']			= $note;
				$data['rate']			= $rate;
				$data['user']			= $user;
				if(date("Y", strtotime("$billing_day/$billing_month/$billing_year")) > 1970) {
					$data['billed']			= "'$billing_year-$billing_month-$billing_day'";
				} else {
					$data['billed']			= "NULL";
				}
	
				$new_effort = new Effort($data);
				$new_effort->setEndTime("$hours:$minutes");
				$new_effort->save();
				$list = 1;
			}
		} else {
			$center_title		= $GLOBALS['_PJ_strings']['inventory'] . ': ' . $GLOBALS['_PJ_strings']['edit_effort'];
			include("$_PJ_root/templates/edit.ihtml");
			exit;
		}
	}

	if(isset($detail)) {
		$center_title		= $GLOBALS['_PJ_strings']['inventory'] . ': ' . $effort->giveValue('description');
		include("$_PJ_root/templates/note.ihtml");
		exit;
	}

	if(isset($pdf)) {
		$efforts = new EffortList($pid, $shown['be'], $cid);
		include("$_PJ_root/templates/effort/pdf.ihtml");
		exit;
	}

	if(isset($delete) && !isset($cancel)) {
		if(isset($confirm)) {
			$effort->delete();
			unset($effort);
			$list = 1;
		} else {
			$center_title		= $GLOBALS['_PJ_strings']['inventory'] . ': ' . $GLOBALS['_PJ_strings']['effort'] . " '" . $effort->giveValue('description') . "' " . $GLOBALS['_PJ_strings']['action_delete'];
			include("$_PJ_root/templates/delete.ihtml");
			exit;
		}
	}

	$efforts			= new EffortList($pid, $shown['be'], $cid);
	$center_title		= $GLOBALS['_PJ_strings']['inventory'] . ': ' . $GLOBALS['_PJ_strings']['effort_list'] . " " . $project->giveValue('project_name');
	include("$_PJ_root/templates/list.ihtml");

	include_once("$_PJ_include_path/degestiv.inc.php");
?>