<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2014 */

require_once dirname(__FILE__)."/../inc/require.inc.php";

class tests_Balances_Imported extends TableTestCase {
	function __construct() {
		parent::__construct();
		$this->initializeTables(
			"balancesimported"
		);
	}
	
	function test_filter_with() {
		$balances_imported = new Balances_Imported();
		$balances_imported->filter_with(array('id' => 3));
		$this->assertTrue($balances_imported->filters['id'] == 3);
		$this->truncateTables("balancesimported");
	}


	function test_get_where() {
		$balance_imported1 = new Balance_Imported();
		$balance_imported1->hash = base64_encode("hash");
		$balance_imported1->balance_id = 42;
		$balance_imported1->save();

		$balance_imported2 = new Balance_Imported();
		$balance_imported2->hash = sha1("hash");
		$balance_imported2->balance_id = uniqid();
		$balance_imported2->save();

		$balances_imported = new Balances_Imported();
		$balances_imported->filter_with(array('balance_id' => 42));
		$balances_imported->select();

		$this->assertEqual(count($balances_imported), 1);

		$this->truncateTables("balancesimported");
	}

	function test_delete() {
		$balance_imported1 = new Balance_Imported();
		$balance_imported1->hash = base64_encode("hash");
		$balance_imported1->balance_id = 42;
		$balance_imported1->save();

		$balance_imported2 = new Balance_Imported();
		$balance_imported2->hash = sha1("hash");
		$balance_imported2->balance_id = uniqid();
		$balance_imported2->save();

		$balances_imported = new Balances_Imported();
		$balances_imported->select();

		$this->assertEqual(count($balances_imported), 2);

		$balances_imported->delete();		
		$balances_imported->select();

		$this->assertEqual(count($balances_imported), 0);

		$this->truncateTables("balancesimported");
	}
}
