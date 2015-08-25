<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2014 */

require_once dirname(__FILE__)."/../inc/require.inc.php";

class tests_User extends TableTestCase {
	function __construct() {
		parent::__construct();
		$this->initializeTables(
		       "users",
		       "useroptions"
		);
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
		$this->assertTrue($user_loaded->load(array('id' => 1 )));
		$user->delete();
		$this->assertFalse($user_loaded->load(array('id' => 1 )));
	}

	function test_expert_view() {
		$user = new User();
		$user->name = "abdelrhamane";
		$user->username = "abdel";
		$user->password = "pass";
		$user->email = "abdelrhamane.benhammou@noparking.net";
		$user->save();
		$option = new User_Option();
		$this->assertFalse($user->ismodexpert());
		$user->savemodexpert(true);
		$option = new User_Option();
		$option->load(array("user_id" => $user->id,"name" => "viewexpert"));
		$this->assertTrue($option->id > 0);
		$this->assertTrue($user->ismodexpert());
		$user->savemodexpert(false);
		$this->assertFalse($user->ismodexpert());
		$this->truncateTable("users");
		$this->truncateTable("useroptions");
	}

	function test_show_form () {
		$user = new User();
		$user->name = "abdelrhamane";
		$user->username = "abdel";
		$user->password = "pass";
		$user->email = "abdelrhamane.benhammou@noparking.net";
		$user->save();
		$form = $user->show_form();
		$this->assertPattern("/new_user_name/",$form);
		$this->assertPattern("/new_user_username/",$form);		
		$this->truncateTable("users");
	}

	
	function test_show_form_modification () {
		$user = new User();
		$user->name = "abdelrhamane";
		$user->username = "abdel";
		$user->password = "pass";
		$user->email = "abdelrhamane.benhammou@noparking.net";
		$user->save();
		$form = $user->show_form_modification();
		$this->assertPattern("/abdelrhamane/",$form);
		$this->assertPattern("/abdel/",$form);		
		$this->assertPattern("/action/",$form);
		$this->assertPattern("/".$user->email."/",$form);
		$this->truncateTable("users");
	}
	
}
