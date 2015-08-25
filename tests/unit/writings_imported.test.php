<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2014 */

require_once dirname(__FILE__)."/../inc/require.inc.php";

class tests_Writings_Imported extends TableTestCase {
	function __construct() {
		parent::__construct();
		$this->initializeTables(
			"writingsimported"
		);
	}
	
	function test_filter_with() {
		$writings_imported = new Writings_Imported();
		$writings_imported->filter_with(array('id' => 3));
		$this->assertTrue($writings_imported->filters['id'] == 3);
		$this->truncateTable("writingsimported");
	}
}
