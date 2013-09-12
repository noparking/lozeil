<?php
/*
	lozeil
	$Author: adrien $
	$URL: $
	$Revision:  $

	Copyright (C) No Parking 2013 - 2013
*/

require_once dirname(__FILE__)."/../inc/require.inc.php";

class tests_Writings_simulations extends TableTestCase {
	function __construct() {
		parent::__construct();
		$this->initializeTables(
			"writingssimulations"
		);
	}
	
	function test_get_amounts_in_array() {
		$writingssimulation = new Writings_Simulation();
		$writingssimulation->name = "Salarié";
		$writingssimulation->amount_inc_vat = "1";
		$writingssimulation->date_start = mktime(0, 0, 0, 8, 25, 2013);
		$writingssimulation->date_stop = mktime(0, 0, 0, 12, 25, 2013);
		$writingssimulation->periodicity = "m";
		$writingssimulation->display = 1;
		$writingssimulation->save();
		
		$writingssimulation = new Writings_Simulation();
		$writingssimulation->name = "Salarié";
		$writingssimulation->amount_inc_vat = "2";
		$writingssimulation->date_start = mktime(0, 0, 0, 8, 25, 2013);
		$writingssimulation->date_stop = mktime(0, 0, 0, 12, 25, 2013);
		$writingssimulation->periodicity = "M";
		$writingssimulation->display = 1;
		$writingssimulation->save();
		
		$writingssimulation = new Writings_Simulation();
		$writingssimulation->name = "Salarié";
		$writingssimulation->amount_inc_vat = "3";
		$writingssimulation->date_start = mktime(0, 0, 0, 8, 25, 2013);
		$writingssimulation->date_stop = mktime(0, 0, 0, 2, 25, 2014);
		$writingssimulation->periodicity = "t";
		$writingssimulation->display = 1;
		$writingssimulation->save();
		
		$writingssimulation = new Writings_Simulation();
		$writingssimulation->name = "Salarié";
		$writingssimulation->amount_inc_vat = "4";
		$writingssimulation->date_start = mktime(0, 0, 0, 8, 25, 2013);
		$writingssimulation->date_stop = mktime(0, 0, 0, 11, 25, 2013);
		$writingssimulation->periodicity = "T";
		$writingssimulation->display = 1;
		$writingssimulation->save();

		$writingssimulation = new Writings_Simulation();
		$writingssimulation->name = "Salarié";
		$writingssimulation->amount_inc_vat = "5";
		$writingssimulation->date_start = mktime(0, 0, 0, 7, 25, 2013);
		$writingssimulation->date_stop = mktime(0, 0, 0, 1, 25, 2014);
		$writingssimulation->periodicity = "q";
		$writingssimulation->display = 1;
		$writingssimulation->save();
		
		$writingssimulation = new Writings_Simulation();
		$writingssimulation->name = "Salarié";
		$writingssimulation->amount_inc_vat = "6";
		$writingssimulation->date_start = mktime(0, 0, 0, 8, 25, 2013);
		$writingssimulation->date_stop = mktime(0, 0, 0, 2, 25, 2014);
		$writingssimulation->periodicity = "Q";
		$writingssimulation->display = 1;
		$writingssimulation->save();
		
		$writingssimulation = new Writings_Simulation();
		$writingssimulation->name = "Salarié";
		$writingssimulation->amount_inc_vat = "7";
		$writingssimulation->date_start = mktime(0, 0, 0, 8, 25, 2013);
		$writingssimulation->date_stop = mktime(0, 0, 0, 8, 25, 2015);
		$writingssimulation->periodicity = "y";
		$writingssimulation->display = 1;
		$writingssimulation->save();
		
		$writingssimulation = new Writings_Simulation();
		$writingssimulation->name = "Salarié";
		$writingssimulation->amount_inc_vat = "8";
		$writingssimulation->date_start = mktime(0, 0, 0, 8, 25, 2013);
		$writingssimulation->date_stop = mktime(0, 0, 0, 8, 25, 2015);
		$writingssimulation->periodicity = "Y";
		$writingssimulation->display = 1;
		$writingssimulation->save();
		
		$writingssimulation = new Writings_Simulation();
		$writingssimulation->name = "Salarié";
		$writingssimulation->amount_inc_vat = "9";
		$writingssimulation->date_start = mktime(0, 0, 0, 8, 25, 2013);
		$writingssimulation->date_stop = mktime(0, 0, 0, 8, 25, 2015);
		$writingssimulation->periodicity = "a";
		$writingssimulation->display = 1;
		$writingssimulation->save();
		
		$writingssimulation = new Writings_Simulation();
		$writingssimulation->name = "Salarié";
		$writingssimulation->amount_inc_vat = "10";
		$writingssimulation->date_start = mktime(0, 0, 0, 8, 25, 2013);
		$writingssimulation->date_stop = mktime(0, 0, 0, 8, 25, 2015);
		$writingssimulation->periodicity = "A";
		$writingssimulation->display = 1;
		$writingssimulation->save();
		
		$writingssimulations = new Writings_Simulations();
		$writingssimulations->select();
		
		$supposed_array = array(
			mktime(0, 0, 0, 9, 1, 2013) => array(
				0 => '1.000000',
				1 => '2.000000'
			),
			mktime(0, 0, 0, 10, 1, 2013) => array(
				0 => '1.000000',
				1 => '2.000000',
				2 => '5.000000'
			),
			mktime(0, 0, 0, 11, 1, 2013) => array(
				0 => '1.000000',
				1 => '2.000000',
				2 => '3.000000',
				3 => '4.000000',
				4 => '6.000000'
			),
			mktime(0, 0, 0, 12, 1, 2013) => array(
				0 => '1.000000',
				1 => '2.000000'
			),
			mktime(0, 0, 0, 2, 1, 2014) => array(
				0 => '3.000000',
				1 => '6.000000'
			),
			mktime(0, 0, 0, 1, 1, 2014) => array(
				0 => '5.000000'
			),
			mktime(0, 0, 0, 8, 1, 2014) => array(
				0 => '7.000000',
				1 => '8.000000',
				2 => '9.000000',
				3 => '10.000000'
			),
			mktime(0, 0, 0, 8, 1, 2015) => array(
				0 => '7.000000',
				1 => '8.000000',
				2 => '9.000000',
				3 => '10.000000'
			)
		);
		$this->assertIdentical($writingssimulations->get_amounts_in_array(), $supposed_array);
		
		
		$this->truncateTable("writingssimulations");
	}
}
