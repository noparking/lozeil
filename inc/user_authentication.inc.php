<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2014 */

class User_Authentication {
	public $user_id;

	function __construct($user_id = 0) {
		$this->user_id = (int)$user_id;
	}
	
	function form_grid() {
		$loginname = new Html_Input("username");
		$password = new Html_Input("password", "", "password");
		$login = new Html_Input("login", utf8_ucfirst(__('login')), "submit");
		
		$grid = array(
			'name' => array(
				'value' => $loginname->item(utf8_ucfirst(__('username'))." :"),
				'class' => "clearform",
			),
			'password' => array(
				'value' => $password->item(utf8_ucfirst(__('password'))." :"),
				'class' => "clearform",
			),
			'submit' => array(
				'class' => "itemsform-submit",
				'value' => $login->input(),
			),
			'password-request' => array(
				'class' => "itemsform-link",
				'value' => Html_Tag::a(link_content("content=passwordrequest.php"), __('request a new password')),
			),
		);
		
		return $grid;
	}
	
	function form() {
		if (isset($GLOBALS['config']['root_url']) and !empty($GLOBALS['config']['root_url'])) {
			$action = rtrim($GLOBALS['config']['root_url'], "/")."/";
			
			if (!empty($_SERVER['QUERY_STRING'])) {
				$action .= "?".$_SERVER['QUERY_STRING'];
			}
		} else {
			$action = "";
		}

		$items = new Html_List(array('leaves' => $this->form_grid(), 'class' => "itemsform itemsform-login"));
		return "<center><form method=\"post\" action=\"".$action."\" name=\"form_login\">".$items->show()."</form></center>";
	}
	
	function form_no_password_request() {
		$grid = $this->form_grid();
		unset($grid['password-request']);
		$items = new Html_List(array('leaves' => $grid, 'class' => "itemsform itemsform-login"));
		return "<form method=\"post\" action=\"\" name=\"form_login\">".$items->show()."</form>";
	}

	function is_authorized($username, $password) {
		$db = new db();
		$is_authorized = false;
		$this->user_id = 0;
		
		if ($db->value("SELECT 1 FROM ".$db->config['table_users']." WHERE username = ".$db->quote($username))) {
			$result = $db->query("
				SELECT id, username
				FROM ".$db->config['table_users']."
				WHERE username = ".$db->quote($username)."
				AND password = password(".$db->quote($password).")"
			);
			$row = $db->fetch_array($result[0]);
	
			if ($row['username'] == $username) {
				$this->user_id = (int)$row['id'];
				$is_authorized = true;
			} else {
				status(__('password'), __("not matching"), -1);
			}
		} else {
			status(__('username'), __("not exisisting"), -1);
		}

		return $is_authorized;
	}
	
	function bypass($username, $dbconfig) {
		$db = new db($dbconfig);
		$is_authorized = false;
		$this->user_id = 0;
		
		$query = "SELECT id, username".
				" FROM ".$db->config['table_users'].
				" WHERE username = ".$db->quote($username);
		$result = $db->query($query);
		$row = $db->fetch_array($result[0]);
		
		if ($row['username'] == $username) {
			$this->user_id = (int)$row['id'];
			$is_authorized = true;
		}
		
		return $is_authorized;
	}
	
	function session_headers($dbparams = "") {
		$session = false;
		if ($this->user_id > 0) {
			$db = new db($dbparams);

			$query = "SELECT ".$db->config['table_users'].".id as userid, ".
			$db->config['table_users'].".name as name, ".
			$db->config['table_users'].".username as username ".
			" FROM ".$db->config['table_users'].
			" WHERE ".$db->config['table_users'].".id = ".$this->user_id.
			" LIMIT 0, 1";
			$result = $db->query($query);

			if ($result[1] == 1) {
				$session = $db->fetch_array($result[0]);
				$session['user_id'] = $session['userid'];
				$session['userdatabase'] = $GLOBALS['dbconfig']['name'];
			}
		}

		return $session;
	}
}
