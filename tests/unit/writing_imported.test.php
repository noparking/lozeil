<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2014 */

require_once dirname(__FILE__)."/../inc/require.inc.php";

class tests_Writing_Imported extends TableTestCase {
	function __construct() {
		parent::__construct();
		$this->initializeTables(
			"writingsimported"
		);
	}
	
	function test_save_load() {
		$writing_imported = new Writing_Imported();
		$writing_imported->hash = "321sf5ez431sf35s1df51sd35f";
		$writing_imported->banks_id = 1;
		$writing_imported->sources_id = 2;
		$writing_imported->save();
		$writing_imported_loaded = new Writing_Imported();
		$writing_imported_loaded->id = 1;
		$writing_imported_loaded->load(array('id' => 1));
		$this->assertEqual($writing_imported_loaded->hash, $writing_imported->hash);
		$this->assertEqual($writing_imported_loaded->banks_id, $writing_imported->banks_id);
		$this->assertEqual($writing_imported_loaded->sources_id, $writing_imported->sources_id);
		$this->truncateTable("writingsimported");
	}
	
	function test_update() {
		$writing_imported = new Writing_Imported();
		$writing_imported->hash = "321sf5ez431sf35s1df51sd35f";
		$writing_imported->banks_id = 2;
		$writing_imported->sources_id = 3;
		$writing_imported->save();
		$writing_imported_loaded = new Writing_Imported();
		$writing_imported_loaded->id = 1;
		$writing_imported_loaded->hash = "002sf5ez4651ds51sd35f";
		$writing_imported_loaded->banks_id = 4;
		$writing_imported_loaded->sources_id = 5;
		$writing_imported_loaded->update();
		$writing_imported_loaded2 = new Writing_Imported();
		$this->assertTrue($writing_imported_loaded2->load(array('id' => 1)));
		$this->assertNotEqual($writing_imported_loaded2->hash, $writing_imported->hash);
		$this->truncateTable("writingsimported");
	}
	
	function test_delete() {
		$writing_imported = new Writing_Imported();
		$writing_imported->hash = "65sd65f651sd65f1dsf651sdf";
		$writing_imported->save();
		$writing_imported_loaded = new Writing_Imported();
		$this->assertTrue($writing_imported_loaded->load(array('id' => 1 )));
		$writing_imported->delete();
		$this->assertFalse($writing_imported_loaded->load(array('id' => 1 )));
		$this->truncateTable("writingsimported");
	}
}
