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
		$menu->prepare_navigation("writings.php");
		$area_html = $menu->show();
		$this->assertPattern("/header/", $area_html);
		$this->assertPattern("/".$GLOBALS['config']['layout_mediaserver']."medias\/images\/logo.png/", $area_html);
		$this->assertPattern("/content=writings.php/", $area_html);
		$this->assertPattern("/menu_actions_import/", $area_html);
		$this->assertPattern("/file/", $area_html);
	}
	
	function test_prepare_navigation() {
		$writing = new Writing();
		$writing->day = time() + 500;
		$writing->amount_inc_vat = 250;
		$writing->save();
		
		$writing = new Writing();
		$writing->day = time() - 500;
		$writing->amount_inc_vat = 200;
		$writing->save();
		
		$menu = new Menu_Area();
		$menu->prepare_navigation("");
		$area_html = $menu->show();
		
		$this->assertPattern("/200/", $area_html);
		$this->assertPattern("/content=writings.php/", $area_html);
		$this->assertPattern("/content=sources.php/", $area_html);
		$this->assertPattern("/content=followupwritings.php/", $area_html);
		$this->assertPattern("/content=categories.php/", $area_html);
		$this->assertNoPattern("/450/", $area_html);
		
	}
}
