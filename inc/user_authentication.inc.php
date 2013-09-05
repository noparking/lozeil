<?php
/*
	lozeil
	$Author: $
	$URL: $
	$Revision: $

	Copyright (C) No Parking 2013 - 2013
*/

class User_Authentication {
	public $user_id;

	function __construct($user_id=0) {
		$this->user_id = (int)$user_id;
	}
	
	function form() {
		$loginname = new Html_Input("loginname");
		$password = new Html_Input("password", "", "password");
		$login = new Html_Input("login", utf8_ucfirst(__('login')), "submit");
		
		$html = "";
		
		$html .= "<form method=\"post\" action=\"\" name=\"form_login\" id=\"form_login\">";
		
		$list = array(
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
		);
		
		$items = new Html_List(array('leaves' => $list, 'class' => "itemsform itemsform-login"));
		$html .= $items->show();
		
		$html .= "</form>";
		
		return $html;
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
			$row = $db->fetchArray($result[0]);
	
			if ($row['username'] == $username) {
				$this->user_id = (int)$row['id'];
				$is_authorized = true;
			} else {
				error_status(__('password')." -> ".__("not matching"), 1);
			}
		} else {
			error_status(__('username')." -> ".__("not exisisting"), 1);
		}

		return $is_authorized;
	}
	
	function bypass($username, $dbconfig) {
		$db = new db($dbconfig);
		$is_authorized = false;
		$this->user_id = 0;
		
		$query = "SELECT id, username".
				" FROM ".$db->config['table_user'].
				" WHERE username = ".$db->quote($username);
		$result = $db->query($query);
		$row = $db->fetchArray($result[0]);
		
		if ($row['username'] == $username) {
			$this->user_id = (int)$row['id'];
			$is_authorized = true;
		}
		
		return $is_authorized;
	}

	function has_access($ip="") {
		$db = new db();
		$has_access = false;

		if ($this->user_id > 0) {
			$query = "SELECT ".$db->config['table_managementuser'].".access, ".
			$db->config['table_user'].".accept".
			" FROM ".$db->config['table_user'].
			" INNER JOIN ".$db->config['table_managementuser'].
			" ON ".$db->config['table_managementuser'].".user_id = ".$db->config['table_user'].".id".
			" WHERE user_id = ".$this->user_id;
			$result = $db->query($query);
			$row = $db->fetchArray($result[0]);

			@list($network,$mask) = split("/", $row['accept']);

			if ($row['access'] != "no" and compare_ip($network, $mask, $ip)) {
				$has_access = true;
			}
		}

		return $has_access;
	}

	function match($parameters) {
		$user = new User(0);
		foreach ($parameters as $parameter => $value) {
			$user->$parameter = $value;
		}
		$user->match_existing(array_keys($parameters));
		$this->user_id = (int)$user->id;

		return $this->user_id;
	}

	function session_headers($dbparams = "") {
		$session = false;
		if ($this->user_id > 0) {
			$db = new db($dbparams);

			$query = "SELECT ".$db->config['table_user'].".id as userid, ".
			$db->config['table_user'].".name as name, ".
			$db->config['table_user'].".username as username, ".
			$db->config['table_managementuser'].".access as useraccess".
			" FROM ".$db->config['table_user'].", ".
			$db->config['table_managementuser'].
			" WHERE ".$db->config['table_user'].".id = '".$this->user_id."'".
			" AND ".$db->config['table_user'].".id = ".$db->config['table_managementuser'].".user_id".
			" LIMIT 0, 1";
			$result = $db->query($query);

			if ($result[1] == 1) {
				$session = $db->fetchArray($result[0]);
				$session['user_id'] = $session['userid'];
				$session['userurl'] = $GLOBALS['config']['root_url'];
				$session['userdatabase'] = $GLOBALS['dbconfig']['name'];
			}
		}

		return $session;
	}
	
	function set_cookies() {
		$user = new User($this->user_id);
		$user->load();
		if (!empty($user->username)) {
			$username = $user->username;
		} else {
			$username = uniqid();
		}
		$key = hash_hmac("sha256", $username, uniqid());
		$user_cookie = new User_Option();
		$user_cookie->user_id = $this->user_id;
		$user_cookie->name = "cookie";
		$user_cookie->value = $key;
		
		if (!$user_cookie->match_existing(array('name' => "cookie", 'user_id' => $user_cookie->user_id))) {
			$user_cookie->insert();
		} else {
			$user_cookie->update();
		}
		
		setcookie("id", utf8_encrypt($user_cookie->user_id."---".$_SERVER['REMOTE_ADDR']), time() + 7*24*3600);
		setcookie("key", $key, time() + 7*24*3600);
		
		return $this;
	}
	
	function unset_cookies() {
		if(isset($_COOKIE['id'])) {
			setcookie("id", "", time() - 3600);
		}
		if(isset($_COOKIE['key'])) {
			setcookie("key", "", time() - 3600);
		}
	}
	
	function is_authorized_from_encrypted_values($user_id_encrypted = "", $value_encrypted = "") {
		$values = explode("---", utf8_decrypt($user_id_encrypted));
		$option = new User_Option();
		$option->user_id = $values[0];
		$option->name = "cookie";
		$option->load();
		if ($value_encrypted === $option->value && isset($values[1]) && $values[1] === $_SERVER['REMOTE_ADDR']) {
			$this->user_id = $option->user_id;
			return true;
		} else {
			$this->user_id = 0;
			return false;
		}
	}
}
