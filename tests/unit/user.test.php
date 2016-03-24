<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2016 */

require_once dirname(__FILE__)."/../inc/require.inc.php";

class tests_User extends TableTestCase {
	function __construct() {
		parent::__construct();
		$this->initializeTables(
			"users",
			"useroptions"
		);
	}
	
	function test_link_to_delete() {
		$user = new User();
		$this->assertNoPattern("/user.delete.php/", $user->link_to_delete());
		$this->assertNoPattern("/id=0/", $user->link_to_delete());
		
		$user->name = "Perrick";
		$user->username = "perrick";
		$user->save();
		$this->assertPattern("/user.delete.php/", $user->link_to_delete());
		$this->assertPattern("/id=".$user->id."/", $user->link_to_delete());
		
		$this->truncateTables("users");
	}

	function test_link_to_edit() {
		$user = new User();
		$this->assertPattern("/user.edit.php/", $user->link_to_edit());
		$this->assertNoPattern("/id=0/", $user->link_to_edit());
		
		$user->name = "Perrick";
		$user->username = "perrick";
		$user->save();
		$this->assertPattern("/user.edit.php/", $user->link_to_edit());
		$this->assertPattern("/id=".$user->id."/", $user->link_to_edit());
		
		$this->truncateTables("users");
	}
	
	function test_clean() {
		$user = new User();
		$cleaned = $user->clean(array('name' => "456 <h1>456</h2>", 'username' => "user&lt;h1&gt;", 'email' => "   perrick@noparking.net    "));
		$this->assertEqual($cleaned['name'], "456 456");
		$this->assertEqual($cleaned['username'], "user&lt;h1&gt;");
		$this->assertEqual($cleaned['email'], "perrick@noparking.net");
	}

	function test_save_load() {
		$user = new User();
		$user->name = "user";
		$user->username = "admin";
		$user->password = "pass";
		$user->email = "admin@noparking.net";
		$user->save();

		$user_loaded = new User();
		$user_loaded->load(array('id' => 1));
		$this->assertEqual($user_loaded->name, $user->name);
		$this->assertEqual($user_loaded->username, $user->username);
		$this->assertEqual($user_loaded->password, "*196BDEDE2AE4F84CA44C47D54D78478C7E2BD7B7");
		$this->assertEqual($user_loaded->email, $user->email);

		$this->truncateTable("users");
	}
	
	function test_update() {
		$user = new User();
		$user->name = "user";
		$user->username = "admin";
		$user->password = "pass";
		$user->email = "admin@noparking.net";
		$user->save();
		$user_loaded = new User();
		$user_loaded->id = 1;
		$user_loaded->name = "autre user";
		$user_loaded->username = "autre admin";
		$user_loaded->password = "autrepass";
		$user_loaded->email = "autreadmin@noparking.net";
		$user_loaded->update();
		$user_loaded2 = new User();
		$this->assertTrue($user_loaded2->load(array('id' => 1)));
		$this->assertNotEqual($user_loaded2->name, $user->name);
		$this->assertNotEqual($user_loaded2->username, $user->username);
		$this->assertNotEqual($user_loaded2->password, $user->password);
		$this->assertNotEqual($user_loaded2->email, $user->email);

		$this->truncateTable("users");
	}
	
	function test_delete() {
		$user = new User();
		$user->name = "premier user";
		$user->save();
		$user_loaded = new User();
		$this->assertTrue($user_loaded->load(array('id' => 1)));
		$user->delete();
		$this->assertFalse($user_loaded->load(array('id' => 1)));
		
		$this->truncateTable("users");
	}

	function test_ask_before_delete() {
		$user = new User();
		$user->name = "Abdelrhamane";
		$user->username = "Abdel";
		$user->password = "pass";
		$user->email = "abdelrhamane.benhammou@noparking.net";
		$user->save();
		
		$form = $user->ask_before_delete();
		$this->assertPattern("/user\[id\]/", $form);
		$this->assertPattern("/value=\"1\"/", $form);
		
		$this->truncateTable("users");
	}
	
	function test_edit() {
		$user = new User();
		$user->name = "Abdelrhamane";
		$user->username = "Abdel";
		$user->password = "pass";
		$user->email = "abdelrhamane.benhammou@noparking.net";
		$user->save();
		
		$form = $user->edit();
		$this->assertPattern("/user\[id\]/", $form);
		$this->assertPattern("/value=\"1\"/", $form);
		$this->assertPattern("/user\[name\]/", $form);
		$this->assertPattern("/value=\"Abdelrhamane\"/", $form);
		$this->assertPattern("/user\[username\]/", $form);		
		$this->assertPattern("/value=\"Abdel\"/", $form);
		$this->assertPattern("/user\[password\]/", $form);
		$this->assertPattern("/value=\"\"/", $form);
		$this->assertPattern("/user\[email\]/", $form);
		$this->assertPattern("/value=\"abdelrhamane.benhammou@noparking.net\"/", $form);
		
		$this->truncateTable("users");
	}
}
