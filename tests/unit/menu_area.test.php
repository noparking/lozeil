<?php
/*
	lozeil
	$Author: adrien $
	$URL: $
	$Revision:  $

	Copyright (C) No Parking 2013 - 2013
*/

require_once dirname(__FILE__)."/../inc/require.inc.php";

class tests_Menu_area extends TableTestCase {
	function __construct() {
		parent::__construct();
		$this->initializeTables(
			"writings"
		);
	}
	
	function test_show() {
		$menu = new Menu_Area();
		$menu->prepare_navigation("lines.php");
		$area_html = $menu->show();
		$this->assertPattern("/header/", $area_html);
		$this->assertPattern("/".$GLOBALS['config']['layout_mediaserver']."medias\/images\/logo.png/", $area_html);
		$this->assertPattern("/content=lines.php/", $area_html);
		$this->assertPattern("/menu_actions_import/", $area_html);
		$this->assertPattern("/file/", $area_html);
	}
}
