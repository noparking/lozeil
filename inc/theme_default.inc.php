<?php
/*
	lozeil
	$Author: adrien $
	$URL:  $
	$Revision:  $

	Copyright (C) No Parking 2013 - 2013
*/

class Theme_Default {
	function html_top() {
		return "<!DOCTYPE HTML>
			<html>";
	}
	
	function head() {
		return "<head>
			<title>".($GLOBALS['config']['title'] == '' ? '' : $GLOBALS['config']['title']." : ").$GLOBALS['config']['name']."</title>
			<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />".
			$this->css_files().
			$this->js_files()."
		</head>";
	}
	
	function body_top($location) {
		return "<body class=\"".Format::body_class($location)."\">";
	}
	
	function css_files() {
		$css_files[] = "medias/css/styles.css";
		$css_files[] = "http://fonts.googleapis.com/css?family=Bitter:400,700";
		
		$html = "";

		if (is_array($css_files)) {
			$media_css_file = "";
			foreach ($css_files as $css_file) {
				if (preg_match("/(print)/", $css_file)) {
					$media_css_file = " media=\"print\"";
				}
				if (substr($css_file, 0, 7) != 'http://') {
					$css_file = $GLOBALS['config']['layout_mediaserver'].$css_file;
				}
				$css_file .= "?v=".urlencode($GLOBALS['config']['version']);
				$html .= "<link rel=\"stylesheet\" type=\"text/css\"".$media_css_file." href=\"".$css_file."\" />\n";
				$media_css_file = "";
			}
		}

		return $html;
	}
	
	function js_files() {
		$js_files[] = "medias/js/jquery-1.9.1.js";
		$js_files[] = "medias/js/jquery-drag_drop.js";
		$js_files[] = "medias/js/drag_drop.js";
		
		$html = "";

		if (is_array($js_files)) {
			foreach ($js_files as $js_file) {
				$js_file = $GLOBALS['config']['layout_mediaserver'].$js_file."?v=".urlencode($GLOBALS['config']['version']);
				$html .= "<script src=\"".$js_file."\" language=\"JavaScript\" type=\"text/javascript\"></script>\n";
			}
		}

		return $html;
	}
	
	function content_top() {
		return "<div class=\"content\">";
	}

	function content_bottom() {
		return "</div>";
	}
	
	function body_bottom() {
		return "</body>";
	}
	
	function html_bottom() {
		return "</html>";
	}
}
