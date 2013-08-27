<?php
/*
	lozeil
	$Author: adrien $
	$URL: $
	$Revision:  $

	Copyright (C) No Parking 2013 - 2013
*/

require_once dirname(__FILE__)."/../inc/require.inc.php";

class tests_Bank extends TableTestCase {
	function __construct() {
		parent::__construct();
		$this->initializeTables(
			"banks"
		);
	}
	
	function test_save_load() {
		$bank = new Bank();
		$bank->name = "première bank";
		$bank->save();
		$bank_loaded = new Bank();
		$bank_loaded->id = 1;
		$bank_loaded->load();
		$this->assertEqual($bank_loaded->name, $bank->name);
		$this->truncateTable("banks");
	}
	
	function test_update() {
		$bank = new Bank();
		$bank->name = "premier bank";
		$bank->save();
		$bank_loaded = new Bank();
		$bank_loaded->id = 1;
		$bank_loaded->name = "changement de nom";
		$bank_loaded->update();
		$bank_loaded2 = new Bank();
		$bank_loaded2->id = 1;
		$bank_loaded2->load();
		$this->assertNotEqual($bank_loaded2->name, $bank->name);
		$this->truncateTable("banks");
	}
	
	function test_delete() {
		$bank = new Bank();
		$bank->name = "premier bank";
		$bank->save();
		$bank_loaded = new Bank();
		$this->assertTrue($bank_loaded->load(1));
		$bank->delete();
		$this->assertFalse($bank_loaded->load(1));
	}
}
