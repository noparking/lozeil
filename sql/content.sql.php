<?php
/*
	lozeil
	$Author: adrien $
	$URL: $
	$Revision:  $

	Copyright (C) No Parking 2013 - 2013
*/

$queries = array(
	'accounts' => "CREATE TABLE ".$GLOBALS['dbconfig']['table_accounts']." (
		  id int(21) NOT NULL AUTO_INCREMENT,
		  name VARCHAR(100) NOT NULL DEFAULT '',
		  PRIMARY KEY (`id`)
		 ) ENGINE=InnoDB DEFAULT CHARSET=utf8;",
	
	'sources' => "CREATE TABLE ".$GLOBALS['dbconfig']['table_sources']." (
		  id int(21) NOT NULL AUTO_INCREMENT,
		  name VARCHAR(100) NOT NULL DEFAULT '',
		  PRIMARY KEY (`id`)
		 ) ENGINE=InnoDB DEFAULT CHARSET=utf8;",
	
	'types' => "CREATE TABLE ".$GLOBALS['dbconfig']['table_types']." (
		  id int(21) NOT NULL AUTO_INCREMENT,
		  name VARCHAR(100) NOT NULL DEFAULT '',
		  PRIMARY KEY (`id`)
		 ) ENGINE=InnoDB DEFAULT CHARSET=utf8;",
	
	'banks' => "CREATE TABLE ".$GLOBALS['dbconfig']['table_banks']." (
		  id int(21) NOT NULL AUTO_INCREMENT,
		  name VARCHAR(100) NOT NULL DEFAULT '',
		  PRIMARY KEY (`id`)
		 ) ENGINE=InnoDB DEFAULT CHARSET=utf8;",

	'writings' => "CREATE TABLE ".$GLOBALS['dbconfig']['table_writings']." (
		  id int(21) NOT NULL AUTO_INCREMENT,
		  account_id INT(11),
		  amount_excl_vat DECIMAL(12,6),
		  amount_inc_vat DECIMAL(12,6),
		  bank_id INT(11),
		  comment TEXT NOT NULL,
		  delay int(10) NOT NULL DEFAULT '0',
		  information TEXT NOT NULL,
		  paid tinyint(1) NOT NULL DEFAULT '0',
		  search_index TEXT NOT NULL,
		  source_id INT(11),
		  type_id INT(11),
		  unique_key TEXT,
		  vat DECIMAL(5,2),
		  PRIMARY KEY (`id`),
		  KEY account_id (account_id),
		  KEY source_id (source_id),
		  KEY type_id (type_id)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;",
);