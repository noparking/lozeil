<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2014
 *  */

require_once dirname(__FILE__)."/../inc/require.inc.php";

class tests_Balance extends TableTestCase {
	function __construct() {
		parent::__construct();
		$this->initializeTables(
			"accountingcodes",
			"accountingcodes_affectation",
			"balances"
		);
	}

	function test_save_load() {
		$balance = new Balance();
		$balance->name = "première balance";
		$balance->save();
		$balance_loaded = new Balance();
		$balance_loaded->load(array('id' => 1));
		$this->assertEqual($balance_loaded->name, $balance->name);
		$this->truncateTable("balances");
	}
	
	function test_update() {
		$balance = new Balance();
		$balance->name = "premier balance";
		$balance->save();
		$balance_loaded = new Balance();
		$balance_loaded->id = 1;
		$balance_loaded->name = "changement de nom";
		$balance_loaded->update();
		$balance_loaded2 = new Balance();
		$balance_loaded2->load(array('id' => 1));
		$this->assertNotEqual($balance_loaded2->name, $balance->name);
		$this->truncateTable("balances");
	}
	
	function test_delete() {
		$balance = new Balance();
		$balance->name = "premier balance";
		$balance->save();
		$balance_loaded = new Balance();
		$this->assertTrue($balance_loaded->load(array('id' => 1)));
		$balance->delete();
		$this->assertFalse($balance_loaded->load(array('id' => 1)));
		$this->truncateTable("balances");
	}

	function test_delete__split() {
		$amounts = array(10);

		$balance = new Balance();
		$balance->number = "60410000";
		$balance->amount = 150;
		$balance->name = "Sous-traitance";
		$balance->period_id = 42;
		$balance->save();

		$balance->split($amounts, "amount");

		$this->assertRecordExists("accountingcodes", array('number' => "96041000", 'name' => "Sous-traitance (split 1)"));
		$this->assertRecordExists("balances", array('number' => "96041000", 'amount' => 10, 'name' => "Sous-traitance (split 1)", 'period_id' => 42));

		$balance->load(array('id' => 2));
		$balance->delete();

		$this->assertRecordNotExists("balances", array('number' => "96041000", 'amount' => 10, 'name' => "Sous-traitance (split 1)", 'period_id' => 42));
		$this->assertRecordNotExists("accountingcodes", array('number' => "96041000", 'name' => "Sous-traitance (split 1)"));

		$this->truncateTables("balances", "accountingcodes_affectation", "accountingcodes");
	}

	function test_split() {
		$amounts = array(10, 20, 30, 40);

		$balance = new Balance();
		$balance->number = "60410000";
		$balance->amount = 150;
		$balance->name = "Sous-traitance";
		$balance->period_id = 42;
		$balance->save();

		$balance->split($amounts, "amount");

		$balances = new Balances();
		$balances->select();

		$this->assertRecordExists("accountingcodes", array('number' => "96041000", 'name' => "Sous-traitance (split 1)"));
		$this->assertRecordExists("accountingcodes", array('number' => "96041001", 'name' => "Sous-traitance (split 2)"));
		$this->assertRecordExists("accountingcodes", array('number' => "96041002", 'name' => "Sous-traitance (split 3)"));
		$this->assertRecordExists("accountingcodes", array('number' => "96041003", 'name' => "Sous-traitance (split 4)"));
		$this->assertRecordExists("balances", array('number' => "96041000", 'amount' => 10, 'name' => "Sous-traitance (split 1)", 'period_id' => 42));
		$this->assertRecordExists("balances", array('number' => "96041001", 'amount' => 20, 'name' => "Sous-traitance (split 2)", 'period_id' => 42));
		$this->assertRecordExists("balances", array('number' => "96041002", 'amount' => 30, 'name' => "Sous-traitance (split 3)", 'period_id' => 42));
		$this->assertRecordExists("balances", array('number' => "96041003", 'amount' => 40, 'name' => "Sous-traitance (split 4)", 'period_id' => 42));
		$this->assertEqual($balance->amount, 50);

		$amounts = array(50);
		$balance->split($amounts, "ratio");

		$this->assertRecordExists("accountingcodes", array('number' => "96041004", 'name' => "Sous-traitance (split 1)"));
		$this->assertRecordExists("balances", array('number' => "96041004", 'amount' => 25, 'name' => "Sous-traitance (split 1)", 'period_id' => 42, 'split' => 1, 'parent_id' => $balance->id));
		$this->assertEqual($balance->amount, 25);

		$this->truncateTables("accountingcodes", "accountingcodes_affectation", "balances");
	}

	function test_merge() {
		$amounts = array(50, 40);

		$balance = new Balance();
		$balance->number = "60410000";
		$balance->amount = 150;
		$balance->name = "Sous-traitance";
		$balance->period_id = 42;
		$balance->save();

		$balance->split($amounts, "ratio");

		$balances = new Balances();
		$balances->select();
		$this->assertEqual(count($balances), 3);

		$code = new Accounting_Code();
		$code->id = 1;
		$code->number = 10000000;
		$code->name = "Accounting Code 1";
		$code->save();

		$balance->load(array('id' => "2"));
		$balance->merge();

		$balances->select();
		$this->assertEqual(count($balances), 1);
		$this->assertEqual($balances[0]->amount, 150);
		$this->assertEqual($balances[0]->number, 60410000);

		$this->truncateTables("accountingcodes", "accountingcodes_affectation", "balances");
	}

	function test_merge__without_existing_code() {
		$amounts = array(50);

		$balance = new Balance();
		$balance->number = "60410000";
		$balance->amount = 150;
		$balance->name = "Sous-traitance";
		$balance->period_id = 42;
		$balance->save();

		$balance->split($amounts, "ratio");

		$balances = new Balances();
		$balances->select();
		$this->assertEqual(count($balances), 2);

		$balance->load(array('id' => "2"));
		$balance->merge("1");

		$balances->select();
		$this->assertEqual(count($balances), 1);
		$this->assertEqual($balances[0]->amount, 150);
		$this->assertEqual($balances[0]->number, 60410000);

		$this->truncateTables("accountingcodes", "accountingcodes_affectation", "balances");
	}

	function test_verify_amounts() {
		$balance = new Balance();
		
		$balance->amount = 150;
		$amounts = array(170);

		$this->assertFalse($balance->verify_amounts($amounts, "amount"));

		$balance->amount = -150;
		$amounts = array(-170);

		$this->assertFalse($balance->verify_amounts($amounts, "amount"));

		$balance->amount = 150;
		$amounts = array(10, 20, 30, 40);

		$this->assertTrue($balance->verify_amounts($amounts, "amount"));
	}

	function test_split_code() {
		$balance1 = new Balance();
		$balance1->name = "Balance 1";
		$balance1->amount = 0;
		$balance1->number = "60670000";
		$balance1->save();

		$code1 = $balance1->split_code($balance1->number);
		$this->assertEqual(96067000, $code1);

		$balance2 = new Balance();
		$balance2->name = "Balance 2";
		$balance2->amount = 0;
		$balance2->number = "60671000";
		$balance2->save();

		$code2 = $balance2->split_code($balance2->number);
		$this->assertEqual(96067100, $code2);

		$balance3 = new Balance();
		$balance3->name = "Balance 3";
		$balance3->amount = 0;
		$balance3->number = "60675120";
		$balance3->save();

		$code3 = $balance3->split_code($balance3->number);
		$this->assertEqual(96067512, $code3);

		$balance4 = new Balance();
		$balance4->name = "Balance 4";
		$balance4->amount = 0;
		$balance4->number = "60675123";
		$balance4->save();

		$code4 = $balance4->split_code($balance4->number);
		$this->assertFalse($code4);

		$this->truncateTables("balances");
	}

	function test_clean() {
		$balance = new Balance();
		$data = array(
			'name' => "    <strong>Balance   </strong>"
		);
		$cleaned_data = $balance->clean($data);

		$this->assertEqual($cleaned_data['name'], "Balance");
	}

	function test_name_already_exists() {
		$balance1 = new Balance();
		$balance1->name = "Balance";

		$this->assertFalse($balance1->name_already_exists());
		$balance1->save();

		$balance2 = new Balance();
		$balance2->name = "Balance";

		$this->assertTrue($balance2->name_already_exists());
		$balance2->save();

		$this->truncateTables("balances");
	}

	function test_is_recently_modified() {
		$balance = new Balance();
		$balance->save();
		$balance->load(array('id' => 1 ));

		$this->assertTrue($balance->is_recently_modified());

		$balance->timestamp = $balance->timestamp - 11;
		$this->assertFalse($balance->is_recently_modified());
		
		$this->truncateTable("balances");
	}
}