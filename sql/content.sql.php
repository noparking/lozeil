<?php
/*
	lozeil
	$Author: adrien $
	$URL: $
	$Revision:  $

	Copyright (C) No Parking 2013 - 2013
*/

if (!isset($queries)) {
        $queries = array();
}
$queries += array(
	'writing' => "CREATE TABLE ".$GLOBALS['dbconfig']['table_writing']." (
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
	"INSERT INTO ".$GLOBALS['dbconfig']['table_writing']." VALUES (1, 1, 1, 250, 2, 19.60, 209.030100, 1375110000, 0);",
	"INSERT INTO ".$GLOBALS['dbconfig']['table_writing']." VALUES (2, 2, 2, 190, 1, 5.5, 180.094787, 1375240000, 1);",
	"INSERT INTO ".$GLOBALS['dbconfig']['table_writing']." VALUES (3, 3, 3, 110, 1, 19.60, 91.973244, 1375440000, 0);",
	
	'account' => "CREATE TABLE ".$GLOBALS['dbconfig']['table_account']." (
	  id int(21) NOT NULL AUTO_INCREMENT,
	  name VARCHAR(100) NOT NULL DEFAULT '',
	  PRIMARY KEY (`id`)
	 ) ENGINE=InnoDB DEFAULT CHARSET=utf8;",
	"INSERT INTO ".$GLOBALS['dbconfig']['table_account']." VALUES (1, 'Premier compte');",
	"INSERT INTO ".$GLOBALS['dbconfig']['table_account']." VALUES (2, 'Deuxième compte');",
	"INSERT INTO ".$GLOBALS['dbconfig']['table_account']." VALUES (3, 'Troisième compte');",
	
	
	'source' => "CREATE TABLE ".$GLOBALS['dbconfig']['table_source']." (
	  id int(21) NOT NULL AUTO_INCREMENT,
	  name VARCHAR(100) NOT NULL DEFAULT '',
	  PRIMARY KEY (`id`)
	 ) ENGINE=InnoDB DEFAULT CHARSET=utf8;",
	"INSERT INTO ".$GLOBALS['dbconfig']['table_source']." VALUES (1, 'Première source');",
	"INSERT INTO ".$GLOBALS['dbconfig']['table_source']." VALUES (2, 'Deuxième source');",
	"INSERT INTO ".$GLOBALS['dbconfig']['table_source']." VALUES (3, 'Troisième source');",
	
	
	'type' => "CREATE TABLE ".$GLOBALS['dbconfig']['table_type']." (
	  id int(21) NOT NULL AUTO_INCREMENT,
	  name VARCHAR(100) NOT NULL DEFAULT '',
	  PRIMARY KEY (`id`)
	 ) ENGINE=InnoDB DEFAULT CHARSET=utf8;",
	"INSERT INTO ".$GLOBALS['dbconfig']['table_type']." VALUES (1, 'Premier type');",
	"INSERT INTO ".$GLOBALS['dbconfig']['table_type']." VALUES (2, 'Deuxième type');",
	"INSERT INTO ".$GLOBALS['dbconfig']['table_type']." VALUES (3, 'Troisième type');",
	
);