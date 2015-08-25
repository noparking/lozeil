<?php
/* Lozeil -- Copyright (C) No Parking 2014 - 2014 */

require_once dirname(__FILE__)."/../inc/require.inc.php";

class tests_Accounting_Code_Affectation extends TableTestCase {
	function __construct() {
		parent::__construct();
		$this->initializeTables(
			"accountingcodes_affectation"
		);
	}
	
	function teardown() {
		$this->truncateTable("accountingcodes_affectation");
	}
	
	function test_save_load() {
		$affectation = new Accounting_Code_Affectation();
		$affectation->accountingcodes_id = 1;
		$affectation->reportings_id = 1;
		$affectation->save();
		$affectation_loaded = new Accounting_Code_Affectation();
		$affectation_loaded->load(array('id' => 1));
		$this->assertEqual($affectation_loaded->id, $affectation->id);
		$this->teardown();
	}
	
	function test_update() {
		$affectation = new Accounting_Code_Affectation();
		$affectation->accountingcodes_id = 1;
		$affectation->reportings_id = 1;
		$affectation->save();
		$this->assertTrue($affectation->id > 0);
		$affectation->reportings_id = 2;
		$affectation->update();
		$affectation_loaded = new Accounting_Code_Affectation();
		$affectation_loaded->load(array('id' => 1));
		$this->assertEqual($affectation->id, $affectation_loaded->id);
		$this->assertEqual($affectation_loaded->reportings_id, 2);
		$this->teardown();
	}
	
	function test_delete() {
		$affectation = new Accounting_Code_Affectation();
		$affectation->accountingcodes_id = 1;
		$affectation->reportings_id = 1;
		$affectation->save();
		$id = $affectation->id;
		$this->assertTrue($affectation->id > 0);
		$affectation->delete();
		$affectation_loaded = new Accounting_Code_Affectation();
		$affectation_loaded->load(array('id' => $id));
		$this->assertFalse($affectation_loaded->id > 0);
		$this->teardown();
	}
}
