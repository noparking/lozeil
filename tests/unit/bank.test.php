<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2016 */

require_once dirname(__FILE__)."/../inc/require.inc.php";

class tests_Bank extends TableTestCase {
	function __construct() {
		parent::__construct();
		$this->initializeTables(
			"banks",
			"sources",
			"writings"
		);
	}
	
	function test_ask_before_delete() {
		$bank = new Bank();
		$bank->name = "Via API";
		$bank->save();
	
		$form = $bank->ask_before_delete();
		$this->assertPattern("/bank\[id\]/", $form);
		$this->assertPattern("/value=\"1\"/", $form);
	
		$this->truncateTable("banks");
	}
	
	function test_edit() {
		$bank = new Bank();
		$bank->name = "Via API";
		$bank->save();
	
		$form = $bank->edit();
		$this->assertPattern("/bank\[id\]/", $form);
		$this->assertPattern("/value=\"1\"/", $form);
		$this->assertPattern("/bank\[name\]/", $form);
		$this->assertPattern("/value=\"Via API\"/", $form);
		$this->assertPattern("/bank\[iban\]/", $form);
		$this->assertPattern("/bank\[accountingcodes_id\]/", $form);
		$this->assertPattern("/bank\[selected\]/", $form);
	
		$this->truncateTable("banks");
	}
	
	function test_link_to_delete() {
		$bank = new Bank();
		$this->assertNoPattern("/bank.delete.php/", $bank->link_to_delete());
		$this->assertNoPattern("/id=0/", $bank->link_to_delete());
	
		$bank->name = "Bank 1";
		$bank->save();
		$this->assertPattern("/bank.delete.php/", $bank->link_to_delete());
		$this->assertPattern("/id=".$bank->id."/", $bank->link_to_delete());
	
		$this->truncateTables("banks");
	}
	
	function test_link_to_edit() {
		$bank = new Bank();
		$this->assertPattern("/bank.edit.php/", $bank->link_to_edit());
		$this->assertNoPattern("/id=0/", $bank->link_to_edit());
	
		$bank->name = "Bank 1";
		$bank->save();
		$this->assertPattern("/bank.edit.php/", $bank->link_to_edit());
		$this->assertPattern("/id=".$bank->id."/", $bank->link_to_edit());
	
		$this->truncateTables("banks");
	}
	
	function test_clean() {
		$bank = new Bank();
		$cleaned = $bank->clean(array('name' => "456 <h1>456</h2>", 'iban' => "bank&lt;h1&gt;     "));
		$this->assertEqual($cleaned['name'], "456 456");
		$this->assertEqual($cleaned['iban'], "bank&lt;h1&gt;");
		$this->assertEqual($cleaned['accountingcodes_id'], "0");
	}

	function test_save_load() {
		$bank = new Bank();
		$bank->name = "première bank";
		$bank->accountingcodes_id = 2;
		$bank->save();

		$bank_loaded = new Bank();
		$bank_loaded->load(array('id' => 1));
		$this->assertEqual($bank_loaded->name, $bank->name);
		$this->assertEqual($bank_loaded->accountingcodes_id, $bank->accountingcodes_id);
		
		$this->truncateTable("banks");
	}
	
	function test_update() {
		$bank = new Bank();
		$bank->name = "premier bank";
		$bank->accountingcodes_id = 2;
		$bank->save();
		
		$bank->name = "changement de nom";
		$bank->accountingcodes_id = 3;
		$bank->update();
		
		$bank_loaded = new Bank();
		$bank_loaded->load(array('id' => $bank->id));
		
		$this->assertEqual($bank_loaded->name, $bank->name);
		$this->assertEqual($bank_loaded->accountingcodes_id, $bank->accountingcodes_id);
		
		$this->truncateTable("banks");
	}
	
	function test_delete() {
		$bank = new Bank();
		$bank->name = "premier bank";
		$bank->save();
		
		$bank_loaded = new Bank();
		$this->assertTrue($bank_loaded->load(array('id' => 1)));
		$bank->delete();
		
		$this->assertFalse($bank_loaded->load(array('id' => 1)));
		
		$this->truncateTable("banks");
	}
	
	function test_is_deletable() {
		$bank = new Bank();
		$bank->name = "premier bank";
		$bank->save();
		$this->assertTrue($bank->is_deletable());
		
		$writing = new Writing();
		$writing->banks_id = 1;
		$writing->save();
		$this->assertFalse($bank->is_deletable());
		
		$this->truncateTable("banks");
	}
}
