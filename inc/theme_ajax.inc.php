<?php
/* Lozeil -- Copyright (C) No Parking 2016 - 2016 */

class Theme_Ajax extends Theme_Default {
	function html_top() {
		return "";
	}
	
	function head() {
		return "";
	}
	
	function body_top($location, $content = "") {
		return "";
	}
	
	function css_files() {
		return array();
	}

	function css() {
		return "";
	}
	
	function js_files() {
		return "";
	}

	function menu($menu) {
		return "";
	}
	
	function heading($heading) {
		return "";
	}

	function content_top() {
		return "<div class=\"content-ajax\">";
	}

	function content_bottom() {
		return "</div>";
	}
	
	function body_bottom() {
		return "";
	}
	
	function html_bottom() {
		return "";
	}
}
