<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2014 */

require_once dirname(__FILE__)."/../inc/require.inc.php";

class tests_Theme_Default extends TableTestCase {
	
	function test_css() {
		$theme = new Theme_Default();
		$GLOBALS['config']['layout_mediaserver'] = "";
		$this->assertPattern("/stylesheet/", $theme->css());
		$this->assertPattern("/medias\/css\/styles.css/", $theme->css());
		$GLOBALS['config']['layout_mediaserver'] = "autre/chemin/";
		$this->assertPattern("/autre\/chemin\//", $theme->css());
		$this->assertPattern("/\?v=".$GLOBALS['config']['version']."/", $theme->css());
	}
	
	function test_js_files() {
		$theme = new Theme_Default();
		$GLOBALS['config']['layout_mediaserver'] = "";
		$this->assertPattern("/javascript/", $theme->js_files());
		$this->assertPattern("/medias\/js\/jquery.js/", $theme->js_files());
		$GLOBALS['config']['layout_mediaserver'] = "autre/chemin/";
		$this->assertPattern("/autre\/chemin\//", $theme->js_files());
		$this->assertPattern("/\?v=".$GLOBALS['config']['version']."/", $theme->js_files());
	}
	
	function test_head() {
		$theme = new Theme_Default();
		$this->assertPattern("/".$GLOBALS['config']['title']."/", $theme->head());
		$this->assertPattern("/".$GLOBALS['config']['name']."/", $theme->head());
	}
}
