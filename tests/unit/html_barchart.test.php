<?php
/* Lozeil -- Copyright (C) No Parking 2014 - 2014 */

require_once dirname(__FILE__)."/../inc/require.inc.php";

class tests_Html_Barchart extends UnitTestCase {
	
	function test_prepare_data() {
		$data = array (
			       "01/01" => 100,
			       "01/02" => 200,
			       "01/03" => 300,
			       "01/04" => 400,
			       "01/05" => 500
			       );
		$bar = new Html_Barchart($data,null,null);
		$donnees = json_decode($bar->data);
		$this->assertEqual(count($donnees),5);
		$this->assertTrue(isset($donnees[1]));
		$this->assertEqual($donnees[1]->name, "01/02");
		$this->assertEqual($donnees[1]->value, 200);
		$this->assertEqual($bar->max,500);
	}

	function test_show() {
		$data = array (
			       "01/01" => 100,
			       "01/02" => 200,
			       "01/03" => 300,
			       "01/04" => 400,
			       "01/05" => 500
			       );
		$next = time();
		$previous = $next +10000;
		$bar = new Html_Barchart($data,$next,$previous);
		$graph = $bar->show();
		$this->assertPattern("/start=".$next."/",$graph);
		$this->assertPattern("/start=".$previous."/",$graph);
		$this->assertPattern("/scale=histogram/",$graph);
		$this->assertPattern("/max = 500/",$graph);
		$this->assertPattern("/01\",\"value\":100/",$graph);
		$this->assertPattern("/02\",\"value\":200/",$graph);
		$this->assertPattern("/03\",\"value\":300/",$graph);
		$this->assertPattern("/04\",\"value\":400/",$graph);
		$this->assertPattern("/05\",\"value\":500/",$graph);
	}
		

}