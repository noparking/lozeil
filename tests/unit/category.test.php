<?php
/*
	lozeil
	$Author: adrien $
	$URL: $
	$Revision:  $

	Copyright (C) No Parking 2013 - 2013
*/

require_once dirname(__FILE__)."/../inc/require.inc.php";

class tests_Category extends TableTestCase {
	function __construct() {
		parent::__construct();
		$this->initializeTables(
			"writings",
			"categories"
		);
	}
	
	function test_save_load() {
		$category = new Category();
		$category->name = "première category";
		$category->vat = "";
		$category->save();
		$category = new Category();
		$category->name = "première category";
		$category->vat = "15.26";
		$category->save();
		$category = new Category();
		$category->name = "première category";
		$category->save();
		$category_loaded = new Category();
		$this->assertTrue($category_loaded->load(1));
		$this->assertEqual($category_loaded->name, $category->name);
		$this->assertTrue($category_loaded->vat == 0	);
		$this->assertTrue($category_loaded->load(2));
		$this->assertTrue($category_loaded->vat == 15.26);
		$this->assertTrue($category_loaded->load(3));
		$this->assertTrue($category_loaded->vat == 0);
		$this->assertFalse($category_loaded->load(4));
		$this->truncateTable("categories");
	}
	
	function test_update() {
		$category = new Category();
		$category->name = "premier category";
		$category->vat = "20.56";
		$category->save();
		$category_loaded = new Category();
		$category_loaded->id = 1;
		$category_loaded->name = "changement de nom";
		$category_loaded->vat = "";
		$category_loaded->update();
		$category_loaded2 = new Category();
		$this->assertTrue($category_loaded2->load(1));
		$this->assertNotEqual($category_loaded2->name, $category->name);
		$this->assertNotEqual($category_loaded2->vat, $category->vat);
		$this->truncateTable("categories");
	}
	
	function test_delete() {
		$category = new Category();
		$category->name = "première category";
		$category->save();
		$category_loaded = new Category();
		$this->assertTrue($category_loaded->load(1));
		$category->delete();
		$this->assertFalse($category_loaded->load(1));
		$this->truncateTable("categories");
	}
	
	function test_is_deletable() {
		$category = new Category();
		$category->id = 1;
		$category->save();
		$this->assertTrue($category->is_deletable());
		$writing = new Writing();
		$writing->categories_id = 1;
		$writing->save();
		$this->assertFalse($category->is_deletable());
		$this->assertFalse($category->is_deletable());
		$this->truncateTable("categories");
		$this->truncateTable("writings");
	}
}
