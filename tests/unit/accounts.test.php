<?php
/*
	lozeil
	$Author: adrien $
	$URL: $
	$Revision:  $

	Copyright (C) No Parking 2013 - 2013
*/

require_once dirname(__FILE__)."/../inc/require.inc.php";

class tests_Accounts extends TableTestCase {
	function __construct() {
		parent::__construct();
		$this->initializeTables(
			"accounts"
		);
	}
	
	function test_names() {
		$account = new Account();
		$account->name = "premier account";
		$account->save();
		$account2 = new Account();
		$account2->name = "deuxième account";
		$account2->save();
		$account3 = new Account();
		$account3->name = "troisième account";
		$account3->save();
		
		$accounts = new Accounts();
		$accounts->select();
		$names = $accounts->names();
		$this->assertTrue(in_array("--", $names));
		$this->assertTrue(in_array("premier account", $names));
		$this->assertTrue(in_array("deuxième account", $names));
		$this->assertTrue(in_array("troisième account", $names));
	}
}
