<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2014 */

require_once dirname(__FILE__)."/../inc/require.inc.php";

class tests_Source extends TableTestCase {
	function __construct() {
		parent::__construct();
		$this->initializeTables(
			"sources",
			"writings"
		);
	}
	
	function test_clean() {
		$source = new Source();
		$cleaned = $source->clean(array('name' => "456 <h1>456</h2>            "));
		$this->assertEqual($cleaned['name'], "456 456");
	}

	function test_save_load() {
		$source = new Source();
		$source->name = "première source";
		$source->save();
		$source_loaded = new Source();
		$source_loaded->load(array('id' => 1));
		$this->assertEqual($source_loaded->name, $source->name);
		$this->truncateTable("sources");
	}
	
	function test_update() {
		$source = new Source();
		$source->name = "premier source";
		$source->save();
		$source_loaded = new Source();
		$source_loaded->id = 1;
		$source_loaded->name = "changement de nom";
		$source_loaded->update();
		$source_loaded2 = new Source();
		$source_loaded2->load(array('id' => 1));
		$this->assertNotEqual($source_loaded2->name, $source->name);
		$this->truncateTable("sources");
	}
	
	function test_delete() {
		$source = new Source();
		$source->name = "premier source";
		$source->save();
		$source_loaded = new Source();
		$this->assertTrue($source_loaded->load(array('id' => 1 )));
		$source->delete();
		$this->assertFalse($source_loaded->load(array('id' => 1 )));
	}
	
	function test_is_deletable() {
		$source = new Source();
		$source->id = 1;
		$source->save();
		$this->assertTrue($source->is_deletable());
		$writing = new Writing();
		$writing->sources_id = 1;
		$writing->save();
		$this->assertFalse($source->is_deletable());
		$this->assertFalse($source->is_deletable());
		$this->truncateTable("sources");
		$this->truncateTable("writings");
	}

	function test_form_add() {
		$bank = new Bank();
		$bank->name = "koala";
		$bank->save();
		$form = $bank->form_add();
		$this->assertPattern("/name_new/",$form);
		$this->truncateTable("sources");
	}

	function test_show_form_modification () {
		$bank = new Bank();
		$bank->name = "koala";
		$bank->save();
		$form = $bank->show_form_modification();
		$this->assertPattern("/koala/",$form);
		$this->assertPattern("/action/",$form);
		$this->truncateTable("sources");
	}
}
