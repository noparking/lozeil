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
		$area = new Menu_Area("Test de header");
		$area_html = $area->show();
		$this->assertPattern("/Test de header/", $area_html);
		$this->assertPattern("/".$GLOBALS['config']['layout_mediaserver']."medias\/images\/logo.png/", $area_html);
		$this->assertPattern("/summary/", $area_html);
		$this->assertPattern("/content=lines.php/", $area_html);
		$this->assertPattern("/import_writings/", $area_html);
		$this->assertPattern("/file/", $area_html);
	}
}
