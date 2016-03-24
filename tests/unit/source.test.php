<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2016 */

require_once dirname(__FILE__)."/../inc/require.inc.php";

class tests_Source extends TableTestCase {
	function __construct() {
		parent::__construct();
		$this->initializeTables(
			"sources",
			"writings"
		);
	}
	
	function test_ask_before_delete() {
		$source = new Source();
		$source->name = "Via API";
		$source->save();
		
		$form = $source->ask_before_delete();
		$this->assertPattern("/source\[id\]/", $form);
		$this->assertPattern("/value=\"1\"/", $form);
		
		$this->truncateTable("sources");
	}
	
	function test_edit() {
		$source = new Source();
		$source->name = "Via API";
		$source->save();
		
		$form = $source->edit();
		$this->assertPattern("/source\[id\]/", $form);
		$this->assertPattern("/value=\"1\"/", $form);
		$this->assertPattern("/source\[name\]/", $form);
		$this->assertPattern("/value=\"Via API\"/", $form);
		
		$this->truncateTable("sources");
	}

	function test_link_to_delete() {
		$source = new Source();
		$this->assertNoPattern("/source.delete.php/", $source->link_to_delete());
		$this->assertNoPattern("/id=0/", $source->link_to_delete());
	
		$source->name = "Source 1";
		$source->save();
		$this->assertPattern("/source.delete.php/", $source->link_to_delete());
		$this->assertPattern("/id=".$source->id."/", $source->link_to_delete());
	
		$this->truncateTables("sources");
	}
	
	function test_link_to_edit() {
		$source = new Source();
		$this->assertPattern("/source.edit.php/", $source->link_to_edit());
		$this->assertNoPattern("/id=0/", $source->link_to_edit());
	
		$source->name = "Source 1";
		$source->save();
		$this->assertPattern("/source.edit.php/", $source->link_to_edit());
		$this->assertPattern("/id=".$source->id."/", $source->link_to_edit());
	
		$this->truncateTables("sources");
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
}
