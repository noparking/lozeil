<?php
/* Lozeil -- Copyright (C) No Parking 2013 - 2016 */

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
	
	function test_js() {
		$theme = new Theme_Default();
		$GLOBALS['config']['layout_mediaserver'] = "";
		$this->assertPattern("/javascript/", $theme->js());
		$this->assertPattern("/medias\/js\/jquery.js/", $theme->js());
		$GLOBALS['config']['layout_mediaserver'] = "autre/chemin/";
		$this->assertPattern("/autre\/chemin\//", $theme->js());
		$this->assertPattern("/\?v=".$GLOBALS['config']['version']."/", $theme->js());
	}
	
	function test_head() {
		$theme = new Theme_Default();
		$this->assertPattern("/".$GLOBALS['config']['title']."/", $theme->head());
		$this->assertPattern("/".$GLOBALS['config']['name']."/", $theme->head());
	}
}
