<?php
/* Lozeil -- Copyright (C) No Parking 2016 - 2016 */

require_once dirname(__FILE__)."/../inc/require.inc.php";

class tests_User extends TableTestCase {
	function __construct() {
		parent::__construct();
		$this->initializeTables(
			"users",
			"useroptions"
		);
	}
	
	function test_grid_header() {
		$users = new Users();
		$users->select();
		$grid = $users->grid_header();
		$this->assertEqual(count($grid['header']['cells']), 5);
	}

	function test_grid_body() {
		$user = new User();
		$user->name = "Abdelrhamane";
		$user->username = "Abdel";
		$user->password = "pass";
		$user->email = "abdelrhamane.benhammou@noparking.net";
		$user->save();
		
		$users = new Users();
		$users->select();
		$grid = $users->grid_body();
		$this->assertEqual(count($grid[$user->id]['cells']), 5);
		$this->assertEqual($grid[$user->id]['cells'][4]['class'], "operations");
		
		$this->truncateTable("users");
	}
}
