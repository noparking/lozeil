<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2014 */

require_once dirname(__FILE__)."/../inc/require.inc.php";

class tests_Files extends TableTestCase {
	function __construct() {
		parent::__construct();
		$this->initializeTables(
			"files"
		);
	}
}
