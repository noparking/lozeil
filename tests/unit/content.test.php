<?php
/* Lozeil -- Copyright (C) No Parking 2016 - 2016 */

require_once dirname(__FILE__)."/../inc/require.inc.php";

class tests_Content extends TableTestCase {
	function test_is_accessible_unauthorized() {
		$content = new Content();
		$this->assertTrue($content->is_accessible_unauthorized("login.php"));
		$this->assertFalse($content->is_accessible_unauthorized("123456789.php"));
		
		$GLOBALS['array_contents']['unauthorized'][] = "123456789.php";
		$this->assertTrue($content->is_accessible_unauthorized("123456789.php"));
	}
}
