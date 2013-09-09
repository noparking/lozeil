<?php
/*
	lozeil
	$Author: adrien $
	$URL: $
	$Revision:  $

	Copyright (C) No Parking 2013 - 2013
*/

$queries = array(
	'categories' => "CREATE TABLE ".$GLOBALS['dbconfig']['table_categories']." (
		  id int(21) NOT NULL AUTO_INCREMENT,
		  name VARCHAR(100) NOT NULL DEFAULT '',
		  PRIMARY KEY (`id`)
		 ) ENGINE=InnoDB DEFAULT CHARSET=utf8;",
	
	'sources' => "CREATE TABLE ".$GLOBALS['dbconfig']['table_sources']." (
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
		  categories_id INT(11),
		  amount_excl_vat DECIMAL(12,6),
		  amount_inc_vat DECIMAL(12,6),
		  banks_id INT(11),
		  comment TEXT NOT NULL,
		  day int(10) NOT NULL DEFAULT '0',
		  information TEXT NOT NULL,
		  paid tinyint(1) NOT NULL DEFAULT '0',
		  search_index TEXT NOT NULL,
		  sources_id INT(11),
		  simulations_id int(11),
		  number INT(20),
		  unique_key TEXT,
		  vat DECIMAL(5,2),
		  PRIMARY KEY (`id`),
		  KEY categories_id (categories_id),
		  KEY sources_id (sources_id),
		  KEY simulations_id (simulations_id)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;",
	
	'users' => "CREATE TABLE ".$GLOBALS['dbconfig']['table_users']." (
		  id INT(11) NOT NULL AUTO_INCREMENT,
		  username VARCHAR(80) NOT NULL DEFAULT '',
		  password VARCHAR(50) NOT NULL DEFAULT '',
		  PRIMARY KEY (`id`),
		  UNIQUE KEY username (username)
		 ) ENGINE=InnoDB DEFAULT CHARSET=utf8;",
	
	'writingssimulations' => "CREATE TABLE ".$GLOBALS['dbconfig']['table_writingssimulations']." (
		`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		`name` varchar(100) NOT NULL DEFAULT '',
		`duration` varchar(50) NOT NULL DEFAULT '',
		`periodicity` varchar(50) NOT NULL DEFAULT '',
		`date` int(10) NOT NULL,
		`display` tinyint(1) NOT NULL,
		PRIMARY KEY (`id`)
	  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
);