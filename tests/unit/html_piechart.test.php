<?php
/* Lozeil -- Copyright (C) No Parking 2014 - 2014 */

require_once dirname(__FILE__)."/../inc/require.inc.php";

class tests_Html_Piechart extends UnitTestCase {
	
	function test_prepare_data() {
		$data = array (
			       "category 1" => 100,
			       "category 2" => 500
			       );
		$bar = new Html_Piechart($data,null,null,null,null);
		$donnees = json_decode($bar->data);
		$this->assertEqual(count($donnees),2);
		$this->assertTrue(isset($donnees[1]));
		$this->assertEqual($donnees[1]->name, "category 2");
		$this->assertEqual($donnees[1]->value, 500);
	}
	
	function test_show() {
		$data = array (
			       "category 1" => 100,
			       "category 2" => 500
			       );
		$next = time();
		$previous = $next +10000;
		$bar = new Html_Piechart($data,$next,$previous,null,"piechart");
		$graph = $bar->show();
		$this->assertPattern("/start=".$next."/",$graph);
		$this->assertPattern("/start=".$previous."/",$graph);
		$this->assertPattern("/scale=piechart/",$graph);
		$this->assertPattern("/category 1\",\"value\":100/",$graph);
		$this->assertPattern("/category 2\",\"value\":500/",$graph);
	}
		

}