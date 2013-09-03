<?php
/*
	lozeil
	$Author: adrien $
	$URL: $
	$Revision:  $

	Copyright (C) No Parking 2013 - 2013
*/

require_once dirname(__FILE__)."/../inc/require.inc.php";

class tests_Types extends TableTestCase {
	function __construct() {
		parent::__construct();
		$this->initializeTables(
			"types"
		);
	}
	
	function test_names() {
		$type = new Type();
		$type->name = "premier type";
		$type->save();
		$type2 = new Type();
		$type2->name = "deuxième type";
		$type2->save();
		$type3 = new Type();
		$type3->name = "troisième type";
		$type3->save();
		
		$types = new Types();
		$types->select();
		$names = $types->names();
		$this->assertTrue(in_array("--", $names));
		$this->assertTrue(in_array("premier type", $names));
		$this->assertTrue(in_array("deuxième type", $names));
		$this->assertTrue(in_array("troisième type", $names));
	}
		
	function test_show_form() {
		$types = new Types();
		$types->select();
		$form = $types->show_form();
		$this->assertPattern("/premier type/", $form);
		$this->assertPattern("/deuxième type/", $form);
		$this->assertPattern("/troisième type/", $form);
		$this->assertPattern("/name_new/", $form);
	}
}
