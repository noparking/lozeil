<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2014 */

class Update extends Updater {

	public $param;
	public $dbconfig;
	function __construct(db $db = null) {
		parent::__construct($db);
		$this->config = new Config_File(dirname(__FILE__)."/../cfg/config.inc.php", "config");
		$this->param = new Param_File(dirname(__FILE__)."/../cfg/param.inc.php", "param");
		$this->dbconfig = new Config_File(dirname(__FILE__)."/../cfg/config.inc.php", "dbconfig");
	}

	function to_50() {
		$this->db->query("ALTER TABLE `balances` ADD `split` TINYINT(4) NOT NULL DEFAULT '0';");
		$this->db->query("ALTER TABLE `balances` ADD `parent_id` INT(11) NOT NULL DEFAULT '0';");
	}

	function to_49() {
		$this->db->query("ALTER TABLE `accountingcodes_ratio` RENAME TO `accountingcodes_affectation`;");
		$this->db->query("ALTER TABLE `accountingcodes_affectation` DROP `ratio`;");
		$this->dbconfig->add("table_accountingcodes_affectation", "accountingcodes_affectation");
	}

	function to_48() {
		$this->db->query("ALTER TABLE `balancesperiod` CHANGE `span` `start` INT(11) NOT NULL DEFAULT '0';");
		$this->db->query("ALTER TABLE `balancesperiod` ADD `stop` INT(11) NOT NULL DEFAULT '0' AFTER `start`;");
	}

	function to_47() {
		$this->db->query("ALTER TABLE `balances` ADD `period_id` INT(11) NOT NULL AFTER `accountingcodes_id`;");
	}

	function to_46() {
		$this->dbconfig->add("table_balancesperiod", "balancesperiod");
		$this->db->query("CREATE TABLE IF NOT EXISTS `balancesperiod` (
			`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			`span` int(11) unsigned NOT NULL DEFAULT '0',
			PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;");
	}

	function to_45() {
		$this->db->query("ALTER TABLE `balancesimported` CHANGE `accountingcodes_id` `balance_id` INT(11) NOT NULL DEFAULT 0;");
	}

	function to_44() {
		$this->db->query("ALTER TABLE `balances` MODIFY `amount` DECIMAL(22,3) NOT NULL DEFAULT 0;");
	}

	function to_43() {
		$this->db->query("ALTER TABLE `accountingcodes_ratio` ADD `timestamp` INT(10) NOT NULL DEFAULT 0 AFTER `ratio`;");
	}

	function to_42() {
		$this->db->query("ALTER TABLE `reportings` ADD `timestamp` INT(10) NOT NULL DEFAULT 0 AFTER `contents`;");
	}

	function to_41() {
		$this->db->query("ALTER TABLE `balances` ADD `timestamp` INT(10) NOT NULL DEFAULT 0 AFTER `accountingcodes_id`;");
	}

	function to_40() {
		$this->db->query("ALTER TABLE `activities` ADD `global` TINYINT(1) NOT NULL DEFAULT 0 AFTER `name`;");
	}

	function to_39() {
		$this->db->query("ALTER TABLE `reportings` ADD `norm` VARCHAR(2) NOT NULL AFTER `id`;");
	}

	function to_38() {
		$this->db->query("ALTER TABLE `balances` CHANGE `amount_inc_vat` `amount` DECIMAL(12,6) NOT NULL;");
	}

	function to_37() {
		$this->dbconfig->add("table_balancesimported", "balancesimported");
		$this->db->query("CREATE TABLE IF NOT EXISTS `balancesimported` (
			`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			`hash` varchar(100),
			`accountingcodes_id` int(11) unsigned NOT NULL,
			PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;");
	}

	function to_36() {
		$this->dbconfig->add("table_balances", "balances");
		$this->db->query("CREATE TABLE IF NOT EXISTS `balances` (
			`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			`accountingcodes_id` int(11) unsigned NOT NULL,
			`amount_inc_vat` DECIMAL(12,6) NOT NULL,
			`number` int(11) NOT NULL,
			`name` varchar(100) NOT NULL DEFAULT '',
			`day` int(11) NOT NULL,
			PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;");
	}

	function to_35() {
		$this->db->query("ALTER TABLE `activities` ADD `timestamp` INT(10)  NULL  DEFAULT NULL  AFTER `name`;");
		$this->db->query("ALTER TABLE `users` ADD `timestamp` INT(10)  NULL  DEFAULT NULL  AFTER `email`;");
	}

	function to_34() {
		$this->param->add('ext_treasury', "1");
		$this->param->add('ext_simulation', "1");
		$this->param->add('ext_account_custom_result', "1");
		$this->param->add('ext_api', "1");
	}

	function to_33() {
		$this->param->add('nb default activities',"1");
	}

	function to_32() {
		$this->param->add("fiscal year begin", "01");
	}

	function to_31() {
		$this->db->query("ALTER TABLE `accountingcodes` DROP `reportings_id`;");
		$this->db->query("CREATE TABLE IF NOT EXISTS accountingcodes_ratio (
			`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			`accountingcodes_id` int(11) unsigned NOT NULL,
			`reportings_id` int(11) unsigned NOT NULL,
			`ratio` int(11) NOT NULL DEFAULT '0',
			PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;");
		$this->dbconfig->add("table_accountingcodes_ratio", "accountingcodes_ratio");
	}

	function to_30() {
		$this->db->query("UPDATE `accountingcodes` SET `reportings_id` = '0' WHERE `reportings_id` IS NULL ");
		$this->dbconfig->add("table_activities", "activities");
		$this->db->query("CREATE TABLE IF NOT EXISTS `activities` (
			`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			`name` varchar(100) NOT NULL DEFAULT '',
			PRIMARY KEY (`id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;");
		$this->db->query("ALTER TABLE `reportings` ADD `activities_id` INT  NOT NULL DEFAULT '0';");
		$this->db->query("ALTER TABLE `reportings` ADD `contents` varchar(200) NOT NULL DEFAULT '';");
	}
	
	function to_29() {
		$this->db->query("ALTER TABLE `reportings` ADD `base` INT NOT NULL DEFAULT '0';");
	}

	function to_28() {
		$this->db->query("ALTER TABLE `banks` ADD iban varchar(150) DEFAULT NULL;");
	}
	

	function to_27() {
		$this->dbconfig->add("table_reportings", "reportings");
		$this->db->query("CREATE TABLE IF NOT EXISTS `reportings` (
			`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			`name` varchar(50) NOT NULL,
			`sort` int(11) NOT NULL DEFAULT '0',
			PRIMARY KEY (`id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=29;");
		$this->db->query("ALTER TABLE `accountingcodes` ADD `reportings_id` int(11) NOT NULL  DEFAULT '0' AFTER `number`;");
		$this->db->query("ALTER TABLE `reportings` ADD `reportings_id` INT NOT NULL DEFAULT '0';");
	}

	function to_26() {
		$this->dbconfig->add("table_useroptions", "useroptions");
		$this->db->query("CREATE TABLE `useroptions` (
			`id` bigint(21) unsigned NOT NULL AUTO_INCREMENT,
			`user_id` int(11) NOT NULL,
			`name` mediumtext NOT NULL,
			`value` mediumtext NOT NULL,
			PRIMARY KEY (`id`),
			KEY `user_id` (`user_id`)
		  ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
	}
	
	function to_25() {
		$this->config->add("mysql_password", "password");
		$this->config->add("email_smtp", "");
		$this->dbconfig->add("table_passwordrequests", "passwordrequests");
		$this->db->query("CREATE TABLE `passwordrequests` (
			id INT(11) unsigned NOT NULL AUTO_INCREMENT,
			timestamp INT(10) NOT NULL DEFAULT '0',
			token VARCHAR(32) NOT NULL DEFAULT '',
			completed INT(1) NOT NULL DEFAULT '0',
			user_id INT(11) unsigned NOT NULL DEFAULT '0',
			PRIMARY KEY (`id`)
		  ) DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=MyISAM;");
	}
	
	function to_24() {
		$this->config->add("error_handling", "0");
		$this->config->add("db_profiler", "0");
		$this->config->add("external_plugins", "1");
		$this->param->add("accountant_view", "0");
		$this->param->add("locale_lang", "fr_FR");
		$this->param->add("currency", "&euro;");
	}
	
	function to_23() {
		$this->db->query("ALTER TABLE `users` ADD `name` varchar(250) AFTER `id`;");
		$this->db->query("ALTER TABLE `users` ADD `email` varchar(250) AFTER `password`;");
	}
	
	function to_22() {
		$this->param->add("locale_timezone", "Europe/Paris");
		$this->param->add("email_from", "lozeil@noparking.net");
		$this->param->add("email_wrap", "50");
	}
	
	function to_21() {
		$this->db->query("ALTER TABLE `categories` ADD `vat_category` TINYINT(1)  NOT NULL  DEFAULT 0  AFTER `vat`;");
	}
	
	function to_20() {
		$this->param->add("fisher_threshold", "0.4");
		$this->param->update(
			array(
				'param' => array(
					'param' => array(
						"amount_inc_vat_weight" => "0.3"
					)
				)
			)
		);
	}
	
	function to_19() {
		$this->dbconfig->add("table_files", "files");
		$this->db->query("ALTER TABLE `writings` ADD `attachment` TINYINT(1)  NOT NULL  DEFAULT 0  AFTER `accountingcodes_id`;");
		$this->db->query("CREATE TABLE `files` (
			`id` INT(11) unsigned NOT NULL AUTO_INCREMENT,
			`writings_id` INT(11),
			`hash` VARCHAR(100),
			`value` VARCHAR(255),
			PRIMARY KEY (`id`)
		  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
	}
	
	function to_18() {
		$this->param->add("threshold", 3);
	}
	
	function to_17() {
		$this->param->add("comment_weight", 1);
		$this->param->add("amount_inc_vat_weight", 3);
		$this->dbconfig->add("table_bayesianelements", "bayesianelements");
		$this->db->query("CREATE TABLE `bayesianelements` (
			`id` INT(11) unsigned NOT NULL AUTO_INCREMENT,
			`element` VARCHAR(100),
			`field` VARCHAR(100),
			`table_name` VARCHAR(100),
			`table_id` INT(11),
			`occurrences` INT(11),
			PRIMARY KEY (`id`)
		  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
		);
	}
	
	function to_16() {
		$this->dbconfig->add("table_accountingcodes", "accountingcodes");
		$this->db->query("ALTER TABLE `writings` ADD `accountingcodes_id` INT(11);");
		$this->db->query("CREATE TABLE `accountingcodes` (
			`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			`name` varchar(255),
			`number` varchar(100),
			PRIMARY KEY (`id`)
		  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
		);
	}
	
	function to_15() {
		$this->param->add("nb_max_writings", "100");
	}
	
	function to_14() {
		$this->db->query("ALTER TABLE `writings` DROP unique_key;");
		$this->db->query("CREATE TABLE `writingsimported` (
			`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			`hash` varchar(100),
			`banks_id` int(11),
			`sources_id` int(11),
			PRIMARY KEY (`id`)
		  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
		);
	}
	
	function to_13() {
		$this->db->query("ALTER TABLE `writingssimulations` ADD `evolution` VARCHAR(100);");
	}
	
	function to_12() {
		$this->db->query("ALTER TABLE `writings` CHANGE `number` `number` VARCHAR(100);");
	}
	
	function to_11() {
		$this->db->query("ALTER TABLE `writingssimulations` ADD `timestamp` INT(10)  NULL  DEFAULT NULL  AFTER `display`;");
	}
	
	function to_10() {
		$this->db->query("ALTER TABLE `categories` ADD `vat` DECIMAL(5,2) NOT NULL DEFAULT 0 AFTER `name`;");
	}
	
	function to_9() {
		$this->db->query("ALTER TABLE `writings` ADD `timestamp` INT(10)  NULL  DEFAULT NULL  AFTER `vat`;");
	}
	
	function to_8() {
		$this->db->query("ALTER TABLE `writingssimulations` DROP `duration`;");
		$this->db->query("ALTER TABLE `writingssimulations` ADD `date_start` INT(10)  NOT NULL AFTER `periodicity`;");
		$this->db->query("ALTER TABLE `writingssimulations` ADD `amount_inc_vat` DECIMAL(12,6)  NOT NULL AFTER `name`;");
		$this->db->query("ALTER TABLE `writingssimulations` CHANGE `date` `date_stop` INT(10)  NOT NULL;");
	}
	
	function to_7() {
		$this->db->query("ALTER TABLE `banks` ADD `selected` TINYINT(1)  NOT NULL DEFAULT 0  AFTER `name`;");
	}
	
	function to_6() {
		$this->db->query("DROP TABLE `types`;");
		$this->db->query("ALTER TABLE `writings` CHANGE `types_id` `number` INT(20)  NULL  DEFAULT NULL;");
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
		$this->db->query("ALTER TABLE `writings` ADD `simulations_id` INT(11)  NULL  DEFAULT NULL  AFTER `vat`;");
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
}
