<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2014 */

require_once dirname(__FILE__)."/../inc/require.inc.php";

class tests_Accounting_Codes_Affectation extends TableTestCase {
	function __construct() {
		parent::__construct();
		$this->initializeTables(
			"accountingcodes_affectation",
			"reportings"
		);
	}
	
	function test_accountingcode_ids() {
		$affectation1 = new Accounting_Code_Affectation();
		$affectation1->accountingcodes_id = 10;
		$affectation1->reportings_id = 5;
		$affectation1->save();

		$affectation2 = new Accounting_Code_Affectation();
		$affectation2->accountingcodes_id = 32;
		$affectation2->reportings_id = 5;
		$affectation2->save();

		$affectations = new Accounting_Codes_Affectation();
		$affectations->select();
		$ids = $affectations->accountingcode_ids();

		$this->assertTrue(in_array(10, $ids));
		$this->assertTrue(in_array(32, $ids));

		$this->truncateTables("accountingcodes_affectation");
	}

	function test_get_where() {
		$affectations = new Accounting_Codes_Affectation();
		$affectations->filter_with(array('accountingcodes_id' => 630, 'reportings_id' => 42));
		$get_where = $affectations->get_where();
		$this->assertPattern("/accountingcodes_affectation.accountingcodes_id = 630/", $get_where[0]);
		$this->assertPattern("/accountingcodes_affectation.reportings_id = 42/", $get_where[1]);

		$affectations2 = new Accounting_Codes_Affectation();
		$get_where2 = $affectations2->get_where();
		$this->assertTrue(!isset($get_where2[0]));
		$this->assertFalse(isset($get_where2[1]));
	}

	function test_filter_with() {
		$affectations = new Accounting_Codes_Affectation();
		$affectations->filter_with(array('accountingcodes_id' => 630));
		$affectations->filter_with(array('reportings_id' => 42));
		$this->assertEqual($affectations->filters['accountingcodes_id'], 630);
		$this->assertEqual($affectations->filters['reportings_id'], 42);
	}

	function test_delete() {
		$affectations = new Accounting_Codes_Affectation();

		$affectation = new Accounting_Code_Affectation();
		$affectation->accountingcodes_id = 30;
		$affectation->save();

		$affectations->select();
		$this->assertEqual(count($affectations), 1);

		$affectations->delete();
		$affectations->select();

		$this->assertEqual(count($affectations), 0);

		$this->truncateTables("accountingcodes_affectation");
	}

	function test_desaffect() {
		$affectations = new Accounting_Codes_Affectation();

		$affectation = new Accounting_Code_Affectation();
		$affectation->accountingcodes_id = 30;
		$affectation->reportings_id = 42;
		$affectation->save();

		$affectations->select();
		$this->assertEqual($affectation->reportings_id, 42);

		$affectations->desaffect();

		$affectation = new Accounting_Code_Affectation();
		$affectation->load(array('accountingcodes_id' => 30));
		$this->assertEqual($affectation->reportings_id, 0);

		$this->truncateTables("accountingcodes_affectation");
	}
}