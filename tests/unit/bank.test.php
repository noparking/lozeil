<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2014
 *  */

require_once dirname(__FILE__)."/../inc/require.inc.php";

class tests_Bank extends TableTestCase {
	function __construct() {
		parent::__construct();
		$this->initializeTables(
			"banks",
			"sources",
			"categories",
			"writings"
		);
	}
	
	function test_clean() {
		$bank = new Bank();
		$cleaned = $bank->clean(array('name' => "456 <h1>456</h2>", 'iban' => "bank&lt;h1&gt;     "));
		$this->assertEqual($cleaned['name'], "456 456");
		$this->assertEqual($cleaned['iban'], "bank&lt;h1&gt;");
	}

	function test_save_load() {
		$bank = new Bank();
		$bank->name = "première bank";
		$bank->save();
		$bank_loaded = new Bank();
		$bank_loaded->load(array('id' => 1));
		$this->assertEqual($bank_loaded->name, $bank->name);
		$this->truncateTable("banks");
	}
	
	function test_update() {
		$bank = new Bank();
		$bank->name = "premier bank";
		$bank->save();
		$bank_loaded = new Bank();
		$bank_loaded->id = 1;
		$bank_loaded->name = "changement de nom";
		$bank_loaded->update();
		$bank_loaded2 = new Bank();
		$bank_loaded2->load(array('id' => 1));
		$this->assertNotEqual($bank_loaded2->name, $bank->name);
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

	function test_form_add() {
		$bank = new Bank();
		$bank->name = "lcl";
		$bank->bankname = "fr2030008055";
		$bank->save();
		$form = $bank->form_add();
		$this->assertPattern("/name_new/",$form);
		$this->assertPattern("/iban_new/",$form);		
		$this->truncateTable("banks");
	}

	function test_show_form_modification () {
		$bank = new Bank();
		$bank->name = "lcl";
		$bank->iban = "fr2030008055";
		$bank->save();
		$form = $bank->show_form_modification();
		$this->assertPattern("/lcl/",$form);
		$this->assertPattern("/fr2030008055/",$form);		
		$this->assertPattern("/action/",$form);
		$this->truncateTable("banks");
	}
}
