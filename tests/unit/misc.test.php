<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2014 */

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
	
	function test_close_years_in_array() {
		$this->assertFalse(in_array(date('Y') - 3, close_years_in_array()));
		$this->assertTrue(in_array(date('Y') - 2, close_years_in_array()));
		$this->assertTrue(in_array(date('Y') - 1, close_years_in_array()));
		$this->assertTrue(in_array(date('Y'), close_years_in_array()));
		$this->assertTrue(in_array(date('Y') + 1, close_years_in_array()));
		$this->assertTrue(in_array(date('Y') + 2, close_years_in_array()));
		$this->assertTrue(in_array(date('Y') + 3, close_years_in_array()));
		$this->assertTrue(in_array(date('Y') + 4, close_years_in_array()));
		$this->assertFalse(in_array(date('Y') + 5, close_years_in_array()));
		$this->assertTrue(sizeof(close_years_in_array()) == 7);
	}
	
	function test_determine_first_day_of_year() {
		$this->assertEqual(determine_first_day_of_year(mktime(0, 0, 0, 10, 10, 2013)), mktime(0, 0, 0, 1, 1, 2013));
	}
	
	function test_determine_last_day_of_year() {
		$this->assertEqual(determine_last_day_of_year(mktime(0, 0, 0, 10, 10, 2013)), mktime(23, 59, 59, 12, 31, 2013));
	}
	
	function test_determine_vat_date() {
		$date = mktime(0, 0, 0, 10, 22, 2013);
		$this->assertEqual(determine_vat_date($date), mktime(0, 0, 0, 1, 15, 2014));
		$date = mktime(0, 0, 0, 1, 1, 2013);
		$this->assertEqual(determine_vat_date($date), mktime(0, 0, 0, 4, 15, 2013));
		$date = mktime(0, 0, 0, 3, 15, 2013);
		$this->assertEqual(determine_vat_date($date), mktime(0, 0, 0, 7, 15, 2013));
		$date = mktime(0, 0, 0, 6, 1, 2013);
		$this->assertEqual(determine_vat_date($date), mktime(0, 0, 0, 10, 15, 2013));
	}

	function test_number_difference() {
		$this->assertEqual(number_difference(10), "+10");
		$this->assertEqual(number_difference(-10), "-10");
	}
}