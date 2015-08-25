<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2014 */

require_once dirname(__FILE__)."/../inc/require.inc.php";

class tests_Html_Cubismchart extends UnitTestCase {
	
	function test_average_of_positive_values() {
		$cubismchart = new Html_Cubismchart();
		$cubismchart->data = array(
			mktime(0, 0, 0, 9, 24, 2013) => 120,
			mktime(0, 0, 0, 9, 12, 2013) => 80,
			mktime(0, 0, 0, 9, 15, 2013) => -50,
			mktime(0, 0, 0, 9, 1, 2013) => -30
		);
		$this->assertEqual($cubismchart->average_of_positive_values(), 100);
		$this->assertEqual($cubismchart->average_of_negative_values(), -40);
		
		$cubismchart = new Html_Cubismchart();
		$cubismchart->data = array(
			mktime(0, 0, 0, 9, 11, 2013) => 119.49,
			mktime(0, 0, 0, 9, 10, 2013) => 0,
			mktime(0, 0, 0, 9, 12, 2013) => 80.51,
			mktime(0, 0, 0, 9, 15, 2013) => -50.51,
			mktime(0, 0, 0, 9, 14, 2013) => -0,
			mktime(0, 0, 0, 9, 1, 2013) => -29.49
		);
		$this->assertEqual($cubismchart->average_of_positive_values(), 100);
		$this->assertEqual($cubismchart->average_of_negative_values(), -40);
	}
}
