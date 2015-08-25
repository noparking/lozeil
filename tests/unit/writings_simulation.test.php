<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2014 */

require_once dirname(__FILE__)."/../inc/require.inc.php";

class tests_Writings_Simulation extends TableTestCase {
	function __construct() {
		parent::__construct();
		$this->initializeTables(
			"writingssimulations"
		);
	}
	
	function test_save_load() {
		$simulation = new Writings_Simulation();
		$simulation->name = "première simulation";
		$simulation->amount_inc_vat = 1500.50;
		$simulation->periodicity = "1m";
		$simulation->date_start = mktime(0, 0, 0, 9, 9, 2013);
		$simulation->date_stop = mktime(0, 0, 0, 9, 9, 2016);
		$simulation->display = 1;
		$simulation->save();
		$simulation_loaded = new Writings_Simulation();
		$simulation_loaded->load(array('id' => 1));
		$this->assertEqual($simulation_loaded->name, $simulation->name);
		$this->assertEqual($simulation_loaded->amount_inc_vat, $simulation->amount_inc_vat);
		$this->assertEqual($simulation_loaded->periodicity, $simulation->periodicity);
		$this->assertEqual($simulation_loaded->date_start, $simulation->date_start);
		$this->assertEqual($simulation_loaded->date_stop, $simulation->date_stop);
		$this->assertEqual($simulation_loaded->display, $simulation->display);
		$this->truncateTable("writingssimulations");
	}
	
	function test_update() {
		$simulation = new Writings_Simulation();
		$simulation->name = "première simulation";
		$simulation->amount_inc_vat = 1500.50;
		$simulation->periodicity = "1m";
		$simulation->date_start = mktime(0, 0, 0, 9, 9, 2013);
		$simulation->date_stop = mktime(0, 0, 0, 9, 9, 2016);
		$simulation->display = 1;
		$simulation->save();
		$simulation_loaded = new Writings_Simulation();
		$simulation_loaded->load(array('id' => 1 ));
		$simulation_loaded->name = "changement de nom";
		$simulation_loaded->amount_inc_vat = 15;
		$simulation_loaded->periodicity = "3m";
		$simulation_loaded->date_start = mktime(0, 0, 0, 9, 9, 2014);
		$simulation_loaded->date_stop = mktime(0, 0, 0, 9, 9, 2017);
		$simulation_loaded->display = 0;
		$simulation_loaded->update();
		$simulation_loaded2 = new Writings_Simulation();
		$simulation_loaded2->load(array('id' => 1 ));
		$this->assertNotEqual($simulation_loaded2->name, $simulation->name);
		$this->assertEqual($simulation_loaded2->amount_inc_vat, 15);
		$this->assertEqual($simulation_loaded2->periodicity, "3m");
		$this->assertEqual($simulation_loaded2->date_start, mktime(0, 0, 0, 9, 9, 2014));
		$this->assertEqual($simulation_loaded2->date_stop, mktime(0, 0, 0, 9, 9, 2017));
		$this->assertEqual($simulation_loaded2->display, 0);
		$this->truncateTable("writingssimulations");
	}
	
	function test_delete() {
		$simulation = new Writings_Simulation();
		$simulation->name = "premier simulation";
		$simulation->save();
		$simulation_loaded = new Writings_Simulation();
		$this->assertTrue($simulation_loaded->load(array('id' => 1 )));
		$simulation->delete();
		$this->assertFalse($simulation_loaded->load(array('id' => 1 )));
	}
	
	function test_is_form_valid() {
		$writingssimulation = new Writings_Simulation();
		$invalid_form = array(
			'name' => 'salarié',
			'amount_inc_vat' => '',
			'date_start' => array(
				'd' => '5',
				'm' => '11',
				'Y' => '2013'
			),
			'date_stop' => array(
				'd' => '5',
				'm' => '11',
				'Y' => '2014'
			),
			'periodicity' => '3m'
		);
		
		$invalid_form2 = array(
			'name' => 'salarié',
			'amount_inc_vat' => '251',
			'date_start' => array(
				'd' => '5',
				'm' => '',
				'Y' => '2013'
			),
			'date_stop' => array(
				'd' => '5',
				'm' => '11',
				'Y' => '2014'
			),
			'periodicity' => '3m'
		);
		
		$invalid_form3 = array(
			'name' => 'salarié',
			'amount_inc_vat' => '120',
			'date_start' => array(
				'd' => '5',
				'm' => '11',
				'Y' => '2013'
			),
			'date_stop' => array(
				'd' => '5',
				'm' => '11',
				'Y' => ''
			),
			'periodicity' => '3m'
		);
		
		$invalid_form4 = array(
			'name' => 'salarié',
			'amount_inc_vat' => '250',
			'date_start' => array(
				'd' => '5',
				'm' => '11',
				'Y' => '2013'
			),
			'date_stop' => array(
				'd' => '5',
				'm' => '11',
				'Y' => '2014'
			),
			'periodicity' => ''
		);
		
		
		$valid_form = array(
			'name' => 'salarié',
			'amount_inc_vat' => '250',
			'date_start' => array(
				'd' => '5',
				'm' => '11',
				'Y' => '2013'
			),
			'date_stop' => array(
				'd' => '5',
				'm' => '11',
				'Y' => '2014'
			),
			'periodicity' => '3Y'
		);
		
		$this->assertFalse($writingssimulation->is_form_valid($invalid_form));
		$this->assertFalse($writingssimulation->is_form_valid($invalid_form2));
		$this->assertFalse($writingssimulation->is_form_valid($invalid_form3));
		$this->assertTrue($writingssimulation->is_form_valid($invalid_form4));
		$this->assertTrue($writingssimulation->is_form_valid($valid_form));
	}
}
