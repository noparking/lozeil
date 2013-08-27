<?php
/*
	lozeil
	$Author:  $
	$URL: $
	$Revision:  $

	Copyright (C) No Parking 2013 - 2013
*/

require_once dirname(__FILE__)."/../inc/require.inc.php";

class tests_Html_Input_Date extends UnitTestCase {
	function test___construct() {
		$name = uniqid();
		$day = time();
		
		$field = new Html_Input_Date($name, $day);
		$this->assertEqual($field->value, $day);
		$this->assertEqual($field->name, $name);
		$this->assertEqual($field->id, $name."[d]");
		$this->assertEqual($field->img_width, 14);
		$this->assertEqual($field->img_height, 14);
		$this->assertEqual($field->img_src, "medias/images/link_calendar.gif");
	}
	
	function test_input__avec_disabled() {
		$field = new Html_Input_Date("nom", time());
		$field->disabled ="disabled";
		$this->assertNoPattern("/input-date-calendar/", $field->input());
		$this->assertNoPattern("/<img/", $field->input());
	}
	
	function test_input__avec_property() {
		$field = new Html_Input_Date("nom", time());
		$field->properties = array(
			'data-name' => "data-nom",
		);
		$this->assertPattern("/data-name=\"data-nom\"/", $field->input());
	}
	
	function test_timestamp__apres_1970() {
		$date = new Html_Input_Date("apres", adodb_mktime(0, 0, 0, 2, 1, 1972));
		$this->assertPattern("/value=\"02\"/", $date->input()); 
		$this->assertPattern("/value=\"01\"/", $date->input()); 
		$this->assertPattern("/value=\"1972\"/", $date->input()); 
	}

	function test_timestamp__avant_1970() {
		$date = new Html_Input_Date("avant", adodb_mktime(0, 0, 0, 2, 1, 1962));
		$this->assertPattern("/value=\"02\"/", $date->input()); 
		$this->assertPattern("/value=\"01\"/", $date->input()); 
		$this->assertPattern("/value=\"1962\"/", $date->input()); 
	}
	
	function test_name_day() {
		$date = new Html_Input_Date("nomcomplet", time());
		$this->assertEqual($date->name("day"), "nomcompletday");
		$this->assertEqual($date->name("month"), "nomcompletmonth");
		$this->assertEqual($date->name("year"), "nomcompletyear");
		
		$date = new Html_Input_Date("nom[complet]", time());
		$this->assertEqual($date->name("day"), "nom[complet]day");
		$this->assertEqual($date->name("month"), "nom[complet]month");
		$this->assertEqual($date->name("year"), "nom[complet]year");
	}
	
	function test__construct__avec_layout_mediaserver() {
		$GLOBALS['config']['layout_mediaserver'] = "http://ailleurs.com/";
		$field = new Html_Input_Date("nouvelle-date", time());
		$this->assertEqual($field->img_src, "http://ailleurs.com/medias/images/link_calendar.gif");

		$GLOBALS['config']['layout_mediaserver'] = "";
		$field = new Html_Input_Date("nouvelle-date", time());
		$this->assertEqual($field->img_src, "medias/images/link_calendar.gif");
	}
}
