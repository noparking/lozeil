<?php
/* Lozeil Copyright (C) No Parking 2014 - 2014 */

require_once dirname(__FILE__)."/../inc/require.inc.php";

class tests_Integrity_Lang extends UnitTestCase {
	function test_number_lang_entries() {
		$nb_fr = count($this->get_lang_entries("fr_FR"));
		$nb_en = count($this->get_lang_entries("en_EN"));		
		$this->assertEqual($nb_fr, $nb_en);
	}

	function test_lang_entries() {
		$entries_fr = $this->get_lang_entries("fr_FR", PREG_PATTERN_ORDER);
		$entries_en = $this->get_lang_entries("en_EN", PREG_PATTERN_ORDER);
			
		foreach ($entries_fr[1] as $entry) {
			$this->assertTrue(in_array($entry, $entries_en[1]), str_replace("%", "#", $entry)." manquant dans en_EN.lang.php");
		}
	}

	function get_lang_entries($lang, $flag=PREG_SET_ORDER) {
		$content = file_get_contents($path = dirname(__FILE__)."/../../lang/".$lang.".lang.php");
		preg_match_all("/(\\\$[a-z0-9_]+(\[[^]]*\])*)(\s*=\s*)(.*)/", $content, $matches, $flag);

		return $matches;
	}
}

class tests_Integrity_Config extends UnitTestCase {
	function test_version_number() {
		$config_dist = file(dirname(__FILE__)."/../../cfg/config.inc.php.dist");
		$version_number_dist = preg_replace("/.*config\['version'\]\s*= \"([0-9\.]*)\".*/", "\\1", trim($config_dist[12]));
		$config = file(dirname(__FILE__)."/../../cfg/config.inc.php");
		$version_number_config = preg_replace("/.*modif\['version'\]\s*= \"([0-9\.]*)\".*/", "\\1", trim($config[12]));

		$this->assertIdentical($version_number_dist, $version_number_config, "Le numéro de version n'est pas cohérent entre .dist et courant : %s.");
	}

	function test_tables() {
		$config_dist = file_get_contents(dirname(__FILE__)."/../../cfg/config.inc.php.dist");
		preg_match_all("/table_([a-z_]*)/", $config_dist, $config_dist_tables);
		$config_dist_tables = array_unique($config_dist_tables[1]);
		sort($config_dist_tables);

		$tables = new Database_Tables($GLOBALS['param']['locale_lang']);
		$tables->prepare();

		foreach (array_keys($tables->elements) as $table) {
			$this->assertTrue(in_array($table, $config_dist_tables), "table_".$table." manquant dans config.inc.php.dist");
		}

		foreach ($config_dist_tables as $table) {
			$this->assertTrue(in_array($table, array_keys($tables->elements)), "table_".$table." manquant dans Database_Tables");
		}
	}
}
