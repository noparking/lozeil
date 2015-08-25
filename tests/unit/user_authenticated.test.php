<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2014 */

require_once dirname(__FILE__)."/../inc/require.inc.php";

class tests_User_Authenticated extends TableTestCase {
	function __construct() {
		parent::__construct();
		
		$this->initializeTables(
			"users"
		);
	}

	function test__construct() {
		$user = new User();
		$user->name = "admin";
		$user->username = "admin";
		$user->save();
		
		$id = -123;
		$user = new User_Authenticated($id);
		$this->assertEqual($user->id, $id);
		$this->assertFalse($user->exists());
		
		$id = 1;
		$user = new User_Authenticated($id);
		$this->assertEqual($user->id, $id);
		$this->assertTrue($user->exists());
	}
}
