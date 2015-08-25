<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2014 */

require_once dirname(__FILE__)."/../inc/require.inc.php";


class tests_Updater extends UnitTestCase {

	function test_last() {
		$update = new simple_updater();
		$this->assertEqual($update->last(),2);
		$this->assertNotEqual($update->last(),1);
		$update->free();
	}

	function test_config() {
		$update = new simple_updater();
		$this->assertEqual($update->config("name","lozeil.com"),"lozeil.com");
		$update->free();
	}
}

class simple_updater extends Updater {

	public $filename ="";
	public $data ="";

	function __construct(db $db = null) {
		parent::__construct();
		$this->filename = time()."txt";
		$this->data = "\$config['name'] = \"Lozeil\";";
		file_put_contents($this->filename,$this->data);
		$this->config = new Config_File($this->filename, "config");
	}

	function free() {
		unlink($this->filename);
	}
	
	function to_2() {
		return true;
	}

	function to_1() {
		return false;
	}
	
}
