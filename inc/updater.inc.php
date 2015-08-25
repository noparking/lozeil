<?php
  /* Lozeil -- Copyright (C) No Parking 2014 - 2014 */

class Updater {
	
	public $config;
	public $db;
	function __construct(db $db = null) {
		if ($db === null) {
			$db = new db();
		}
		$this->db = $db;
	}
		
	function last() {
		$last = 0;
		$methods = get_class_methods($this);
		foreach ($methods as $method) {
			if (preg_match( "/^to_[0-9]*$/", $method )) {
				$last = max($last,(int)substr($method, 3));
			}
		}
		return $last;
	}

	function config($key, $value) {
		$values = array('config' => $this->config->values());
		$values['config']['config'][$key] = $value;
		return $this->config->update($values);
	}
  }
