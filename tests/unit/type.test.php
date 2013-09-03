<?php
/*
	lozeil
	$Author: adrien $
	$URL: $
	$Revision:  $

	Copyright (C) No Parking 2013 - 2013
*/

require_once dirname(__FILE__)."/../inc/require.inc.php";

class tests_Type extends TableTestCase {
	function __construct() {
		parent::__construct();
		$this->initializeTables(
			"types"
		);
	}
	
	function test_save_load() {
		$type = new Type();
		$type->name = "premier type";
		$type->save();
		$type_loaded = new Type();
		$type_loaded->id = 1;
		$type_loaded->load();
		$this->assertEqual($type_loaded->name, $type->name);
		$this->truncateTable("types");
	}
	
	function test_update() {
		$type = new Type();
		$type->name = "premier type";
		$type->save();
		$type_loaded = new Type();
		$type_loaded->id = 1;
		$type_loaded->name = "changement de nom";
		$type_loaded->update();
		$type_loaded2 = new Type();
		$type_loaded2->id = 1;
		$type_loaded2->load();
		$this->assertNotEqual($type_loaded2->name, $type->name);
		$this->truncateTable("types");
	}
	
	function test_delete() {
		$type = new Type();
		$type->name = "premier type";
		$type->save();
		$type_loaded = new Type();
		$this->assertTrue($type_loaded->load(1));
		$type->delete();
		$this->assertFalse($type_loaded->load(1));
	}
	
	function test_is_deletable() {
		$type = new Type();
		$type->id = 1;
		$type->save();
		$this->assertTrue($type->is_deletable());
		$writing = new Writing();
		$writing->types_id = 1;
		$writing->save();
		$this->assertFalse($type->is_deletable());
		$this->assertFalse($type->is_deletable());
		$this->truncateTable("types");
		$this->truncateTable("writings");
	}
}
