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

	function to_5() {
		$this->dbconfig->add("table_writingssimulations", "writingssimulations");
		$this->db->query("CREATE TABLE `writingssimulations` (
			`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			`name` varchar(100) NOT NULL DEFAULT '',
			`duration` varchar(50) NOT NULL DEFAULT '',
			`periodicity` varchar(50) NOT NULL DEFAULT '',
			`date` int(10) NOT NULL,
			`display` tinyint(1) NOT NULL,
			PRIMARY KEY (`id`)
		  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
		);
		$this->db->query("ALTER TABLE `writings` ADD `simulations_id` INT(11)  NULL  DEFAULT NULL  AFTER `vat`;
"
		);
	}

	function to_4() {
		$this->dbconfig->add("table_users", "users");
		$this->db->query("CREATE TABLE `users` (`id` int(11) NOT NULL AUTO_INCREMENT,`username` varchar(80) NOT NULL DEFAULT '',`password` varchar(50) NOT NULL DEFAULT '',
			PRIMARY KEY (`id`),
			UNIQUE KEY `username` (`username`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
		);
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
