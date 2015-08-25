<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2014 */

require_once dirname(__FILE__)."/../inc/require.inc.php";

class tests_Balance_Imported extends TableTestCase {
	function __construct() {
		parent::__construct();
		$this->initializeTables(
			"balancesimported"
		);
	}
	
	function test_save_load() {
		$balance_imported = new Balance_Imported();
		$balance_imported->hash = "321sf5ez431sf35s1df51sd35f";
		$balance_imported->balance_imported_loaded = 42;
		$balance_imported->save();
		$balance_imported_loaded = new Balance_Imported();
		$balance_imported_loaded->id = 1;
		$balance_imported_loaded->load(array('id' => 1));
		$this->assertEqual($balance_imported_loaded->hash, $balance_imported->hash);
		$this->assertEqual($balance_imported_loaded->balance_id, $balance_imported->balance_id);
		$this->truncateTable("balancesimported");
	}
	
	function test_update() {
		$balance_imported = new Balance_Imported();
		$balance_imported->hash = "321sf5ez431sf35s1df51sd35f";
		$balance_imported->balance_id = 42;
		$balance_imported->save();
		$balance_imported_loaded = new Balance_Imported();
		$balance_imported_loaded->id = 1;
		$balance_imported_loaded->hash = "002sf5ez4651ds51sd35f";
		$balance_imported_loaded->balance_id = 4242;
		$balance_imported_loaded->update();
		$balance_imported_loaded2 = new Balance_Imported();
		$this->assertTrue($balance_imported_loaded2->load(array('id' => 1)));
		$this->assertNotEqual($balance_imported_loaded2->hash, $balance_imported->hash);
		$this->truncateTable("balancesimported");
	}
	
	function test_delete() {
		$balance_imported = new Balance_Imported();
		$balance_imported->hash = "65sd65f651sd65f1dsf651sdf";
		$balance_imported->save();
		$balance_imported_loaded = new Balance_Imported();
		$this->assertTrue($balance_imported_loaded->load(array('id' => 1 )));
		$balance_imported->delete();
		$this->assertFalse($balance_imported_loaded->load(array('id' => 1 )));
		$this->truncateTable("balancesimported");
	}
}
