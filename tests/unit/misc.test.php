<?php
/*
	lozeil
	$Author: adrien $
	$URL: $
	$Revision:  $

	Copyright (C) No Parking 2013 - 2013
*/

require_once dirname(__FILE__)."/../inc/require.inc.php";

class tests_misc extends TableTestCase {
	
	function test_determine_operation() {
		$this->assertEqual(determine_operation(array()), "");
		$this->assertEqual(determine_operation("string"), "string");
		$this->assertEqual(determine_operation(array("key")), "key");
		$this->assertEqual(determine_operation(array("key", "")), "key");
		$this->assertEqual(determine_operation(array("key", "clé")), "key");
		$this->assertEqual(determine_operation(array("", "clé")), "clé");
	}
	
	function test_link_content() {
		$GLOBALS['config']['link_handling'] = 1;
		$this->assertEqual(link_content("test.php"), $GLOBALS['config']['name']."&test.php");
		$GLOBALS['config']['link_handling'] = 0;
		$this->assertEqual(link_content("test.php"), $GLOBALS['location']."?test.php");
		unset($GLOBALS['location']);
		$this->assertEqual(link_content("test.php"), $_SERVER['SCRIPT_NAME']."?test.php");
		$GLOBALS['location'] = "index.php";
	}
}