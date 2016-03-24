<?php
/* Lozeil -- Copyright (C) No Parking 2016 - 2016 */

require_once dirname(__FILE__)."/../inc/require.inc.php";

class tests_Theme extends UnitTestCase {
	function test_factory() {
		$theme = Theme::factory("");
		$this->assertTrue($theme instanceof Theme_Default);
		
		$theme = Theme::factory("ajax");
		$this->assertTrue($theme instanceof Theme_Ajax);
		
		$theme = Theme::factory("something-else");
		$this->assertTrue($theme instanceof Theme_Default);
	}
}
