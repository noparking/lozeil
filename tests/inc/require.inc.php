<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2013 */

require_once dirname(__FILE__)."/../../inc/require.inc.php";
require_once dirname(__FILE__)."/../libraries/simpletest/autorun.php";
require_once dirname(__FILE__)."/simpletest_table_tester.php";

session_start();

$GLOBALS['dbconfig']['name'] = "dvlpt_test";

$db = new db();
if (!$db->database_exists($GLOBALS['dbconfig']['name'])) {
	$db->query("CREATE SCHEMA `".$GLOBALS['dbconfig']['name']."`");
	if (!$db->database_exists($GLOBALS['dbconfig']['name'])) {
		echo "<br />"."Access denied"."\n";
		exit();
	}
}

$content = "test.php";
$location = "index.php";
