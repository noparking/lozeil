<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2014 */

require_once dirname(__FILE__)."/../inc/require.inc.php";

class tests_Param_File extends TableTestCase {
	function __construct() {
		parent::__construct();
		$this->initializeTables();
	}

	function test_bool() {
		$param = new Param_File(dirname(__FILE__)."/../../cfg/param.inc.php");
		$key1 = "ext_treasury"; $value1 = "1";
		$key2 = "ext_api"; $value2 = "<h1>xss me</h1>";
		$key3 = "ext_foobar"; $value3 = "1";
		$cleaned1 = $param->clean($key1, $value1);
		$cleaned2 = $param->clean($key2, $value2);
		$cleaned3 = $param->clean($key3, $value3);
		$this->assertEqual($cleaned1, "1");
		$this->assertEqual($cleaned2, "0");
		$this->assertEqual($cleaned3, "1");
	}

	function test_string() {
		$param = new Param_File(dirname(__FILE__)."/../../cfg/param.inc.php");
		$key1 = "email_from"; $value1 = "  j'aime les        espaces      ";
		$key2 = "smtp_host"; $value2 = "<h1>xss me</h1>";
		$cleaned1 = $param->clean($key1, $value1);
		$cleaned2 = $param->clean($key2, $value2);
		$this->assertEqual($cleaned1, "j'aime les espaces");
		$this->assertEqual($cleaned2, "xss me");
	}

	function test_number() {
		$param = new Param_File(dirname(__FILE__)."/../../cfg/param.inc.php");
		$key1 = "threshold"; $value1 = "150";
		$key2 = "email_wrap"; $value2 = 150;
		$key2 = "fisher_threshold"; $value2 = "string <h1>test</h1>";
		$cleaned1 = $param->clean($key1, $value1);
		$cleaned2 = $param->clean($key2, $value2);
		$this->assertEqual($cleaned1, 150);
		$this->assertEqual($cleaned2, 0);
	}
}