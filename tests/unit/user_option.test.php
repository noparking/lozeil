<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2016 */

require_once dirname(__FILE__)."/../inc/require.inc.php";

class tests_User_Option extends TableTestCase {
	function __construct() {
		parent::__construct();
		$this->initializeTables(
			"useroptions"
		);
	}
	function test_insert() {
		$this->truncateTable("useroptions");

		$option = new User_Option();
		$option->id = 0;
		$name = uniqid();
		$option->name = $name;
		$value = uniqid();
		$option->value = $value;

		$this->assertTrue($option->insert());

		$this->assertEqual($option->id, 1);
		$this->assertEqual($option->name, $name);
		$this->assertEqual($option->value, $value);
		foreach ($option as $column => $value) {
			$query = "SELECT ".$column." FROM ".$this->db->config['table_useroptions'];
			$this->assertEqual($this->db->value($query), $value);
		}

		$this->truncateTable("useroptions");
	}

	function test_update() {
		$this->truncateTable("useroptions");

		$option = new User_Option();
		$option->id = 0;
		$name = uniqid();
		$option->name = $name;
		$value = uniqid();
		$option->value = $value;

		$option->insert();

		$this->assertEqual($option->id, 1);
		$this->assertEqual($option->name, $name);

		$value = uniqid();
		$option->value = $value;

		$this->assertTrue($option->update());

		$this->assertEqual($option->id, 1);
		$this->assertEqual($option->name, $name);
		$this->assertEqual($option->name, $name);
		$this->assertEqual($option->value, $value);

		foreach ($option as $column => $value) {
			$query = "SELECT ".$column." FROM ".$this->db->config['table_useroptions'];
			$this->assertEqual($this->db->value($query), $value);
		}

		$option->id = 0;
		$this->assertFalse($option->update());
		$this->truncateTable("useroptions");

		$option->id = 1;
		$this->assertFalse($option->update());
		$this->truncateTable("useroptions");
	}

	function test_delete() {
		$this->truncateTable("useroptions");

		$option = new User_Option();
		$option->id = 0;
		$name = uniqid();
		$option->name = $name;

		$option->insert();

		$this->assertEqual($option->id, 1);
		$this->assertEqual($option->name, $name);

		$this->assertTrue($option->delete());

		$this->assertEqual($option->id, 0);
		$this->assertEqual($option->name, $name);

		$this->assertFalse($option->delete());

		$this->truncateTable("useroptions");
	}

	function test_save() {
		$this->truncateTable("useroptions");

		$option = new User_Option();
		$option->id = 0;
		$name = uniqid();
		$option->name = $name;
		$value = uniqid();
		$option->value = $value;

		$option->save();

		$this->assertEqual($option->id, 1);
		$this->assertEqual($option->name, $name);
		$this->assertEqual($option->value, $value);

		$value = uniqid();
		$option->value = $value;

		$this->assertTrue($option->save());

		$this->assertEqual($option->id, 1);
		$this->assertEqual($option->name, $name);
		$this->assertEqual($option->value, $value);

		foreach ($option as $column => $value) {
			$query = "SELECT ".$column." FROM ".$this->db->config['table_useroptions'];
			$this->assertEqual($this->db->value($query), $value);
		}

		$this->truncateTable("useroptions");
	}

	private static function get_db() {
		static $db = null;

		if ($db === null) {
			$db = new db();
		}

		return $db;
	}
}
