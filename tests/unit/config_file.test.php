<?php
/* Lozeil -- Copyright (C) No Parking 2015 - 2016 */

require_once dirname(__FILE__)."/../inc/require.inc.php";

class tests_Config_File extends TableTestCase {
	function skip() {
		$this->skipUnless(is_readable(__DIR__."/../../lang/nl_BE.lang.php"), "les tests ont besoin de pouvoir lire dans '".__DIR__."/../../lang/nl_BE.lang.php'");
		$this->skipUnless(is_readable(__DIR__."/../../cfg/config.inc.php.dist"), "les tests ont besoin de pouvoir lire dans '".__DIR__."/../../cfg/config.inc.php.dist'");
	}

	function test_load_at_global_level() {
		$this->assertEqual($GLOBALS['__']['writing'], "écriture");
		$locale_lang = new Config_File(__DIR__."/../../lang/nl_BE.lang.php");
		$this->assertTrue($locale_lang->load_at_global_level());
		$this->assertNotEqual($GLOBALS['__']['writing'], "écriture");
		$this->assertEqual($GLOBALS['__']['writing'], "handtekening");
		$locale_lang = new Config_File(__DIR__."/../../lang/fr_FR.lang.php");
		$this->assertTrue($locale_lang->load_at_global_level());
	}

	function test_read_value() {
		$config = new Config_File(__DIR__."/../../cfg/config.inc.php.dist");
		$opentime_name = $config->read_value("name");
		$this->assertEqual($opentime_name, $GLOBALS['config']['name']);

		$table_user_name = $config->read_value("table_user");
		$this->assertEqual($table_user_name, "");

		$dbconfig = new Config_File(__DIR__."/../../cfg/config.inc.php.dist", "dbconfig");
		$table_user_name = $dbconfig->read_value("table_users");
		$this->assertEqual($table_user_name, $GLOBALS['dbconfig']['table_users']);
	}
	
	function test_find_default_value() {
		$config = new Config_File(__DIR__."/../../cfg/config.inc.php.dist");
		$lozeil_name = $config->find_default_value("name");
		$this->assertEqual($lozeil_name, $GLOBALS['config']['name']);
		$unknown_value = $config->find_default_value("unknown");
		$this->assertFalse($unknown_value);
	}
}
