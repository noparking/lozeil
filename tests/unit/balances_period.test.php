<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2014 */

require_once dirname(__FILE__)."/../inc/require.inc.php";

class tests_Balances_Period extends TableTestCase {
	function __construct() {
		parent::__construct();
		$this->initializeTables(
			"balancesperiod"
		);
	}
	
	function test_filter_with() {
		$balances_period = new Balances_Period();
		$balances_period->filter_with(array('id' => 3));
		$this->assertTrue($balances_period->filters['id'] == 3);
		$this->truncateTables("balancesperiod");
	}


	function test_get_where() {
		$balance_period1 = new Balance_Period();
		$balance_period1->start = 42;
		$balance_period1->stop = 84;
		$balance_period1->save();

		$balance_period2 = new Balance_Period();
		$balance_period2->start = time();
		$balance_period2->stop = time();
		$balance_period2->save();

		$balances_period = new Balances_Period();
		$balances_period->filter_with(array('start' => 0, 'stop' => 100));
		$balances_period->select();

		$this->assertEqual(count($balances_period), 1);

		$this->truncateTables("balancesperiod");
	}

	function test_delete() {
		$balance_period1 = new Balance_Period();
		$balance_period1->start = 42;
		$balance_period1->stop = 42;
		$balance_period1->save();

		$balance_period2 = new Balance_Period();
		$balance_period2->start = time();
		$balance_period2->stop = time();
		$balance_period2->save();

		$balances_period = new Balances_Period();
		$balances_period->select();

		$this->assertEqual(count($balances_period), 2);

		$balances_period->delete();
		$balances_period->select();

		$this->assertEqual(count($balances_period), 0);

		$this->truncateTables("balancesperiod");
	}
}
