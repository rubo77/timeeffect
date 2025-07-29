<?php
    require_once(__DIR__ . "/../bootstrap.php"); // Modern PHP 8.4 compatibility
	include_once("../include/config.inc.php");
	include_once($_PJ_include_path . '/scripts.inc.php');

	$eid = $_REQUEST['eid'] ?? null;
	$stop = $_REQUEST['stop'] ?? null;
	$stop_all = $_REQUEST['stop_all'] ?? null;
	
	// Handle stop_all BEFORE any output to avoid header issues
	if(!empty($stop_all)) {
		// Get all open efforts for the current user
		$open_efforts = new OpenEfforts($_PJ_auth);
		$stopped_count = 0;
		
		if($open_efforts->effortCount() > 0) {
			$open_efforts->reset();
			while($open_efforts->nextEffort()) {
				$open_effort = $open_efforts->giveEffort();
				if($open_effort->checkUserAccess('write')) {
					$open_effort->stop();
					$stopped_count++;
				}
			}
		}
		
		// Show success message or redirect
		$success_message = "Es wurden $stopped_count Aktivitäten gestoppt.";
		// Redirect to customer list after stopping all
		header("Location: " . $GLOBALS['_PJ_customer_inventory_script'] . "?message=" . urlencode($success_message));
		exit;
	}
	$pid = $_REQUEST['pid'] ?? null;
	$cid = $_REQUEST['cid'] ?? null;
	$cont = $_REQUEST['cont'] ?? null;
	$new = $_REQUEST['new'] ?? null;
	$edit = $_REQUEST['edit'] ?? null;
	$altered = $_REQUEST['altered'] ?? null;
	$year = $_REQUEST['year'] ?? null;
	$month = $_REQUEST['month'] ?? null;
	$day = $_REQUEST['day'] ?? null;
	$hour = $_REQUEST['hour'] ?? null;
	$minute = $_REQUEST['minute'] ?? null;
	$second = $_REQUEST['second'] ?? null;
	$description = $_REQUEST['description'] ?? null;
	$note = $_REQUEST['note'] ?? null;
	$selected_cid = $_REQUEST['selected_cid'] ?? null;
	$selected_pid = $_REQUEST['selected_pid'] ?? null;
	$rate = $_REQUEST['rate'] ?? null;
	$user = $_REQUEST['user'] ?? null;
	$gid = $_REQUEST['gid'] ?? null;
	$access_owner = $_REQUEST['access_owner'] ?? null;
	$access_group = $_REQUEST['access_group'] ?? null;
	$access_world = $_REQUEST['access_world'] ?? null;
	$billing_day = $_REQUEST['billing_day'] ?? null;
	$billing_month = $_REQUEST['billing_month'] ?? null;
	$billing_year = $_REQUEST['billing_year'] ?? null;
	$hours = $_REQUEST['hours'] ?? null;
	$minutes = $_REQUEST['minutes'] ?? null;
	$detail = $_REQUEST['detail'] ?? null;
	$pdf = $_REQUEST['pdf'] ?? null;
	$delete = $_REQUEST['delete'] ?? null;
	$cancel = $_REQUEST['cancel'] ?? null;
	$confirm = $_REQUEST['confirm'] ?? null;
	$shown = $_REQUEST['shown'] ?? array();
	$list = $_REQUEST['list'] ?? null;

	// AJAX endpoint for getting projects by customer
	if(isset($_GET['get_projects']) && isset($_GET['customer_id'])) {
		$customer_id = intval($_GET['customer_id']);
		$projects = array();
		
		if($customer_id > 0) {
			$customer_for_ajax = new Customer($_PJ_auth, $customer_id);
			$project_list = new ProjectList($customer_for_ajax, $_PJ_auth);
			while($project_list->nextProject()) {
				$project = $project_list->giveProject();
				$projects[] = array(
					'id' => $project->giveValue('id'),
					'name' => $project->giveValue('project_name')
				);
			}
		}
		
		header('Content-Type: application/json');
		echo json_encode($projects);
		exit;
	}

	// Only create Effort object if valid eid is provided
	$effort = $eid ? new Effort($eid, $_PJ_auth) : null;
	if(!empty($stop)) {
		if($eid && !$effort->checkUserAccess('write')) {
			$error_message		= $GLOBALS['_PJ_strings']['error_access'];
			include("$_PJ_root/templates/error.ihtml");
			include_once("$_PJ_include_path/degestiv.inc.php");
			exit;
		}
		$effort->stop();
	}
	if($pid == '') {
		if(isset($effort) && is_object($effort)) {
			$pid = $effort->giveValue('project_id');
		} elseif(!isset($new)) {
			// Only exit if we're not creating a new effort
			exit;
		}
	}
	
	// Only create project object if we have a valid pid
	$project = null;
	if($pid) {
		$project = new Project($customer, $_PJ_auth, $pid);
	}

	if($cid == '') {
		if(isset($project) && is_object($project)) {
			$cid = $project->giveValue('customer_id');
		} elseif(!isset($new)) {
			// Only exit if we're not creating a new effort
			exit;
		}
	}
	
	// Only create customer object if we have a valid cid
	$customer = null;
	if($cid) {
		$customer = new Customer($cid, $_PJ_auth);
	}
	$center_template	= "inventory/effort";

	if(!empty($cont)) {
		if($eid && !$project->checkUserAccess('new')) {
			$error_message		= $GLOBALS['_PJ_strings']['error_access'];
			include("$_PJ_root/templates/error.ihtml");
			include_once("$_PJ_include_path/degestiv.inc.php");
			exit;
		}
		$new_effort = $effort->copy($_PJ_auth);
		$new_effort->save();
	}

	if(isset($new)) {
		$center_title		= $GLOBALS['_PJ_strings']['inventory'] . ': ' . $GLOBALS['_PJ_strings']['new_effort'];
		include("$_PJ_root/templates/add.ihtml");
		exit;
	}

	if(isset($edit)) {
		if($eid && !$effort->checkUserAccess('write')) {
			$error_message		= $GLOBALS['_PJ_strings']['error_access'];
			include("$_PJ_root/templates/error.ihtml");
			include_once("$_PJ_include_path/degestiv.inc.php");
			exit;
		}
		if(isset($altered)) {
			// last_description mod by Ruben Barkow -- START
			$_SESSION['last_description'] = $description;
			// last_description mod by Ruben Barkow -- END

			$data = array();
			$data['id']				= $eid;
			
			// Auto-assignment logic for customer and project
			$final_pid = $pid;
			$final_cid = $cid;
			
			// Use selected values from form if available
			if(!empty($selected_pid)) {
				$final_pid = $selected_pid;
			} elseif(!empty($selected_cid)) {
				$final_cid = $selected_cid;
				// If customer is selected but no project, we'll use the customer's first project if available
			}
			
			// Auto-assignment based on description if no project is selected
			$cleaned_description = $description;
			if(empty($final_pid) && !empty($description)) {
				// Check if description starts with 'k' followed by customer ID
				if(preg_match('/^k(\d+)\s*/', $description, $matches)) {
					$auto_cid = intval($matches[1]);
					$test_customer = new Customer($_PJ_auth, $auto_cid);
					if($test_customer->giveValue('id')) {
						$final_cid = $auto_cid;
						// Remove the shortcode from the description
						$cleaned_description = preg_replace('/^k\d+\s*/', '', $description);
					}
				}
				// Check if description starts with 'p' followed by project ID
				elseif(preg_match('/^p(\d+)\s*/', $description, $matches)) {
					$auto_pid = intval($matches[1]);
					// Verify project exists and user has access
					$test_project = new Project(null, $_PJ_auth, $auto_pid);
					if($test_project->giveValue('id')) {
						$final_pid = $auto_pid;
						$final_cid = $test_project->giveValue('customer_id');
						// Remove the shortcode from the description
						$cleaned_description = preg_replace('/^p\d+\s*/', '', $description);
					}
				}
				// Check if customer name appears in description
				else {
					$customer_list = new CustomerList($_PJ_auth);
					while($customer_list->nextCustomer()) {
						$customer_check = $customer_list->giveCustomer();
						$customer_name = $customer_check->giveValue('customer_name');
						if(!empty($customer_name) && stripos($description, $customer_name) !== false) {
							$final_cid = $customer_check->giveValue('id');
							break;
						}
					}
				}
			}
			
			// If we have a customer but no project, try to get the first available project
			if(!empty($final_cid) && empty($final_pid)) {
				$customer_for_project = new Customer($_PJ_auth, $final_cid);
				$project_list = new ProjectList($customer_for_project, $_PJ_auth);
				if($project_list->nextProject()) {
					$first_project = $project_list->giveProject();
					$final_pid = $first_project->giveValue('id');
				}
			}
			
			// Update global variables with final values
			$pid = $final_pid;
			$cid = $final_cid;
			
			// Recreate customer and project objects with updated values
			if($cid) {
				$customer = new Customer($_PJ_auth, $cid);
			}
			if($pid) {
				$project = new Project($customer, $_PJ_auth, $pid);
			}
			
			$data['project_id']		= $final_pid;
			$data['date']			= "$year-$month-$day";
			$data['begin']			= sprintf('%02d:%02d:%02d', intval($hour), intval($minute), intval($second));
			$data['description']	= add_slashes($cleaned_description);
			$data['note']			= add_slashes($note);
			$data['rate']			= $rate;
			$data['user']			= $user;
			$data['gid']			= $gid;
			$data['access']			= $access_owner . $access_group . $access_world;
			// LOG_EFFORT_SAVE: Set defaults for empty fields
			if($data['user'] == '') {
				if ($effort && $effort->giveValue('user')) {
					// Use existing effort's user
					$data['user'] = $effort->giveValue('user');
					error_log("LOG_EFFORT_SAVE: Using existing effort user: " . $data['user']);
				} else {
					// Use current user for new efforts
					$data['user'] = $_PJ_auth->giveValue('id');
					error_log("LOG_EFFORT_SAVE: Using current user for new effort: " . $data['user']);
				}
			}
			if($data['user'] == '') {
				$data['user']	= $_PJ_auth->giveValue('id');
			}
			if($data['gid'] == '') {
				if ($effort && $effort->giveValue('gid')) {
					// Use existing effort's gid
					$data['gid'] = $effort->giveValue('gid');
					error_log("LOG_EFFORT_SAVE: Using existing effort gid: " . $data['gid']);
				} else {
					// Use user's default gid for new efforts
					$data['gid'] = $_PJ_auth->giveValue('gid');
					error_log("LOG_EFFORT_SAVE: Using user default gid for new effort: " . $data['gid']);
				}
			}
			if($data['access'] == '') {
				if ($effort && $effort->giveValue('access')) {
					// Use existing effort's access
					$data['access'] = $effort->giveValue('access');
					error_log("LOG_EFFORT_SAVE: Using existing effort access: " . $data['access']);
				} else {
					// Use default access for new efforts (owner: read/write, group: read, world: none)
					$data['access'] = 'rw-r-----';
					error_log("LOG_EFFORT_SAVE: Using default access for new effort: " . $data['access']);
				}
			}
			if(date("Y", strtotime("$billing_day/$billing_month/$billing_year")) > 1970) {
				$data['billed']			= "'$billing_year-$billing_month-$billing_day'";
			} else {
				$data['billed']			= "NULL";
			}
	
			$new_effort = new Effort($data, $_PJ_auth);
			$new_effort->setEndTime("$hours:$minutes");
			$message = $new_effort->save();
			if($message != '') {
				if(!$new_effort->giveValue('id')) {
					$center_title		= $GLOBALS['_PJ_strings']['inventory'] . ': ' . $GLOBALS['_PJ_strings']['new_effort'];
					include("$_PJ_root/templates/add.ihtml");
				} else {
					$center_title		= $GLOBALS['_PJ_strings']['inventory'] . ': ' . $GLOBALS['_PJ_strings']['edit_effort'];
					include("$_PJ_root/templates/edit.ihtml");
				}
				include_once("$_PJ_include_path/degestiv.inc.php");
				exit;
			}
			$list = 1;
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
		$efforts = new EffortList($customer, $project, $_PJ_auth, $shown['be']);
		include("$_PJ_root/templates/effort/pdf.ihtml");
		exit;
	}

	if(isset($delete) && !isset($cancel)) {
		if(!$effort->checkUserAccess('write') || (!$_PJ_auth->checkPermission('accountant') && !$GLOBALS['_PJ_agents_allow_delete'])) {
			$error_message		= $GLOBALS['_PJ_strings']['error_access'];
			include("$_PJ_root/templates/error.ihtml");
			include_once("$_PJ_include_path/degestiv.inc.php");
			exit;
		}
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

	// LOG_PROJECT_ACCESS: Check project object before accessing checkUserAccess method
	if($pid && $project && !$project->checkUserAccess('read')) {
		error_log("LOG_PROJECT_ACCESS: Access denied for project $pid by user " . $_PJ_auth->giveValue('id'));
		$error_message		= $GLOBALS['_PJ_strings']['error_access'];
		include("$_PJ_root/templates/error.ihtml");
		include_once("$_PJ_include_path/degestiv.inc.php");
		exit;
	} elseif ($pid && !$project) {
		error_log("LOG_PROJECT_ACCESS: No project object available for pid=$pid, allowing access");
	}
	$sort_order = $_GET['sort'] ?? 'desc';
	$efforts			= new EffortList($customer, $project, $_PJ_auth, isset($shown['be']) ? $shown['be'] : false, NULL, $sort_order);
	// LOG_TITLE_GENERATION: Set appropriate title based on project context
	if ($project && $project->giveValue('project_name')) {
		// Single project view
		error_log("LOG_TITLE_GENERATION: Generating title for single project: " . $project->giveValue('project_name'));
		$center_title = $GLOBALS['_PJ_strings']['inventory'] . ': ' . $GLOBALS['_PJ_strings']['effort_list'] . " " . $project->giveValue('project_name');
	} elseif ($customer && $customer->giveValue('customer_name')) {
		// Customer-specific efforts view
		error_log("LOG_TITLE_GENERATION: Generating title for customer efforts: " . $customer->giveValue('customer_name'));
		$center_title = $GLOBALS['_PJ_strings']['inventory'] . ': ' . $GLOBALS['_PJ_strings']['effort_list'] . " " . $customer->giveValue('customer_name');
	} else {
		// All efforts view
		error_log("LOG_TITLE_GENERATION: Generating title for all efforts view");
		$center_title = $GLOBALS['_PJ_strings']['inventory'] . ': ' . $GLOBALS['_PJ_strings']['effort_list'];
	}
	include("$_PJ_root/templates/list.ihtml");

	include_once("$_PJ_include_path/degestiv.inc.php");
?>
