<?php
/* Lozeil -- Copyright (C) No Parking 2014 - 2014 */

require_once dirname(__FILE__)."/../inc/require.inc.php";

class tests_Balance_Period extends TableTestCase {
	function __construct() {
		parent::__construct();
		$this->initializeTables(
			"balancesperiod"
		);
	}
	
	function test_save_load() {
		$balance_period = new Balance_Period();
		$balance_period->start = time();
		$balance_period->balance_period_loaded = 42;
		$balance_period->save();
		$balance_period_loaded = new Balance_Period();
		$balance_period_loaded->id = 1;
		$balance_period_loaded->load(array('id' => 1));
		$this->assertEqual($balance_period_loaded->start, $balance_period->start);
		$this->truncateTable("balancesperiod");
	}
	
	function test_update() {
		$balance_period = new Balance_Period();
		$balance_period->start = time();
		$balance_period->save();
		$balance_period_loaded = new Balance_Period();
		$balance_period_loaded->id = 1;
		$balance_period_loaded->hash = time() + 100;
		$balance_period_loaded->update();
		$balance_period_loaded2 = new Balance_Period();
		$this->assertTrue($balance_period_loaded2->load(array('id' => 1)));
		$this->assertNotEqual($balance_period_loaded2->start, $balance_period->start);
		$this->truncateTable("balancesperiod");
	}
	
	function test_delete() {
		$balance_period = new Balance_Period();
		$balance_period->start = time();
		$balance_period->save();
		$balance_period_loaded = new Balance_Period();
		$this->assertTrue($balance_period_loaded->load(array('id' => 1 )));
		$balance_period->delete();
		$this->assertFalse($balance_period_loaded->load(array('id' => 1 )));
		$this->truncateTable("balancesperiod");
	}
}
