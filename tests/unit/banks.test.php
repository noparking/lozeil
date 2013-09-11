<?php
/*
	lozeil
	$Author: adrien $
	$URL: $
	$Revision:  $

	Copyright (C) No Parking 2013 - 2013
*/

require_once dirname(__FILE__)."/../inc/require.inc.php";

class tests_Banks extends TableTestCase {
	function __construct() {
		parent::__construct();
		$this->initializeTables(
			"banks"
		);
	}
	
	function test_names() {
		$bank = new Bank();
		$bank->name = "première bank";
		$bank->save();
		$bank2 = new Bank();
		$bank2->name = "deuxième bank";
		$bank2->save();
		$bank3 = new Bank();
		$bank3->name = "troisième bank";
		$bank3->save();
		
		$banks = new Banks();
		$banks->select();
		$names = $banks->names();
		$this->assertTrue(in_array("--", $names));
		$this->assertTrue(in_array("première bank", $names));
		$this->assertTrue(in_array("deuxième bank", $names));
		$this->assertTrue(in_array("troisième bank", $names));
		$this->truncateTable("banks");
	}
	
	function test_get_id_from_name() {
		$bank = new Bank();
		$bank->name = "Bank 1";
		$bank->selected = 1;
		$bank->save();
		
		$bank2 = new Bank();
		$bank2->name = "Bank 2";
		$bank2->selected = 0;
		$bank2->save();
		
		$banks = new Banks();
		$id1 = $banks->get_id_from_name("Bank 1"); 
		$id2 = $banks->get_id_from_name("Bank 2");
		$id3 = $banks->get_id_from_name("Bank 5");
		$this->assertTrue($id1 == 1);
		$this->assertTrue($id2 == 0);
		$this->assertTrue($id3 == 0);
		$this->truncateTable("banks");
	}
}
