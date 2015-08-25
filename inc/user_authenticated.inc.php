<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2013 */

class User_Authenticated extends User {
	private $exists = false;

	function __construct($id = 0) {
		parent::__construct($id);
		$this->load(array('id' => $id));
	}

	function load(array $key = array(), $table = "users", $columns = null) {
		$this->exists = parent::load($key, $table, $columns);
		return $this->exists();
	}
	
	function has_at_least_access($access) {
		if ($this->is_archived()) {
			return false;
		}

		if ($this->is_root()) {
			return true;
		}
		
		switch ($access) {
			case "a":
				if ($this->is_admin()) {
					return true;
				}
				break;
			case "b":
				if ($this->access == "b") {
					return true;
				}
				break;
			case "a|b":
				if ($this->access == "a" or $this->access == "b") {
					return true;
				}
				break;
			case "":
				return true;
		}
		
		return false;
	}

	function has_access_to_contacts() {
		if ($GLOBALS['param']['ext_contacts'] == "0") {
			return false;
		} else {
			if (isset($GLOBALS['param']['contact_access']) and !empty($GLOBALS['param']['contact_access'])) {
				return preg_match("/".preg_quote($GLOBALS['param']['contact_access'])."/", $this->access); 
			} else {
				return true;
			}
		}
	}
	
	function has_no_access() {
		return $this->access == "no";
	}

	function is_root() {
		return $this->access == "aa";
	}
	
	function is_expert() {
		$option = new User_Option();
		$option->load(array('user_id' => $this->id, 'name' => "viewexpert"));
		if ($option->value == 1) {
			return true;
		} else {
			return false;
		}
	}

	function is_admin() {
		switch ($this->access) {
			case "a":
			case "aa":
				return true;
			default:
				return false;
		}
	}
	
	function can_edit_customer($customer) {
		$user_id = $customer->user_id;
		
		if (!is_array($user_id)) {
			$user_id = unserialize($user_id);
			if ($user_id === false) {
				$user_id = array();
			}
		}

		if ($GLOBALS['param']['level_0_handling'] == 0) {
			return false;
		} elseif ($this->has_at_least_access($GLOBALS['param']['permissions_editcustomer'])) {
			if ($this->is_root() or in_array($this->id, $user_id)) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	} 

	function can_edit($element) {
		switch (true) {
			case $element instanceof Customer:
				return $this->can_edit_customer($element);
			default:
				return false;
		}
	}

	function exists() {
		return $this->exists;
	}
}
