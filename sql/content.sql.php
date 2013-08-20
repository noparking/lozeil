<?php
/*
	lozeil
	$Author: adrien $
	$URL: $
	$Revision:  $

	Copyright (C) No Parking 2013 - 2013
*/

$queries = array(
	'account' => "CREATE TABLE ".$GLOBALS['dbconfig']['table_accounts']." (
		  id int(21) NOT NULL AUTO_INCREMENT,
		  name VARCHAR(100) NOT NULL DEFAULT '',
		  PRIMARY KEY (`id`)
		 ) ENGINE=InnoDB DEFAULT CHARSET=utf8;",
	
	'source' => "CREATE TABLE ".$GLOBALS['dbconfig']['table_sources']." (
		  id int(21) NOT NULL AUTO_INCREMENT,
		  name VARCHAR(100) NOT NULL DEFAULT '',
		  PRIMARY KEY (`id`)
		 ) ENGINE=InnoDB DEFAULT CHARSET=utf8;",
	
	'type' => "CREATE TABLE ".$GLOBALS['dbconfig']['table_types']." (
		  id int(21) NOT NULL AUTO_INCREMENT,
		  name VARCHAR(100) NOT NULL DEFAULT '',
		  PRIMARY KEY (`id`)
		 ) ENGINE=InnoDB DEFAULT CHARSET=utf8;",

	'writings' => "CREATE TABLE ".$GLOBALS['dbconfig']['table_writings']." (
		  id int(21) NOT NULL AUTO_INCREMENT,
		  account_id INT(11),
		  source_id INT(11),
		  amount_inc_tax FLOAT(12,6),
		  type_id INT(11),
		  vat FLOAT(5,2),
		  amount_excl_tax FLOAT(12,6),
		  delay int(10) NOT NULL DEFAULT '0',
		  paid tinyint(1) NOT NULL DEFAULT '0',
		  PRIMARY KEY (`id`),
		  KEY account_id (account_id),
		  KEY source_id (source_id),
		  KEY type_id (type_id)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;",
);