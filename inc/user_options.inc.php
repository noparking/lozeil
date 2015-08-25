<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2013 */

class User_Options extends Collector {
	function __construct(db $db = null) {
		parent::__construct(substr(__CLASS__, 0, -1), "useroptions", $db);
	}
	
	protected function get_where() {
		$where = parent::get_where();

		if (isset($this->user_id)) {
			if (!is_array($this->user_id)) {
				$this->user_id = array($this->user_id);
			}
			$where[] = "user_id IN ".array_2_list($this->user_id);
		}
		if (isset($this->name)) {
			$where[] = "name = ".$this->db->quote($this->name);
		}
		if (isset($this->value)) {
			if (is_array($this->value)) {
				$where[] = "value IN ".array_2_list($this->value, "'");
			} else {
				$where[] = "value = ".$this->db->quote($this->value);
			}
		}
		
		return $where;
	}
	
	function values_per_user_id() {
		$values = array();
		foreach ($this as $option) {
			$values[$option->user_id] = $option->value;
		}
		return $values;
	}
	
	function users_id() {
		$users_id = array();
		foreach ($this as $option) {
			$users_id[$option->user_id] = $option->user_id;
		}
		return $users_id;
	}

	function values() {
		$values = array();
		foreach ($this as $option) {
			$values[$option->value] = $option->value;
		}
		return $values;
	}
}
