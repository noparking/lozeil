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
		$loginname = new Html_Input("username");
		$password = new Html_Input("password", "", "password");
		$login = new Html_Input("login", utf8_ucfirst(__('login')), "submit");
		
		$html = "<form method=\"post\" action=\"\" name=\"form_login\">";
		
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
}
