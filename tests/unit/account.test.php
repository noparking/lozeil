<?php
/*
	lozeil
	$Author: adrien $
	$URL: $
	$Revision:  $

	Copyright (C) No Parking 2013 - 2013
*/

require_once dirname(__FILE__)."/../inc/require.inc.php";

class tests_Account extends TableTestCase {
	function __construct() {
		parent::__construct();
		$this->initializeTables(
			"accounts"
		);
	}
	
	function test_save_load() {
		$account = new Account();
		$account->name = "première account";
		$account->save();
		$account_loaded = new Account();
		$account_loaded->id = 1;
		$account_loaded->load();
		$this->assertEqual($account_loaded->name, $account->name);
		$this->truncateTable("accounts");
	}
	
	function test_update() {
		$account = new Account();
		$account->name = "premier account";
		$account->save();
		$account_loaded = new Account();
		$account_loaded->id = 1;
		$account_loaded->name = "changement de nom";
		$account_loaded->update();
		$account_loaded2 = new Account();
		$account_loaded2->id = 1;
		$account_loaded2->load();
		$this->assertNotEqual($account_loaded2->name, $account->name);
		$this->truncateTable("accounts");
	}
	
	function test_delete() {
		$account = new Account();
		$account->name = "premier account";
		$account->save();
		$account_loaded = new Account();
		$this->assertTrue($account_loaded->load(1));
		$account->delete();
		$this->assertFalse($account_loaded->load(1));
	}
}
