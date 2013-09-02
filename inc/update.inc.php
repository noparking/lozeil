<?php
/*
	lozeil
	$Author: $
	$URL: $
	$Revision: $

	Copyright (C) No Parking 2013 - 2013
*/

class Update {
	public $config;
	public $param;
	
	function __construct(db $db = null) {
		if ($db === null) {
			$db = new db();
		}
		$this->db = $db;
		$this->config = new Config_File(dirname(__FILE__)."/../cfg/config.inc.php", "config");
		$this->param = new Config_File(dirname(__FILE__)."/../cfg/param.inc.php", "param");
		$this->dbconfig = new Config_File(dirname(__FILE__)."/../cfg/config.inc.php", "dbconfig");
	}

	function to_3() {
		$this->dbconfig->add("table_categories", "categories");
		$this->db->query("ALTER TABLE `writings` CHANGE `account_id` `categories_id` INT(11)  NULL  DEFAULT NULL;");
		$this->db->query("ALTER TABLE `writings` CHANGE `source_id` `sources_id` INT(11)  NULL  DEFAULT NULL;");
		$this->db->query("ALTER TABLE `writings` CHANGE `type_id` `types_id` INT(11)  NULL  DEFAULT NULL;");
		$this->db->query("ALTER TABLE `writings` CHANGE `bank_id` `banks_id` INT(11)  NULL  DEFAULT NULL;");
		$this->db->query("RENAME TABLE `accounts` TO `categories`;");
	}
	
	function to_2() {
		$this->db->query("ALTER TABLE `writings` CHANGE `delay` `day` INT(10)  NOT NULL DEFAULT '0';");
	}

	function to_1() {
		$this->config->add("version", 0);
	}
	
	function current() {
		$values = $this->config->values();
		return $values['config']['version'];
	}

	function last() {
		$last = 0;
		$methods = get_class_methods($this);
		foreach ($methods as $method) {
			if (preg_match("/^to_[0-9]*$/", $method)) {
				$last = max($last, (int)substr($method, 3));
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
