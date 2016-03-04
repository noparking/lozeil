<?php
/* Lozeil -- Copyright (C) No Parking 2016 - 2016 */

require_once dirname(__FILE__)."/../inc/require.inc.php";

class tests_User_Authentication extends TableTestCase {
	function __construct() {
		parent::__construct();
		
		$this->initializeTables(
			"users"
		);
	}

	function test_session_headers() {
		$user = new User();
		$user->name = "user";
		$user->username = "admin";
		$user->password = "pass";
		$user->email = "admin@noparking.net";
		$user->save();
		
		$authentication = new User_Authentication($user->id);
		$session_headers = $authentication->session_headers($GLOBALS['dbconfig']);
		$this->assertEqual($session_headers['userid'], $user->id);
		$this->assertEqual($session_headers['name'], $user->name);
		$this->assertEqual($session_headers['username'], $user->username);
		$this->assertEqual($session_headers['user_id'], $user->id);
		$this->assertEqual($session_headers['userdatabase'], $GLOBALS['dbconfig']['name']);
	}
}
