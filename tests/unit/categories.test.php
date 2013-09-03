<?php
/*
	lozeil
	$Author: adrien $
	$URL: $
	$Revision:  $

	Copyright (C) No Parking 2013 - 2013
*/

require_once dirname(__FILE__)."/../inc/require.inc.php";

class tests_Categories extends TableTestCase {
	function __construct() {
		parent::__construct();
		$this->initializeTables(
			"categories"
		);
	}
	
	function test_names() {
		$category = new Category();
		$category->name = "premier category";
		$category->save();
		$category2 = new Category();
		$category2->name = "deuxième category";
		$category2->save();
		$category3 = new Category();
		$category3->name = "troisième category";
		$category3->save();
		
		$categories = new Categories();
		$categories->select();
		$names = $categories->names();
		$this->assertTrue(in_array("--", $names));
		$this->assertTrue(in_array("premier category", $names));
		$this->assertTrue(in_array("deuxième category", $names));
		$this->assertTrue(in_array("troisième category", $names));
	}
	
	function test_show_form() {
		$categories = new Categories();
		$categories->select();
		$form = $categories->show_form();
		$this->assertPattern("/premier category/", $form);
		$this->assertPattern("/deuxième category/", $form);
		$this->assertPattern("/troisième category/", $form);
		$this->assertPattern("/name_new/", $form);
	}
}
