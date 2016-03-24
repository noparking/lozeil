<?php
/* Lozeil -- Copyright (C) No Parking 2016 - 2016 */

require_once dirname(__FILE__)."/../inc/require.inc.php";

class tests_Theme_Ajax extends UnitTestCase {
	function test_menu() {
		$menu = Plugins::factory("Menu_Area");
		$theme = new Theme_Ajax();
		$this->assertEqual($theme->menu($menu), "");
	}
	
	function test_heading() {
		$heading = new Heading_Area("Heading title");
		$theme = new Theme_Ajax();
		$this->assertEqual($theme->heading($heading), "");
	}
	
	function test_css() {
		$theme = new Theme_Ajax();
		$this->assertEqual($theme->css(), "");
	}
	
	function test_js() {
		$theme = new Theme_Ajax();
		$this->assertEqual($theme->js(), "");
	}
	
	function test_head() {
		$theme = new Theme_Ajax();
		$this->assertEqual($theme->head(), "");
	}
	
	function test_content_top() {
		$theme = new Theme_Ajax();
		$this->assertEqual($theme->content_top(), "<div class=\"content-ajax\">");
	}

	function test_content_bottom() {
		$theme = new Theme_Ajax();
		$this->assertEqual($theme->content_bottom(), "</div>");
	}
}
