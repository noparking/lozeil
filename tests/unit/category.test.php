<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2016 */

require_once dirname(__FILE__)."/../inc/require.inc.php";

class tests_Category extends TableTestCase {
	function __construct() {
		parent::__construct();
		$this->initializeTables(
			"banks",
			"categories",
			"categories",
			"writings"
		);
	}
	
	function test_ask_before_delete() {
		$category = new Category();
		$category->name = "Via API";
		$category->save();
		
		$form = $category->ask_before_delete();
		$this->assertPattern("/category\[id\]/", $form);
		$this->assertPattern("/value=\"1\"/", $form);
		
		$this->truncateTable("categories");
	}
	
	function test_edit() {
		$category = new Category();
		$category->name = "Via API";
		$category->save();
		
		$form = $category->edit();
		$this->assertPattern("/category\[id\]/", $form);
		$this->assertPattern("/value=\"1\"/", $form);
		$this->assertPattern("/category\[name\]/", $form);
		$this->assertPattern("/value=\"Via API\"/", $form);
		$this->assertPattern("/category\[vat\]/", $form);
		$this->assertPattern("/category\[vat_category\]/", $form);
		
		$this->truncateTable("categories");
	}

	function test_link_to_delete() {
		$category = new Category();
		$this->assertNoPattern("/category.delete.php/", $category->link_to_delete());
		$this->assertNoPattern("/id=0/", $category->link_to_delete());
	
		$category->name = "Category 1";
		$category->save();
		$this->assertPattern("/category.delete.php/", $category->link_to_delete());
		$this->assertPattern("/id=".$category->id."/", $category->link_to_delete());
	
		$this->truncateTables("categories");
	}
	
	function test_link_to_edit() {
		$category = new Category();
		$this->assertPattern("/category.edit.php/", $category->link_to_edit());
		$this->assertNoPattern("/id=0/", $category->link_to_edit());
	
		$category->name = "Category 1";
		$category->save();
		$this->assertPattern("/category.edit.php/", $category->link_to_edit());
		$this->assertPattern("/id=".$category->id."/", $category->link_to_edit());
	
		$this->truncateTables("categories");
	}
	
	function test_save_load() {
		$category = new Category();
		$category->name = "première category";
		$category->vat = "";
		$category->vat_category = 0;
		$category->save();
		$category = new Category();
		$category->name = "première category";
		$category->vat = "15.26";
		$category->save();
		$category = new Category();
		$category->name = "première category";
		$category->vat_category = 1;
		$category->save();
		$category_loaded = new Category();
		$this->assertTrue($category_loaded->load(array('id' => 1)));
		$this->assertEqual($category_loaded->name, $category->name);
		$this->assertTrue($category_loaded->vat == 0);
		$this->assertTrue($category_loaded->vat_category == 0);
		$this->assertTrue($category_loaded->load(array("id" => 2 )));
		$this->assertTrue($category_loaded->vat == 15.26);
		$this->assertTrue($category_loaded->vat_category == 0);
		$this->assertTrue($category_loaded->load(array("id" => 3 )));
		$this->assertTrue($category_loaded->vat == 0);
		$this->assertTrue($category_loaded->vat_category == 1);
		$this->assertFalse($category_loaded->load(array("id" => 4 )));
		$this->truncateTable("categories");
	}
	
	function test_update() {
		$category = new Category();
		$category->name = "premier category";
		$category->vat = "20.56";
		$category->vat_category = 0;
		$category->save();
		$category_loaded = new Category();
		$category_loaded->id = 1;
		$category_loaded->name = "changement de nom";
		$category_loaded->vat = "";
		$category_loaded->vat_category = 1;
		$category_loaded->update();
		$category_loaded2 = new Category();
		$this->assertTrue($category_loaded2->load(array("id" => 1 )));
		$this->assertNotEqual($category_loaded2->name, $category->name);
		$this->assertNotEqual($category_loaded2->vat, $category->vat);
		$this->assertNotEqual($category_loaded2->vat_category, $category->vat_category);
		$this->truncateTable("categories");
	}
	
	function test_delete() {
		$category = new Category();
		$category->name = "première category";
		$category->save();
		$category_loaded = new Category();
		$this->assertTrue($category_loaded->load(array("id" => 1 )));
		$category->delete();
		$this->assertFalse($category_loaded->load(array("id" => 1 )));
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
	
	function test_is_in_use() {
		$category = new Category();
		$category->name = "premiere catégorie";
		$category->save();
		$this->assertFalse($category->is_in_use());
		$writing = new Writing();
		$writing->categories_id = 1;
		$writing->save();
		$this->assertTrue($category->is_in_use());
		$this->truncateTable("categories");
		$this->truncateTable("writings");
	}
	
	function test_clean() {
		$category = new Category();
		$cleaned = $category->clean(array('name' => "456 <h1>456</h2>", 'vat' => "category&lt;h1&gt;             "));
		$this->assertEqual($cleaned['name'], "456 456");
		$this->assertEqual($cleaned['vat'], "0");
		$this->assertEqual($cleaned['vat_category'], "0");
		
		$category = new Category();
		$cleaned = $category->clean(array('name' => 'Salaire', 'vat' => '1 0', 'vat_category' => '1'));
		$this->assertEqual($cleaned, array('name' => 'Salaire', 'vat' => '10.00', 'vat_category' => 1));
	}
}
