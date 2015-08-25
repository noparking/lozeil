<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2014 */

require_once dirname(__FILE__)."/../inc/require.inc.php";

class tests_cache extends UnitTestCase {
	function test_empty_cache_dir() {
		$fichier_cache = dirname(__FILE__)."/../../var/".time().".cache.php";
		file_put_contents($fichier_cache, "no-data");
		$this->assertTrue(file_exists($fichier_cache));
		empty_cache_dir();
		$this->assertFalse(file_exists($fichier_cache));
	}
}
