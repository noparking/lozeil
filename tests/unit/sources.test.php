<?php
/*
	lozeil
	$Author: adrien $
	$URL: $
	$Revision:  $

	Copyright (C) No Parking 2013 - 2013
*/

require_once dirname(__FILE__)."/../inc/require.inc.php";

class tests_Sources extends TableTestCase {
	function __construct() {
		parent::__construct();
		$this->initializeTables(
			"sources"
		);
	}
	
	function test_names() {
		$source = new Source();
		$source->name = "première source";
		$source->save();
		$source2 = new Source();
		$source2->name = "deuxième source";
		$source2->save();
		$source3 = new Source();
		$source3->name = "troisième source";
		$source3->save();
		
		$sources = new Sources();
		$sources->select();
		$names = $sources->names();
		$this->assertTrue(in_array("--", $names));
		$this->assertTrue(in_array("première source", $names));
		$this->assertTrue(in_array("deuxième source", $names));
		$this->assertTrue(in_array("troisième source", $names));
	}
}
