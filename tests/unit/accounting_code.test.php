<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2016 */

require_once dirname(__FILE__)."/../inc/require.inc.php";

class tests_Accounting_Code extends TableTestCase {
	function __construct() {
		parent::__construct();
		$this->initializeTables(
			   "accountingcodes",
			   "accountingcodes_affectation",
			   "balances",
			   "reportings"
		);
	}
	
	function teardown() {
		$this->truncateTable("accountingcodes");
		$this->truncateTable("accountingcodes_affectation");
		$this->truncateTable("balances");
		$this->truncateTable("reportings");
	}

	function test_clean() {
		$accountcode = new Accounting_Code();
		$cleaned = $accountcode->clean(array('name' => "456 <h1>456</h2>"));
		$this->assertEqual($cleaned['name'], "456 456");
	}

	function test_fill() {
		$accountingcode = new Accounting_Code();
		$accountingcode->fill(array('name' => "AccountCode"));
		$accountingcode->save();
		$this->assertEqual($accountingcode->name, "AccountCode");
	}
	
	function test_insert() {
		$this->backupTables("accountingcodes");
		
		$accountingcode = new Accounting_Code();
		$accountingcode->number = "411NOPA";
		$accountingcode->name = "No Parking";
		$accountingcode->save();
		$this->assertEqual($accountingcode->number, "411NOPA0");
		$this->assertEqual($accountingcode->id, 1);

		$this->restoreTables();
	}

	function test_save_load() {
		$accountingcode = new Accounting_Code();
		$accountingcode->name = "première accountingcode";
		$accountingcode->save();
		$accountingcode_loaded = new Accounting_Code();
		$accountingcode_loaded->load(array('id' => 1));
		$this->assertEqual($accountingcode_loaded->name, $accountingcode->name);
		$this->truncateTable("accountingcodes");
	}
	
	function test_update() {
		$accountingcode = new Accounting_Code();
		$accountingcode->name = "premier accountingcode";
		$accountingcode->save();
		$accountingcode_loaded = new Accounting_Code();
		$accountingcode_loaded->id = 1;
		$accountingcode_loaded->name = "changement de nom";
		$accountingcode_loaded->update();
		$accountingcode_loaded2 = new Accounting_Code();
		$accountingcode_loaded2->load(array('id' => 1));
		$this->assertNotEqual($accountingcode_loaded2->name, $accountingcode->name);
		$this->truncateTable("accountingcodes");
	}
	
	function test_delete() {
		$accountingcode = new Accounting_Code();
		$accountingcode->name = "premier accountingcode";
		$accountingcode->save();
		$affectation = new Accounting_Code_Affectation();
		$affectation->accountingcodes_id = $accountingcode->id;
		$affectation->reportings_id  = 1;
		$affectation->save();
		$this->assertTrue($affectation->id > 0);
		$accountingcode_loaded = new Accounting_Code();
		$this->assertTrue($accountingcode_loaded->load(array('id' => 1)));
		$affectation_loaded = new Accounting_Code_Affectation();
		$accountingcode->delete();
		$this->assertFalse($affectation_loaded->load(array('id' => $affectation->id)));
		$this->assertFalse($accountingcode_loaded->load(array('id' => 1)));
		$this->truncateTable("accountingcodes");
	}

	function test_adjust_number() {
		$code = new Accounting_Code();

		$number = 60800000000;
		$adjust_number = $code->adjust_number($number);

		$this->assertEqual($adjust_number, 60800000);

		$this->truncateTable("accountingcodes");
	}

	function test_reaffect_by_default() {
		$codes = new Accounting_Codes();
		$reportings = new Reportings();
		$affectations = new Accounting_Codes_Affectation();

		$code = new Accounting_Code();
		$code->number = 60970000;
		$code->name = "Prestation de serveur";
		$code->save();

		$reporting = new Reporting();
		$reporting->norm = "B";
		$reporting->name = "Charges directes";
		$reporting->save();

		$code->reaffect_by_default();

		$codes->select();
		$reportings->select();
		$affectations->select();

		$this->assertEqual(count($codes), 1);
		$this->assertEqual(count($reportings), 1);
		$this->assertEqual(count($affectations), 1);

		$affectation = new Accounting_Code_Affectation();
		$affectation->load(array('accountingcodes_id' => $code->id));

		$this->assertNotEqual($affectation->id, 0);
		$this->assertRecordExists("accountingcodes_affectation", array('reportings_id' => $reporting->id));

		$affectation->reportings_id = $reporting->id + 123;
		$affectation->save();

		$this->assertRecordNotExists("accountingcodes_affectation", array('reportings_id' => $reporting->id));

		$code->reaffect_by_default();

		$this->assertRecordExists("accountingcodes_affectation", array('reportings_id' => $reporting->id));
		
		$this->tearDown();
	}
}
