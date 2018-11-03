<?php
	if(!isset($_PJ_root)) {
		print "<b>FEHLER:</b> \$_PJ_root ist <b>nicht festgelegt</b>! (" . __FILE__ . ", Zeile: " . __LINE__ . ")";
		exit;
	}

	require_once('Auth/Auth.php');

	class PJAuth extends Auth {
		// global permissions for checking user access rights
		var $permissions;

		// user data (e.g. name, id, personal permissions)
		var $data = array();

		function giveValue($key) {
			return $this->data[$key];
		}

		function checkPermission($permission) {
			if (!isset($this->data['permissions']) ) {
				$this->data['permissions'] = '';
			}
			$pageperm = split(',', $permission);
			$userperm = split(',', $this->data['permissions']);
			list($ok0, $pagebits) = $this->sumPermissions($pageperm);
			list($ok1, $userbits) = $this->sumPermissions($userperm);

			$has_all = (($userbits & $pagebits) == $pagebits);
			if (!($has_all && $ok0 && $ok1) ) {
				return false;
			} else {
				return true;
			}
		}

		function sumPermissions($permissions) {
			if (!is_array($permissions)) {
				return array(false, 0);
			}
			$perms = $this->permissions;

			$r = 0;
			reset($permissions);
			while(list($key, $val) = each($permissions)) {
				if (!isset($perms[$val])) {
					return array(false, 0);
				}
				$r |= $perms[$val];
			}

			return array(true, $r);
		}

		function save($data) {
			if($data['password'] != $GLOBALS['_PJ_password_dummy']) {
				if($data['password'] != $data['password_retype']) {
					return $GLOBALS['_PJ_strings']['error_pw_retype'];
				}
				$password = md5($data['password']);
			} else {
				$password = $this->giveValue('password');
			}
			$query = sprintf("UPDATE %s SET %s = '%s', firstname = '%s', lastname = '%s', email = '%s', telephone = '%s', facsimile = '%s', allow_nc = '%s' WHERE id='%s'",
							 $this->storage->options['table'],
							 $this->storage->options['passwordcol'],
							 $password,
							 $this->giveValue('firstname'),
							 $this->giveValue('lastname'),
							 $data['email'],
							 $data['telephone'],
							 $data['facsimile'],
							 $data['allow_nc'],
							 $data['id']
							 );
			$res = $this->storage->query($query);

			if (DB::isError($res)) {
				$error = PEAR::raiseError("", $res->code, PEAR_ERROR_DIE);
			}
			$this->data['email']		= $data['email'];
			$this->data['telephone']	= $data['telephone'];
			$this->data['facsimile']	= $data['facsimile'];
			return NULL;
		}

		public function PJAuth()
		{
			self::__construct();
		}
		public function __construct() {
			$database	= new Database();
			$dsn		= "mysql://" . $database->User . ":" . $database->Password . "@" . $database->Host . "/" . $database->Database;
			
			$parent = get_parent_class($this);
			$options = array(
				'table'			=> $GLOBALS['_PJ_auth_table'],
				'usernamecol'	=> 'username',
				'passwordcol'	=> 'password',
				'db_fields'		=> '*',
				'dsn'			=> $dsn
			);
			$this->$parent("DB", $options, "PJ_login"); 

			$this->setSessionname('PJ');
			$this->setExpire($GLOBALS['_PJ_session_length']);
			$this->start();
			$this->fetchAdditionalData();
			$this->fetchGIDs();
			$this->loadUserList();
		}

		function fetchAdditionalData() {
			$retVal = array();

			$session = &Auth::_importGlobalVariable("session");
			$query = sprintf("SELECT * FROM `%s` WHERE %s='%s'",
							 $this->storage->options['table'],
							 $this->storage->options['usernamecol'],
							 $this->getUsername()
							 );

			$res = $this->storage->query($query);
			if (DB::isError($res)) {
				return PEAR::raiseError('', $res->code, PEAR_ERROR_TRIGGER);
			} 
			if($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
				$this->data = $row;
				$this->fetchPermissions();
			}
		}

		function fetchPermissions() {
			$query = sprintf("SELECT * FROM `%s`", $GLOBALS['_PJ_group_table']);
			$res = $this->storage->query($query);
			if (DB::isError($res)) {
				return PEAR::raiseError("", $res->code, PEAR_ERROR_DIE);
			} 
			while($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
				$this->permissions[$row['name']] = $row['level'];
			}
		}

		function fetchGIDs() {
			$query = sprintf("SELECT * FROM `%s`", $GLOBALS['_PJ_gid_table']);
			$res = $this->storage->query($query);
			if (DB::isError($res)) {
				return PEAR::raiseError("", $res->code, PEAR_ERROR_DIE);
			} 
			while($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
				$this->gids[$row['id']] = $row['name'];
			}
		}

		static function assembleFormFields($array = NULL, $name_default = NULL, $excludes = NULL) {
			if(!is_array($array)) {
				$array = array();
				if(is_array($_POST)) {
					// put any POSTed content into link string
					$array = $_POST;
				}
				if(is_array($_GET)) {
					$array = array_merge($array, $_GET);
				}
			}

			// if any excludes mentioned
			if(is_array($excludes)) {
				$e_count = count($excludes);
				for($e = 0; $e < $e_count; $e++) {
					unset($array[$excludes[$e]]);
				}
			}

			// iterate through array
			$form_string = '';
			$i_count = 0;
			
			while(list($name, $content) = @each($array)) {
				// if $content is array
				if(is_array($content)) {
					// start recursion
					$form_string .= PJAuth::assembleFormFields($content, $name, $excludes);
				// if $content is scalar
				} else {
					if($name_default) {
						// use $name_default instead of $name
						$form_string .= '<INPUT TYPE="hidden" NAME="' . $name_default . '[' . (++$i_count) . ']" VALUE="' . $content . '">' . "\n";
					} else {
						$form_string .= '<INPUT TYPE="hidden" NAME="' . $name . '" VALUE="' . $content . '">' . "\n";
					}
				}
			}
			// remove last '&'-character from $query_string and return
			return $form_string;
		}

		function giveUserById($id) {
			return $this->user_list[$id];
		}
	
		function loadUserList() {
			$this->user_list = array();
	
			$list = $this->listUsers();
			$l_count = count($list);
			for($i = 0; $i < $l_count; $i++) {
				$this->user_list[$list[$i]['id']] = $list[$i];
			}
		}
	
	} // class PJAuth

	function PJ_login() {
		include($GLOBALS['_PJ_root'] . '/templates/shared/login.ihtml');
		exit;
	}

	if(!empty($no_login)) {
		return;
	}
	$_PJ_auth = new PJAuth();
	if(!$_PJ_auth->getAuth()) {
		exit;
	}

	if ($logout == 1) {
		$_PJ_auth->logout();
		$_PJ_auth->start();
		$_PJ_session_timeout = '';
	} else {
		$session = &Auth::_importGlobalVariable("session"); 
		$_PJ_session_timeout = $session[$_PJ_auth->_sessionName]['timestamp'] - time() + $_PJ_auth->expire;
	}
?>
