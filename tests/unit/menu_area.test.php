<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2014 */

require_once dirname(__FILE__)."/../inc/require.inc.php";

class tests_Menu_area extends TableTestCase {
	function __construct() {
		parent::__construct();
		$this->initializeTables(
			"writings",
			"sources",
			"banks"
		);
	}
	
	function test_show() {
		$menu = new Menu_Area();
		$area_html = $menu->show();
		$this->assertPattern("/header/", $area_html);
		$this->assertPattern("/content=writings.php/", $area_html);
		$this->assertPattern("/level_0/", $area_html);
		$this->assertPattern("/level_1/", $area_html);
	}
	
	
}
